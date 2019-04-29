<?php
/**
 * Class LTEXReader
 *
 * @link https://arctium.io/wiki/index.php?title=Locale_Lookup_Index_(.bin)
 *
 * @filesource   LTEXReader.php
 * @created      05.01.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

/**
 * @property string $prettyname
 */
class LTEXReader extends ReaderAbstract{

	// https://docs.microsoft.com/en-us/openspecs/windows_protocols/ms-lcid/a9eac961-e77d-41a6-90a5-ce1a8b0cdb9c
	protected const LCID = [
		0x0407 => 'de-DE', // 1031
		0x0409 => 'en-US', // 1033
		0x040C => 'fr-FR', // 1036
		0x0412 => 'ko-KR', // 1042
	];

	/**
	 * @var string
	 * @internal
	 */
	protected $FORMAT_HEADER = 'a4Signature/LVersion/LLanguage/LLCID/QTagNameStringLength/QTagNameStringPtr/QShortNameStringLength/QShortNameStringPtr/QLongNameStringLength/QLongNameStringPtr/QEntryCount/QEntryIndexPtr/QNameStoreLength/QNameStorePtr';

	/**
	 * @var string
	 */
	protected $prettyname;

	/**
	 * @param string $filename
	 *
	 * @return \codemasher\WildstarDB\ReaderInterface
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function read(string $filename):ReaderInterface{
		$this->loadFile($filename);

		if($this->header['Signature'] !== "\x58\x45\x54\x4c"){ // XETL
			throw new WSDBException('invalid LTEX');
		}

		\fseek($this->fh, $this->headerSize + $this->header['LongNameStringPtr']);

		$this->prettyname = $this->decodeString(\fread($this->fh, $this->header['LongNameStringLength'] * 2));
		$this->name       = 'LocalizedText_'.$this::LCID[$this->header['LCID']];
		$this->cols       = [
			['name' => 'ID',            'header' => ['DataType' =>   3]],
			['name' => 'LocalizedText', 'header' => ['DataType' => 130]],
		];

		$this->readData();

		$this->logger->info($this->prettyname.' ('.$this->header['LCID'].', '.$this::LCID[$this->header['LCID']].'), rows: '.$this->header['EntryCount']);

		return $this;
	}

	/**
	 * @return void
	 */
	protected function readData():void{
		\fseek($this->fh, $this->headerSize + $this->header['EntryIndexPtr']);

		$this->data = array_fill(0, $this->header['EntryCount'], null);

		foreach($this->data as $i => $_){
			// get the id and offset for the data block
			$c = \unpack('Lid/Loffset', \fread($this->fh, 8));
			// save the current position
			$p = \ftell($this->fh);

			// seek forward to the data block
			\fseek($this->fh, $this->headerSize + $this->header['NameStorePtr'] + $c['offset'] * 2);

			$v = '';
			// read until we hit a double nul or the void
			do{
				$s = \fread($this->fh, 2);
				$v .= $s;
			}
			while($s !== "\x00\x00" && $s !== '');

			$this->data[$i] = ['ID' => $c['id'], 'LocalizedText' => $this->decodeString($v)];

			// restore the previous position
			\fseek($this->fh, $p);
		}

	}

}
