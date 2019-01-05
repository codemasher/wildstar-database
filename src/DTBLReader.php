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

use chillerlan\Database\Database;
use Psr\Log\{LoggerAwareTrait, LoggerInterface, NullLogger};

/**
 * @property string $dtbl
 * @property string $name
 * @property array  $header
 * @property array  $cols
 * @property array  $data
 */
class DTBLReader{
	use LoggerAwareTrait;

	protected const FORMAT_HEADER = 'a4Signature/LVersion/QTableNameLength/QUnknown1/QRecordSize/QFieldCount/QDescriptionOffset/QRecordCount/QFullRecordSize/QEntryOffset/QNextId/QIDLookupOffset/QUnknown2';
	protected const FORMAT_COLUMN = 'LNameLength/LUnknown1/QNameOffset/SDataType/SUnknown2/LUnknown3';

	/**
	 * @var string
	 */
	protected $dtbl = '';

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var array
	 */
	protected $header = [];

	/**
	 * @var array
	 */
	protected $cols = [];

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var resource
	 */
	private $fh;

	/**
	 * DTBLReader constructor.
	 *
	 * @param \Psr\Log\LoggerInterface|null $logger
	 *
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function __construct(LoggerInterface $logger = null){

		if(PHP_INT_SIZE < 8){
			throw new WSDBException('64-bit PHP required');
		}

		$this->setLogger($logger ?? new NullLogger);
	}

	/**
	 * @return void
	 */
	public function __destruct(){

		if(is_resource($this->fh)){
			fclose($this->fh);
		}

	}

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get(string $name){
		return property_exists($this, $name) && $name !== 'fh' ? $this->{$name} : null;
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected function decodeString(string $str):string{
		return trim(mb_convert_encoding($str, 'UTF-8', 'UTF-16LE'));
	}

	/**
	 * @throws \codemasher\WildstarDB\WSDBException
	 * @return void
	 */
	protected function init():void{
		$this->logger->info('init: '.$this->dtbl);

		$this->fh   = fopen($this->dtbl, 'rb');
		$header     = fread($this->fh, 0x60);
		$this->cols = [];
		$this->data = [];

		if(strlen($header) !== 0x60){
			throw new WSDBException('cannot read DTBL header');
		}

		$this->header = unpack($this::FORMAT_HEADER, $header);

		$this->logger->info('fields: '.$this->header['FieldCount'].', rows: '.$this->header['RecordCount']);


		if($this->header['Signature'] !== "\x4c\x42\x54\x44"){ // LBTD
			throw new WSDBException('invalid DTBL');
		}

		$this->name = $this->decodeString(fread($this->fh, $this->header['TableNameLength'] * 2));

		fseek($this->fh, $this->header['DescriptionOffset'] + 0x60);

		for($i = 0; $i < $this->header['FieldCount']; $i++){
			$this->cols[$i]['header'] = unpack($this::FORMAT_COLUMN, fread($this->fh, 0x18));
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
	 * @param string $dtbl
	 *
	 * @return \codemasher\WildstarDB\DTBLReader
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function read(string $dtbl):DTBLReader{

		if(!is_file($dtbl) || !is_readable($dtbl)){
			throw new WSDBException('DTBL not readable');
		}

		$this->dtbl = $dtbl;

		$this->init();

		$offset = $this->header['EntryOffset'] + 0x60;

		fseek($this->fh, $offset);

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
						$v = unpack('L', substr($data, $j, 4))[1]; $j += 4; break;
					case 4:  // float
						$v = round(unpack('f', substr($data, $j, 4))[1], 3); $j += 4; break;
					case 20: // uint64
						$v = unpack('Q', substr($data, $j, 8))[1]; $j += 8; break;
					case 130: // string
						{
							$o    = unpack('L', substr($data, $j, 4))[1];
							$p    = ftell($this->fh);
							$skip = $o === 0;

							fseek($this->fh, $offset + ($o > 0 ? $o : unpack('L', substr($data, $j + 4, 4))[1]));

							$j += 8;
							$v = '';

							do{
								$s = fread($this->fh, 2);

								$v .= $s;
							}
							while($s !== "\x00\x00" && $s !== '');

							$v = $this->decodeString($v);
							fseek($this->fh, $p);
							break;
						}

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
	 * @throws \codemasher\WildstarDB\WSDBException
	 * @return void
	 */
	protected function checkData():void{

		if(empty($this->data)){
			throw new WSDBException('empty data, run DTBLReader::read() first');
		}

	}

	/**
	 * @param string $data
	 * @param string $file
	 *
	 * @return bool
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function saveToFile(string $data, string $file):bool{

		if(!is_writable(dirname($file))){
			throw new WSDBException('cannot write data to file: '.$file.', target directory is not writable');
		}

		return (bool)file_put_contents($file, $data);
	}

	/**
	 * @param string|null $file
	 * @param int|null    $jsonOptions
	 *
	 * @return string
	 */
	public function toJSON(string $file = null, int $jsonOptions = 0):string{
		$this->checkData();

		$json = json_encode($this->data, $jsonOptions);

		if($file !== null){
			$this->saveToFile($json, $file);
		}

		return $json;
	}

	/**
	 * @param string|null $file
	 * @param string      $delimiter
	 * @param string      $enclosure
	 * @param string      $escapeChar
	 *
	 * @return string
	 */
	public function toCSV(string $file = null, string $delimiter = ',', string $enclosure = '"', string $escapeChar = '\\'):string{
		$this->checkData();

		$mh = fopen('php://memory', 'r+');

		fputcsv($mh, array_column($this->cols, 'name'), $delimiter, $enclosure, $escapeChar);

		foreach($this->data as $row){
			fputcsv($mh, array_values($row), $delimiter, $enclosure, $escapeChar);
		}

		rewind($mh);

		$csv = stream_get_contents($mh);

		fclose($mh);

		if($file !== null){
			$this->saveToFile($csv, $file);
		}

		return $csv;
	}

	/**
	 * ugh!
	 *
	 * @param string|null $file
	 *
	 * @return string
	 */
	public function toXML(string $file = null):string{
		$this->checkData();

		$sxe = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>', LIBXML_BIGLINES);

		$types = [3 => 'uint32', 4 => 'float', 11 => 'bool', 20 => 'uint64', 130 => 'string'];

		foreach($this->data as $row){
			$item = $sxe->addChild('item');

			foreach(array_values($row) as $i => $value){
				$item
					->addChild($this->cols[$i]['name'], $value)
					->addAttribute('dataType', $types[$this->cols[$i]['header']['DataType']]);
				;
			}
		}

		$xml = $sxe->asXML();

		if($file !== null){
			$this->saveToFile($xml, $file);
		}

		return $xml;
	}

	/**
	 * @param \chillerlan\Database\Database $db
	 * @return void
	 */
	public function toDB(Database $db):void{
		// Windows: https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_lower_case_table_names
		$createTable = $db->create
			->table($this->name)
			->primaryKey($this->cols[0]['name'])
			->ifNotExists()
		;

		foreach($this->cols as $i => $col){

			switch($col['header']['DataType']){
				case 3:   $createTable->int($col['name'], 10, null, null, 'UNSIGNED'); break;
				case 4:   $createTable->decimal($col['name'], '7,3', 0); break;
				case 11:  $createTable->field($col['name'], 'BOOLEAN'); break;
				case 20:  $createTable->field($col['name'], 'BIGINT', null, 'UNSIGNED'); break;
				case 130: $createTable->text($col['name']); break;
			}

		}

		$createTable->query();

		if(count($this->data) < 1){
			$this->logger->notice('no records available for table '.$this->name);
			return;
		}

		$db->insert->into($this->name)->values($this->data)->multi();
	}

}
