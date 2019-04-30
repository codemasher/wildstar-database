<?php
/**
 * @filesource   archiveextract.php
 * @created      28.04.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\ArchiveExtractor;

/** @var \chillerlan\Database\Database $db */
$db = null;

/** @var \Psr\Log\LoggerInterface $logger */
$logger = null;

require_once __DIR__.'/common.php';

$extractor = new ArchiveExtractor($logger);

foreach(ArchiveExtractor::ARCHIVES as $archive){
	$extractor->open('./WildStar/Patch/'.$archive.'.index');
	$extractor->extract();

	foreach($extractor->warnings as $file){
		// handle warnings if necessary
		$logger->info('a warning occured for: '.$file->Parent.$file->Name);
	}
}

