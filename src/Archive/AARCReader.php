<?php
/**
 * Class AARCReader
 *
 * @filesource   AARCReader.php
 * @created      27.04.2019
 * @package      codemasher\WildstarDB\Archive
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB\Archive;

use codemasher\WildstarDB\WSDBException;

use function bin2hex, fread, fseek, unpack;

class AARCReader extends PACKReaderAbstract{

	protected const AARC_ROOT = 'a4ArchiveType/LVersion/LBlockcount/LIndex';
	protected const AARC_DATA = 'LIndex/a20Hash/QSizeCompressed';

	/**
	 * @inheritDoc
	 */
	protected function readData():void{

		// get the root info block of the AARC file (4+4+4+4 = 16 bytes)
		$rootInfo = unpack($this::AARC_ROOT, fread($this->fh, 16));

		if($rootInfo['ArchiveType'] !== "\x43\x52\x41\x41"){ // CRAA
			throw new WSDBException('invalid AARC');
		}

		// get the root data info block
		$blockInfo = $this->blocktable[$rootInfo['Index']];

		fseek($this->fh, $blockInfo['Offset']);

		// read the data block info (4+20+8 = 32 bytes)
		for($i = 0; $i < $rootInfo['Blockcount']; $i++){
			$data = unpack($this::AARC_DATA, fread($this->fh, 32));
			$hash = bin2hex($data['Hash']);
			unset($data['Hash']);
			$this->data[$hash] = $data;
		}

	}

}
