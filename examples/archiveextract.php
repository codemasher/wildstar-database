<?php
/**
 * @filesource   archiveextract.php
 * @created      28.04.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\Archive\Extractor;

require_once __DIR__.'/common.php';

/**
 * @var \Psr\Log\LoggerInterface $logger
 * @var string $wildstar_path
 */

$extractor = new Extractor($logger);

foreach(Extractor::ARCHIVES as $archive){

	$extractor
		->open($wildstar_path.'/Patch/'.$archive.'.index')
		->extract('/vagrant/WildStar')
	;

	foreach($extractor->warnings as $file){
		// handle warnings if necessary
		$logger->info('a warning occured for: '.$file->Parent.$file->Name);
	}

}

