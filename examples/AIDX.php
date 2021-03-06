<?php
/**
 * @filesource   AIDX.php
 * @created      06.01.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\Archive\AIDXReader;
use Throwable;

require_once __DIR__.'/common.php';

/**
 * @var \Psr\Log\LoggerInterface $logger
 * @var string $wildstar_path
 */

$reader = new AIDXReader($logger);

foreach(['ClientData', 'ClientDataDE', 'ClientDataEN', 'ClientDataFR', 'Client64', 'Patch'] as $index){

	try{
		$reader
			->read($wildstar_path.'/Patch/'.$index.'.index')
			->toJSON(__DIR__.'/'.$index.'.index.json', JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)
		;
	}
	catch(Throwable $e){
		$logger->error($e->getMessage());
	}
}
