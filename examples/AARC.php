<?php
/**
 *
 * @filesource   AARC.php
 * @created      27.04.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\Archive\{AARCReader, Extractor};
use Throwable;

/** @var \Psr\Log\LoggerInterface $logger */
/** @var string $wildstar_path */

require_once __DIR__.'/common.php';

$reader = new AARCReader($logger);

foreach(Extractor::ARCHIVES as $index){

	try{
		$reader
			->read($wildstar_path.'/Patch/'.$index.'.archive')
			->toJSON(__DIR__.'/'.$index.'.archive.json', JSON_PRETTY_PRINT)
		;
	}
	catch(Throwable $e){
		$logger->error($e->getMessage());
	}
}
