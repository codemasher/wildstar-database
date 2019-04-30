<?php
/**
 * Class ArchiveExtractor
 *
 * @link https://github.com/codemasher/php-xz REQUIRED! ext-xz to decompress lzma
 * @link https://github.com/hcs64/ww2ogg (.wem audio to ogg vorbis)
 * @link https://github.com/Vextil/Wwise-Unpacker (.bnk, .wem)
 * @link https://github.com/hpxro7/wwiseutil (.bnk GUI tool)
 * @link https://hcs64.com/vgm_ripping.html
 *
 * @filesource   ArchiveExtractor.php
 * @created      28.04.2019
 * @package      codemasher\WildstarDB
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};

class ArchiveExtractor implements LoggerAwareInterface{
	use LoggerAwareTrait;

	public const ARCHIVES = ['ClientData', 'ClientDataDE', 'ClientDataEN', 'ClientDataFR'];

	/** @var \codemasher\WildstarDB\AIDXReader */
	protected $AIDX;
	/** @var \codemasher\WildstarDB\AARCReader */
	protected $AARC;
	/** @var resource */
	protected $fh;
	/** @var string */
	protected $archivepath;
	/** @var string */
	protected $archivename;
	/** @var string */
	protected $destination;
	/** @var \codemasher\WildstarDB\ArchiveFile[] */
	public $errors;
	/** @var \codemasher\WildstarDB\ArchiveFile[] */
	public $warnings;

	/**
	 * ArchiveExtractor constructor.
	 *
	 * @param \Psr\Log\LoggerInterface $logger
	 *
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function __construct(LoggerInterface $logger){

		if(!\extension_loaded('xz')){
			throw new WSDBException('required extension xz missing!');
		}

		$this->logger = $logger ?? new NullLogger;

		$this->AIDX = new AIDXReader($this->logger);
		$this->AARC = new AARCReader($this->logger);
	}

	/**
	 * @param string $index
	 *
	 * @return \codemasher\WildstarDB\ArchiveExtractor
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function open(string $index):ArchiveExtractor{
		$this->archivename = \str_replace(['.index', '.archive'], '', \basename($index));

		if(!in_array($this->archivename, $this::ARCHIVES)){
			throw new WSDBException('invalid archive file (Steam Wildstar not supported)');
		}

		$this->archivepath = \dirname($index).\DIRECTORY_SEPARATOR.$this->archivename;

		$this->AIDX->read($this->archivepath.'.index');
		$this->AARC->read($this->archivepath.'.archive');

		return $this;
	}

	/**
	 * @param string|null $destination
	 *
	 * @return \codemasher\WildstarDB\ArchiveExtractor
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	public function extract(string $destination = null):ArchiveExtractor{
		$this->destination = \rtrim($destination ?? $this->archivepath, '\\/');

		// does the destination parent exist?
		if(!$this->destination || !\file_exists(\dirname($this->destination))){
			throw new WSDBException('invalid destination: '.$this->destination);
		}

		// destination does not exist?
		if(!\file_exists($this->destination)){
			// is the parent writable?
			if(!\is_writable(\dirname($this->destination))){
				throw new WSDBException('destination parent is not writable');
			}
			// create it
			\mkdir($this->destination, 0777);
		}

		// destination exists but isn't writable?
		if(!\is_writable($this->destination)){
			throw new WSDBException('destination is not writable');
		}

		$this->fh       = \fopen($this->archivepath.'.archive', 'rb');
		$this->warnings = [];
		$this->errors   = [];

		foreach($this->AIDX->data as $item){
			$this->read($item);
		}

		\fclose($this->fh);

		return $this;
	}

	/**
	 * @param \codemasher\WildstarDB\ArchiveItemAbstract $item
	 *
	 * @return void
	 */
	protected function read(ArchiveItemAbstract $item):void{

		if($item instanceof ArchiveDirectory){

			foreach($item->Content as $dir){
				$dest = $this->destination.$dir->Parent;

				if(!\file_exists($dest)){
					\mkdir($dest, 0777, true);
				}

				$this->read($dir);
			}

			return;
		}
		/** @var \codemasher\WildstarDB\ArchiveFile $item */
		$this->extractFile($item);
	}

	/**
	 * @param \codemasher\WildstarDB\ArchiveFile $file
	 *
	 * @throws \codemasher\WildstarDB\WSDBException
	 */
	protected function extractFile(ArchiveFile $file):void{
		$dest = $this->destination.$file->Parent.$file->Name;

		if(\file_exists($dest)){ // @todo: overwrite option
			$this->logger->notice('file already exists: '.$dest);
			return;
		}

		$blockInfo = $this->AARC->data[$file->Hash];
		$block     = $this->AARC->blocktable[$blockInfo['Index']];

		\fseek($this->fh, $block['Offset']);
		$content = \fread($this->fh, $block['Size']);

		// hash the read data
		if(\sha1($content) !== $file->Hash){
			throw new WSDBException('corrupt data, invalid hash: '.\sha1($content).' (expected '.$file->Hash.' for '.$file->Name.')');
		}

		// $Flags is supposed to be a bitmask
		if($file->Flags === 1){ // no compression
			// nada
		}
		elseif($file->Flags === 3){ // deflate (probably unsed)
			$content = \gzinflate($content);
		}
		elseif($file->Flags === 5){ // lzma (requires ext-xz)
			// https://bitbucket.org/mugadr_m/wildstar-studio/issues/23
			$content = \xzdecode(\substr($content, 0, 5).\pack('Q', $file->SizeUncompressed).\substr($content, 5));
		}
		else{
			throw new WSDBException('invalid file flag');
		}

		$bytesWritten = \file_put_contents($dest, $content);

		if($bytesWritten === false){
			$this->errors[$file->Hash] = $file;
			$this->logger->error('error writing '.$dest);

		}
		elseif($bytesWritten !== $file->SizeUncompressed){
			$this->warnings[$file->Hash] = $file;
			// throw new WSDBException
			$this->logger->warning('size discrepancy for '.$dest.', expected '.$file->SizeUncompressed.' got '.$bytesWritten);
		}

		$this->logger->info('extracted: '.$dest.' ('.$bytesWritten.' bytes)');
	}

}
