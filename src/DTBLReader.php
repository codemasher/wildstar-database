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

	protected $FORMAT_HEADER = 'a4Signature/LVersion/QTableNameLength/QUnknown1/QRecordSize/QFieldCount/QDescriptionOffset/QRecordCount/QFullRecordSize/QEntryOffset/QNextId/QIDLookupOffset/QUnknown2';
	protected $FORMAT_COLUMN = 'LNameLength/LUnknown1/QNameOffset/SDataType/SUnknown2/LUnknown3';

	/**
	 * @return void
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function init():void{

		if($this->header['Signature'] !== "\x4c\x42\x54\x44"){ // LBTD
			throw new WSDBException('invalid DTBL');
		}

		$this->name = $this->decodeString(fread($this->fh, $this->header['TableNameLength'] * 2));

		$this->logger->info($this->name.', fields: '.$this->header['FieldCount'].', rows: '.$this->header['RecordCount']);

		fseek($this->fh, $this->header['DescriptionOffset'] + 0x60);

		for($i = 0; $i < $this->header['FieldCount']; $i++){
			$this->cols[$i]['header'] = unpack($this->FORMAT_COLUMN, fread($this->fh, 0x18));
		}

		$offset = $this->header['FieldCount'] * 0x18 + $this->header['DescriptionOffset'] + 0x60;

		if($this->header['FieldCount'] % 2){
			$offset += 8;
		}

		foreach($this->cols as $i => $col){
			fseek($this->fh, $offset + $col['header']['NameOffset']);

			$this->cols[$i]['name'] = $this->decodeString(fread($this->fh, $col['header']['NameLength'] * 2));
		}

	}

	/**
	 * @param string $filename
	 *
	 * @return \codemasher\WildstarDB\ReaderInterface
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function read(string $filename):ReaderInterface{
		$this->loadFile($filename);
		$this->init();

		fseek($this->fh, $this->header['EntryOffset'] + 0x60);

		for($i = 0; $i < $this->header['RecordCount']; $i++){
			$data = fread($this->fh, $this->header['RecordSize']);
			$row  = [];
			$j    = 0;
			$skip = false;

			foreach($this->cols as $c => $col){

				if($skip === true && ($c > 0 && $this->cols[$c - 1]['header']['DataType'] === 130) && $col['header']['DataType'] !== 130){
					$j += 4;
				}

				switch($col['header']['DataType']){
					case 3:  // uint32
					case 11: // booleans (stored as uint32 0/1)
						$v = uint32(substr($data, $j, 4)); $j += 4; break;
					case 4:  // float
						$v = round(float(substr($data, $j, 4)), 3); $j += 4; break;
					case 20: // uint64
						$v = uint64(substr($data, $j, 8)); $j += 8; break;
					case 130: // string
						$v = $this->readString($data, $j, $skip); $j += 8; break;

					default: $v = null;
				}

				$row[$col['name']] = $v;
			}

			if(count($row) !== $this->header['FieldCount']){
				throw new WSDBException('invalid field count');
			}

			$this->data[$i] = $row;
		}

		fclose($this->fh);

		if(count($this->data) !== $this->header['RecordCount']){
			throw new WSDBException('invalid row count');
		}

		return $this;
	}

	/**
	 * @param string $data
	 * @param int    $j
	 * @param bool   $skip
	 *
	 * @return string
	 */
	protected function readString(string $data, int $j, bool &$skip):string{
		$o    = uint32(substr($data, $j, 4));
		$p    = ftell($this->fh);
		$skip = $o === 0;

		fseek($this->fh, $this->header['EntryOffset'] + 0x60 + ($o > 0 ? $o : uint32(substr($data, $j + 4, 4))));

		$v = '';

		do{
			$s = fread($this->fh, 2);
			$v .= $s;
		}
		while($s !== "\x00\x00" && $s !== '');

		fseek($this->fh, $p);

		return $this->decodeString($v);
	}

}
