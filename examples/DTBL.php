<?php
/**
 * @filesource   DTBL.php
 * @created      04.01.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\Archive\DTBLReader;
use DirectoryIterator, Throwable;

require_once __DIR__.'/common.php';

/**
 * @var \chillerlan\Database\Database $db
 * @var \Psr\Log\LoggerInterface $logger
 * @var string $wildstar_path
 */

$reader   = new DTBLReader($logger);
$iterator = new DirectoryIterator($wildstar_path.'/Patch/ClientData/DB');

foreach($iterator as $finfo){

	if($finfo->isDot()){
		continue;
	}

	if($finfo->getExtension() !== 'tbl'){
		$logger->notice($finfo->getFilename().' is probably not a DTBL');
		continue;
	}

	try{
		$reader
			->read($finfo->getRealPath())
			->toDB($db)
#			->toJSON($finfo->getFilename().'.json')
		;

		$logger->info('success: '.$reader->name.', '.$finfo->getFilename());
	}
	catch(Throwable $e){
		$logger->error($finfo->getFilename().': '.$e->getMessage());
	}

}
