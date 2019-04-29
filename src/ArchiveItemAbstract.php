<?php
/**
 * Class ArchiveItemAbstract
 *
 * @filesource   ArchiveItemAbstract.php
 * @created      28.04.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

abstract class ArchiveItemAbstract{

	public $Parent;
	public $Name;
	public $NameOffset;

	public function __construct(array $data, string $parent){

		foreach($data as $property => $value){
			$this->{$property} = $value;
		}

		$this->Parent = $parent;
	}

}
