<?php
/**
 * @filesource   DTBL.php
 * @created      04.01.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\DTBLReader;
use DirectoryIterator, Throwable;

/** @var \chillerlan\Database\Database $db */
$db = null;

/** @var \Psr\Log\LoggerInterface $logger */
$logger = null;

require_once __DIR__.'/common.php';

$reader   = new DTBLReader($logger);
$iterator = new DirectoryIterator(__DIR__.'/tbl');

foreach($iterator as $finfo){

	if($finfo->isDot()){
		continue;
	}

	if($finfo->getExtension() !== 'tbl'){
		$logger->notice($finfo->getFilename().' is probably not a DTBL');
		continue;
	}

	try{
		$reader->read($finfo->getRealPath());
		$reader->toDB($db);

		$logger->info('success: '.$reader->name.', '.$finfo->getFilename());
	}
	catch(Throwable $e){
		$logger->error($finfo->getFilename().': '.$e->getMessage());
	}

}
