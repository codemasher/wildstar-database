<?php
/**
 * Class DTBLReader
 *
 * @link         https://arctium.io/wiki/index.php?title=WildStar_Client_Database_(.tbl)
 * @link         https://bitbucket.org/mugadr_m/wildstar-studio/src/973583416d4436e4980de840c2c91cfc5972fb2a/WildstarStudio/DataTable.h
 *
 * @filesource   DTBLReader.php
 * @created      04.01.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

class DTBLReader extends ReaderAbstract{

	/**
	 * @var string
	 * @internal
	 */
	protected $FORMAT_HEADER = 'a4Signature/LVersion/QTableNameLength/x8/QRecordSize/QFieldCount/QDescriptionOffset/QRecordCount/QFullRecordSize/QEntryOffset/QNextId/QIDLookupOffset/x8';

	/**
	 * @var bool
	 * @internal
	 */
	protected $skip;

	/**
	 * @var int
	 * @internal
	 */
	protected $pos;

	/**
	 * @var string
	 * @internal
	 */
	protected $rowdata;

	/**
	 * @param string $filename
	 *
	 * @return \codemasher\WildstarDB\ReaderInterface
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function read(string $filename):ReaderInterface{
		$this->loadFile($filename);

		if($this->header['Signature'] !== "\x4c\x42\x54\x44"){ // LBTD
			throw new WSDBException('invalid DTBL');
		}

		$this->readColumnHeaders();
		$this->readData();

		if(\count($this->data) !== $this->header['RecordCount']){
			throw new WSDBException('invalid row count');
		}

		return $this;
	}

	/**
	 * @return void
	 */
	protected function readColumnHeaders():void{

		// table name (UTF-16LE: length *2)
		$this->name = $this->decodeString(\fread($this->fh, $this->header['TableNameLength'] * 2));

		// skip forward
		\fseek($this->fh, $this->header['DescriptionOffset'] + $this->headerSize);

		// read the column headers (4+4+8+2+2+4 = 24 bytes)
		for($i = 0; $i < $this->header['FieldCount']; $i++){
			$this->cols[$i]['header'] = \unpack('LNameLength/x4/QNameOffset/SDataType/x2/x4', \fread($this->fh, 24));
		}

		$offset = $this->header['FieldCount'] * 24 + $this->header['DescriptionOffset'] + $this->headerSize;

		if($this->header['FieldCount'] % 2){
			$offset += 8;
		}

		// read the column names
		foreach($this->cols as $i => $col){
			\fseek($this->fh, $offset + $col['header']['NameOffset']);

			// column name (UTF-16LE: length *2)
			$this->cols[$i]['name'] = $this->decodeString(\fread($this->fh, $col['header']['NameLength'] * 2));
		}

		$this->logger->info($this->name.', fields: '.$this->header['FieldCount'].', rows: '.$this->header['RecordCount']);
	}

	/**
	 * @return void
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function readData():void{
		\fseek($this->fh, $this->header['EntryOffset'] + $this->headerSize);

		$this->data = array_fill(0, $this->header['RecordCount'], null);

		// read a row
		foreach($this->data as $i => $_){
			$this->rowdata = \fread($this->fh, $this->header['RecordSize']);
			$this->pos     = 0;
			$this->skip    = false;

			// loop through the columns
			foreach($this->cols as $c => $col){

				// skip 4 bytes if the string offset is 0 (determined by $skip), the current type is string and the next isn't
				if($this->skip === true && ($c > 0 && $this->cols[$c - 1]['header']['DataType'] === 130) && $col['header']['DataType'] !== 130){
					$this->pos += 4;
				}

				$this->data[$i][$col['name']] = $this->getValue($col['header']['DataType']);
			}

			// if we run into this, a horrible thing happened
			if(\count($this->data[$i]) !== $this->header['FieldCount']){
				throw new WSDBException('invalid field count');
			}

		}

	}

	/**
	 * @param int $datatype
	 *
	 * @return int|float|string|null
	 */
	protected function getValue(int $datatype){

		switch($datatype){
			case 3:  // uint32
			case 11: // booleans (stored as uint32 0/1)
				$v = uint32(\substr($this->rowdata, $this->pos, 4));
				$this->pos += 4; break;
			case 4:  // float
				$v = \round(float(\substr($this->rowdata, $this->pos, 4)), 3); // @todo: determine round precision
				$this->pos += 4; break;
			case 20: // uint64
				$v = uint64(\substr($this->rowdata, $this->pos, 8));
				$this->pos += 8; break;
			case 130: // string (UTF-16LE)
				$v = $this->readString();
				$this->pos += 8; break;

			default: $v = null;
		}

		return $v;
	}

	/**
	 * @return string
	 */
	protected function readString():string{
		$o    = uint32(\substr($this->rowdata, $this->pos, 4));
		$p    = \ftell($this->fh);
		$this->skip = $o === 0;

		\fseek($this->fh, $this->header['EntryOffset'] + $this->headerSize + ($o > 0 ? $o : uint32(\substr($this->rowdata, $this->pos + 4, 4))));

		$v = '';
		// loop through the string until we hit 2 nul bytes or the void
		do{
			$s = \fread($this->fh, 2);
			$v .= $s;
		}
		while($s !== "\x00\x00" && $s !== '');

		\fseek($this->fh, $p);

		return $this->decodeString($v);
	}

}
