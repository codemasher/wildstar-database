<?php
/**
 * @filesource   LTEX.php
 * @created      06.01.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\Archive\LTEXReader;
use Throwable;

require_once __DIR__.'/common.php';

/**
 * @var \chillerlan\Database\Database $db
 * @var \Psr\Log\LoggerInterface $logger
 * @var string $wildstar_path
 */

$reader = new LTEXReader($logger);

foreach(['DE' => 'de-DE', 'EN' => 'en-US', 'FR' => 'fr-FR'] as $dir => $lang){
	$file  = $wildstar_path.'/Patch/ClientData'.$dir.'/'.$lang.'.bin';
	$table = 'LocalizedText_'.$lang;

	try{
		$db->drop->table($table)->ifExists()->query();

		$reader->read($file)
			->toDB($db)
#			->toJSON(__DIR__.'/'.$lang.'.json', JSON_PRETTY_PRINT)
#			->toCSV(__DIR__.'/'.$lang.'.csv', '|', '`')
		;

		// defrag & optimize table
		/** @noinspection SqlResolve */
		$db->raw('ALTER TABLE `'.$table.'` ENGINE=InnoDB');
		$db->raw('OPTIMIZE TABLE `'.$table.'`');

		$logger->info('success: '.$reader->prettyname. ' ('.$file.')');
	}
	catch(Throwable $e){
		$logger->error($e->getMessage());
	}

}
