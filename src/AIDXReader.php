<?php
/**
 * Class AIDXReader
 *
 * @link https://github.com/Narthorn/Halon/blob/master/halon.py
 *
 * @filesource   AIDXReader.php
 * @created      06.01.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

/**
 * @property array $dirs
 */
class AIDXReader extends PACKReaderAbstract{

	/** @var array */
	protected $dirs;

	/**
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function readData():void{

		// get the root info block of the AIDX file (4+4+4+4 = 16 bytes)
		$rootInfo = \unpack(
			'a4ArchiveType/LVersion/LBuildnumber/LIndex',
			\fread($this->fh, $this->blocktable[$this->header['RootInfoIndex']]['Size'])
		);

		if($rootInfo['ArchiveType'] !== "\x58\x44\x49\x41"){ // XDIA
			throw new WSDBException('invalid AIDX');
		}

		$this->dirs = [];
		$this->data = $this->getBlock($this->blocktable[$rootInfo['Index']]);
	}

	/**
	 * @param array  $blockInfo
	 * @param string $parent
	 *
	 * @return array
	 */
	protected function getBlock(array $blockInfo, string $parent = ''):array{

		// add the current path to the collection
		$this->dirs[] = $parent;

		// find the info block
		\fseek($this->fh, $blockInfo['Offset']);

		// get the count of directories and files in that block (4+4 = 8 bytes)
		$n = \unpack('Ldirs/Lfiles', \fread($this->fh, 8));

		$dirs  = \array_fill(0, $n['dirs'], null);
		$files = \array_fill(0, $n['files'], null);

		// create a directory object for each dir (4+4 = 8 bytes)
		foreach($dirs as $i => $_){
			$dirs[$i] = new ArchiveDirectory(\unpack('LNameOffset/LBlockIndex', \fread($this->fh, 8)), $parent);
		}

		// create a file object for each file (4+4+8+8+8+20+4 = 56 bytes)
		foreach($files as $i => $_){
			$files[$i] = new ArchiveFile(
				\unpack('LNameOffset/LFlags/QFiletime/QSizeUncompressed/QSizeCompressed/a20Hash/x4', \fread($this->fh, 56)),
				$parent
			);
		}

		// read the list of names from the remaining data
		$names = \fread($this->fh, $blockInfo['Size'] - (\ftell($this->fh) - $blockInfo['Offset']));

		// apply the names to each object in the block
		$setnames = function(array &$arr) use ($names):void{
			foreach($arr ?? [] as $i => $e){
				$arr[$i]->Name = \DIRECTORY_SEPARATOR.
					\substr($names, $e->NameOffset, \strpos($names, "\x00", $e->NameOffset) - $e->NameOffset);
			}
		};

		$setnames($dirs);
		$setnames($files);

		// loop through the directory stucture recursively and add the block data
		foreach($dirs as $i => $info){
			if(isset($this->blocktable[$info->BlockIndex])){
				$dirs[$i]->Content = $this->getBlock($this->blocktable[$info->BlockIndex], $parent.$info->Name);
			}
		}

		return \array_merge($dirs, $files);
	}

}