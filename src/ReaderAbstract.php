<?php
/**
 * Class ReaderAbstract
 *
 * @filesource   ReaderAbstract.php
 * @created      05.01.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

use chillerlan\Database\Database;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};
use SimpleXMLElement;

/**
 * @property string $file
 * @property array  $header
 * @property string $name
 * @property array  $cols
 * @property array  $data
 * @property int    $headerSize
 */
abstract class ReaderAbstract implements ReaderInterface, LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * @see http://php.net/manual/function.pack.php
	 * @var string
	 */
	protected $FORMAT_HEADER;

	/**
	 * @var string
	 */
	protected $file = '';

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
	protected $fh;

	/**
	 * @var int
	 */
	protected $headerSize = 96;

	/**
	 * ReaderInterface constructor.
	 *
	 * @param \Psr\Log\LoggerInterface|null $logger
	 *
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function __construct(LoggerInterface $logger = null){

		if(\PHP_INT_SIZE < 8){
			throw new WSDBException('64-bit PHP required');
		}

		$this->setLogger($logger ?? new NullLogger);
	}

	/**
	 * @return void
	 */
	public function __destruct(){
		$this->logger->info('memory usage: '.(\memory_get_usage(true)/1048576).'MB');
		$this->logger->info('peak memory usage: '.(\memory_get_peak_usage(true)/1048576).'MB');

		$this->close();
	}

	/**
	 * @return \codemasher\WildstarDB\ReaderInterface
	 */
	public function close():ReaderInterface{

		if(\is_resource($this->fh)){
			\fclose($this->fh);

			$this->fh = null;
		}

		$this->file   = '';
		$this->name   = '';
		$this->header = [];
		$this->cols   = [];
		$this->data   = [];

		return $this;
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get(string $name){
		return \property_exists($this, $name) && $name !== 'fh' ? $this->{$name} : null;
	}

	/**
	 * @param string $filename
	 *
	 * @return void
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function loadFile(string $filename):void{
		$this->close();
		$filename = \realpath($filename);

		if(!$filename || !\is_file($filename) || !\is_readable($filename)){
			throw new WSDBException('input file not readable');
		}

		$this->file = $filename;
		$this->fh   = \fopen($this->file, 'rb');
		$header     = \fread($this->fh, $this->headerSize);

		$this->logger->info('loading: '.$this->file);

		if(\strlen($header) !== $this->headerSize){
			throw new WSDBException('cannot read header');
		}

		$this->header = \unpack($this->FORMAT_HEADER, $header);
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected function decodeString(string $str):string{
		return \trim(\mb_convert_encoding($str, 'UTF-8', 'UTF-16LE'));
	}

	/**
	 * @return void
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function checkData():void{

		if(empty($this->data)){
			throw new WSDBException('empty data, run ReaderInterface::read() first');
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

		if(!\is_writable(\dirname($file))){
			throw new WSDBException('cannot write data to file: '.$file.', target directory is not writable');
		}

		$this->logger->info('writing data to file: '.$file);

		return (bool)\file_put_contents($file, $data);
	}

	/**
	 * @param string|null $file
	 * @param int|null    $jsonOptions
	 *
	 * @return string
	 */
	public function toJSON(string $file = null, int $jsonOptions = 0):string{
		$this->checkData();

		$json = \json_encode($this->data, $jsonOptions);

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

		$mh = \fopen('php://memory', 'r+');

		\fputcsv($mh, \array_column($this->cols, 'name'), $delimiter, $enclosure, $escapeChar);

		foreach($this->data as $row){
			\fputcsv($mh, \array_values($row), $delimiter, $enclosure, $escapeChar);
		}

		\rewind($mh);

		$csv = \stream_get_contents($mh);

		\fclose($mh);

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

		$sxe   = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>', \LIBXML_BIGLINES);
		$types = [3 => 'uint32', 4 => 'float', 11 => 'bool', 20 => 'uint64', 130 => 'string'];

		foreach($this->data as $row){
			$item = $sxe->addChild('item');

			foreach(\array_values($row) as $i => $value){
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
	 *
	 * @return \codemasher\WildstarDB\ReaderInterface
	 */
	public function toDB(Database $db):ReaderInterface{
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

		$this->logger->info($createTable->sql());

		$createTable->query();

		if(\count($this->data) < 1){
			$this->logger->notice('no records available for table '.$this->name);
			return $this;
		}

		$db->insert->into($this->name)->values($this->data)->multi();

		return $this;
	}

}
