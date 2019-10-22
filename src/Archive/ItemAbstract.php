<?php
/**
 * Class ItemAbstract
 *
 * @filesource   ItemAbstract.php
 * @created      28.04.2019
 * @package      codemasher\WildstarDB\Archive
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB\Archive;

abstract class ItemAbstract{

	/** @var string */
	public $Parent;
	/** @var string */
	public $Name;
	/** @var int */
	public $NameOffset;

	/**
	 * ItemAbstract constructor.
	 *
	 * @param array  $data
	 * @param string $parent
	 */
	public function __construct(array $data, string $parent){

		foreach($data as $property => $value){
			$this->{$property} = $value;
		}

		$this->Parent = $parent;
	}

}
