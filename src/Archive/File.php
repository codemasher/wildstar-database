<?php
/**
 * Class File
 *
 * @filesource   File.php
 * @created      28.04.2019
 * @package      codemasher\WildstarDB\Archive
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB\Archive;

use function bin2hex;

final class File extends ItemAbstract{

	/** @var int */
	public $Flags;
	/** @var int */
	public $Filetime;
	/** @var int */
	public $FileUtime;
	/** @var int */
	public $SizeUncompressed;
	/** @var int */
	public $SizeCompressed;
	/** @var string */
	public $Hash;

	/**
	 * File constructor.
	 *
	 * @param array  $data
	 * @param string $parent
	 */
	public function __construct(array $data, string $parent){
		parent::__construct($data, $parent);

		$this->Hash      = bin2hex($this->Hash);
		$this->FileUtime = (int)($this->Filetime / 100000000);
#		$dt = (new \DateTime)->createFromFormat('U.u', $this->FileUtime) ?: (new \DateTime)->createFromFormat('U', $this->FileUtime);
#		$this->FileTimeStr = $dt->format(\DateTimeInterface::RFC3339_EXTENDED);
	}

}
