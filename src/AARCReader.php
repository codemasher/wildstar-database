<?php
/**
 * Class AARCReader
 *
 * @filesource   AARCReader.php
 * @created      27.04.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

class AARCReader extends PACKReaderAbstract{

	/**
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function readData():void{

		// get the root info block of the AARC file (4+4+4+4 = 16 bytes)
		$rootInfo = \unpack(
			'a4ArchiveType/LVersion/LBlockcount/LIndex',
			\fread($this->fh, $this->blocktable[$this->header['RootInfoIndex']]['Size'])
		);

		if($rootInfo['ArchiveType'] !== "\x43\x52\x41\x41"){ // CRAA
			throw new WSDBException('invalid AARC');
		}

		// get the root data info block
		$blockInfo = $this->blocktable[$rootInfo['Index']];

		\fseek($this->fh, $blockInfo['Offset']);

		// read the data block info (4+20+8 = 32 bytes)
		for($i = 0; $i < $rootInfo['Blockcount']; $i++){
			$data = unpack('LIndex/a20Hash/QSizeUncompressed', \fread($this->fh, 32));
			$hash = \bin2hex($data['Hash']);
			unset($data['Hash']);
			$this->data[$hash] = $data;
		}

	}

}
