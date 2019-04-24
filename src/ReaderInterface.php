<?php
/**
 * Interface ReaderInterface
 *
 * @filesource   ReaderInterface.php
 * @created      05.01.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

use chillerlan\Database\Database;

interface ReaderInterface{

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	public function __get(string $name);

	/**
	 * @param string $filename
	 *
	 * @return \codemasher\WildstarDB\ReaderInterface
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function read(string $filename):ReaderInterface;

	/**
	 * @param string|null $file
	 * @param int|null    $jsonOptions
	 *
	 * @return string
	 */
	public function toJSON(string $file = null, int $jsonOptions = 0):string;

	/**
	 * @param string|null $file
	 * @param string      $delimiter
	 * @param string      $enclosure
	 * @param string      $escapeChar
	 *
	 * @return string
	 */
	public function toCSV(string $file = null, string $delimiter = ',', string $enclosure = '"', string $escapeChar = '\\'):string;

	/**
	 * @param string|null $file
	 *
	 * @return string
	 */
	public function toXML(string $file = null):string;

	/**
	 * @param \chillerlan\Database\Database $db
	 * @return void
	 */
	public function toDB(Database $db):ReaderInterface;

}
