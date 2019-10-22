<?php
/**
 * Class ArchiveFile
 *
 * @filesource   ArchiveFile.php
 * @created      28.04.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

use function bin2hex;

class ArchiveFile extends ArchiveItemAbstract{

	public $Flags;
	public $Filetime;
	public $FileUtime;
	public $SizeUncompressed;
	public $SizeCompressed;
	public $Hash;

	public function __construct(array $data, string $parent){
		parent::__construct($data, $parent);

		$this->Hash      = bin2hex($this->Hash);
		$this->FileUtime = (int)($this->Filetime / 100000000);
#		$dt = (new \DateTime)->createFromFormat('U.u', $this->FileUtime) ?: (new \DateTime)->createFromFormat('U', $this->FileUtime);
#		$this->FileTimeStr = $dt->format(\DateTimeInterface::RFC3339_EXTENDED);
	}

}
