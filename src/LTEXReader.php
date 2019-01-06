<?php
/**
 * Class LTEXReader
 *
 * @filesource   LTEXReader.php
 * @created      05.01.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

class LTEXReader extends ReaderAbstract{

	protected $FORMAT_HEADER = 'a4Signature/LVersion/LLanguage/LLCID/QTagNameStringLength/QTagNameStringPtr/QShortNameStringLength/QShortNameStringPtr/QLongNameStringLength/QLongNameStringPtr/QEntryCount/QEntryIndexPtr/QNameStoreLength/QNameStorePtr';

	/**
	 * @return void
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function init():void{

		if($this->header['Signature'] !== "\x58\x45\x54\x4c"){ // XETL
			throw new WSDBException('invalid LTEX');
		}

		fseek($this->fh, 0x60 + $this->header['LongNameStringPtr']);

		$this->name = $this->decodeString(fread($this->fh, $this->header['LongNameStringLength'] * 2));
		$this->cols = [
			['name' => 'ID',            'header' => ['DataType' =>   3]],
			['name' => 'LocalizedText', 'header' => ['DataType' => 130]],
		];

		$this->logger->info($this->name.', rows: '.$this->header['EntryCount']);
	}

	/**
	 * @param string $filename
	 *
	 * @return \codemasher\WildstarDB\ReaderInterface
	 */
	public function read(string $filename):ReaderInterface{
		$this->loadFile($filename);
		$this->init();

		fseek($this->fh, 0x60 + $this->header['EntryIndexPtr']);

		for($i = 0; $i < $this->header['EntryCount']; $i++){
			$a = unpack('Lid/Lpos', fread($this->fh, 8));
			$p = ftell($this->fh);
			$v = '';

			fseek($this->fh, 0x60 + $this->header['NameStorePtr'] + ($a['pos'] * 2));

			do{
				$s = fread($this->fh, 2);
				$v .= $s;
			}
			while($s !== "\x00\x00" && $s !== '');

			$this->data[$i] = ['ID' => $a['id'], 'LocalizedText' => $this->decodeString($v)];
			fseek($this->fh, $p);
		}

		return $this;
	}

}
