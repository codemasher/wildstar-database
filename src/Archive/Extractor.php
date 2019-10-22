<?php
/**
 * Class Extractor
 *
 * @link         https://github.com/codemasher/php-xz REQUIRED! ext-xz to decompress lzma
 * @link         https://github.com/hcs64/ww2ogg (.wem audio to ogg vorbis)
 * @link         https://github.com/Vextil/Wwise-Unpacker (.bnk, .wem)
 * @link         https://github.com/hpxro7/wwiseutil (.bnk GUI tool)
 * @link         https://hcs64.com/vgm_ripping.html
 * @link         https://www.reddit.com/r/WildStar/comments/9efluz/wildstar_model_exporter/ (.m3)
 * @link         https://pastebin.com/R72C8NgT (.tex)
 *
 * @link         https://arctium.io/wiki/index.php?title=File_Formats (gone???)
 * @link         https://github.com/CucFlavius/WSEdit
 * @link         https://github.com/Taggrin/WildStar-MapMerger/blob/master/mapmerger.py
 * @link         https://github.com/Prior99/wildstar-map
 *
 * @filesource   Extractor.php
 * @created      28.04.2019
 * @package      codemasher\WildstarDB\Archive
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB\Archive;

use codemasher\WildstarDB\WSDBException;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};

use function basename, dirname, extension_loaded, fclose, file_exists, file_put_contents, fopen, fread, fseek,
	gc_collect_cycles, gc_enable, gc_mem_caches, gzinflate, in_array, is_writable, mkdir, pack, rtrim, sha1,
	sprintf, str_replace, substr, xzdecode;

use const DIRECTORY_SEPARATOR;

class Extractor implements LoggerAwareInterface{

	use LoggerAwareTrait;

	public const ARCHIVES = ['ClientData', 'ClientDataDE', 'ClientDataEN', 'ClientDataFR'];

	/** @var \codemasher\WildstarDB\Archive\AIDXReader */
	protected $AIDX;
	/** @var \codemasher\WildstarDB\Archive\AARCReader */
	protected $AARC;
	/** @var string */
	protected $archivepath;
	/** @var string */
	protected $archivename;
	/** @var string */
	protected $destination;
	/** @var \codemasher\WildstarDB\Archive\File[] */
	public $errors;
	/** @var \codemasher\WildstarDB\Archive\File[] */
	public $warnings;

	/**
	 * Extractor constructor.
	 *
	 * @param \Psr\Log\LoggerInterface $logger
	 *
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function __construct(LoggerInterface $logger){

		if(!extension_loaded('xz')){
			throw new WSDBException('required extension xz missing!');
		}

		$this->logger = $logger ?? new NullLogger;

		$this->AIDX = new AIDXReader($this->logger);
		$this->AARC = new AARCReader($this->logger);

		gc_enable();
	}

	/**
	 * @param string $index
	 *
	 * @return \codemasher\WildstarDB\Archive\Extractor
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function open(string $index):Extractor{
		$this->archivename = str_replace(['.index', '.archive'], '', basename($index));

		if(!in_array($this->archivename, $this::ARCHIVES)){
			throw new WSDBException('invalid archive file (Steam Wildstar not supported)');
		}

		$this->archivepath = dirname($index).DIRECTORY_SEPARATOR.$this->archivename;

		$this->AIDX->read($this->archivepath.'.index');
		$this->AARC->read($this->archivepath.'.archive');

		return $this;
	}

	/**
	 * @param string|null $destination
	 *
	 * @return \codemasher\WildstarDB\Archive\Extractor
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function extract(string $destination = null):Extractor{
		$this->destination = rtrim($destination ?? $this->archivepath, '\\/');

		// does the destination parent exist?
		if(!$this->destination || !file_exists(dirname($this->destination))){
			throw new WSDBException('invalid destination: '.$this->destination);
		}

		// destination does not exist?
		if(!file_exists($this->destination)){
			// is the parent writable?
			if(!is_writable(dirname($this->destination))){
				throw new WSDBException('destination parent is not writable');
			}
			// create it
			mkdir($this->destination, 0777);
		}

		// destination exists but isn't writable?
		if(!is_writable($this->destination)){
			throw new WSDBException('destination is not writable');
		}

		$this->warnings = [];

		foreach($this->AIDX->data as $item){
			$this->read($item);
		}

		return $this;
	}

	/**
	 * @param \codemasher\WildstarDB\Archive\ItemAbstract $item
	 *
	 * @return void
	 */
	protected function read(ItemAbstract $item):void{

		if($item instanceof Directory){

			foreach($item->Content as $dir){

				if(!file_exists($this->destination.$dir->Parent)){
					mkdir($this->destination.$dir->Parent, 0777, true);
				}

				$this->read($dir);
			}

			return;
		}
		/** @var \codemasher\WildstarDB\Archive\File $item */
		$this->extractFile($item);

		gc_collect_cycles();
		gc_mem_caches();
	}

	/**
	 * @param \codemasher\WildstarDB\Archive\File $file
	 */
	protected function extractFile(File $file):void{
		$dest = $this->destination.$file->Parent.$file->Name;

		if(file_exists($dest)){ // @todo: overwrite option
			$this->logger->notice('file already exists: '.$dest);

			return;
		}

		$block        = $this->AARC->blocktable[$this->AARC->data[$file->Hash]['Index']];
		$bytesWritten = file_put_contents(
			$dest,
			$this->getContent($file, $block['Offset'], $block['Size'])
		);

		if($bytesWritten === false){
#			$this->errors[$file->Hash] = $file;
			$this->logger->error('error writing '.$dest);

		}
		elseif($bytesWritten !== $file->SizeUncompressed){
#			$this->warnings[$file->Hash] = $file;
			// throw new WSDBException
			$this->logger->warning(
				sprintf('size discrepancy for %1$s, expected %2$s got %3$s', $dest, $file->SizeUncompressed, $bytesWritten)
			);
		}

		$this->logger->info('extracted: '.$dest.' ('.$bytesWritten.' bytes)');
	}

	/**
	 * @param \codemasher\WildstarDB\Archive\File $file
	 * @param int                                 $offset
	 * @param int                                 $size
	 *
	 * @return string
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function getContent(File $file, int $offset, int $size):string{
		// slower but probably more memory efficient (it'll blow up either way)
		$fh = fopen($this->archivepath.'.archive', 'rb');
		fseek($fh, $offset);
		$content = fread($fh, $size);
		fclose($fh);

		// hash the read data
		if(sha1($content) !== $file->Hash){
			throw new WSDBException(
				sprintf('corrupt data, invalid hash: %1$s (expected %2$s for %3$s)', sha1($content), $file->Hash, $file->Name)
			);
		}

		// $Flags is supposed to be a bitmask
		if($file->Flags === 1){ // no compression
			return $content;
		}
		elseif($file->Flags === 3){ // deflate (probably unsed)
			return gzinflate($content);
		}
		elseif($file->Flags === 5){ // lzma (requires ext-xz)
			// https://bitbucket.org/mugadr_m/wildstar-studio/issues/23
			return xzdecode(substr($content, 0, 5).pack('Q', $file->SizeUncompressed).substr($content, 5));
		}

		throw new WSDBException('invalid file flag');
	}

}
