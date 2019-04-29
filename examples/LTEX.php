<?php
/**
 * @filesource   LTEX.php
 * @created      06.01.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\LTEXReader;
use Throwable;

/** @var \chillerlan\Database\Database $db */
$db = null;

/** @var \Psr\Log\LoggerInterface $logger */
$logger = null;

require_once __DIR__.'/common.php';

$reader = new LTEXReader($logger);

#$reader->read(__DIR__.'/en-US.bin')->toJSON(__DIR__.'/en.json', JSON_PRETTY_PRINT);
#$reader->read(__DIR__.'/de-DE.bin')->toCSV(__DIR__.'/de.csv', '|', '`');

foreach(['de-DE', 'en-US', 'fr-FR'] as $lang){
	$file  = __DIR__.'/'.$lang.'.bin';
	$table = 'LocalizedText_'.$lang;

	try{
		$db->drop->table($table)->ifExists()->query();

		$reader->read($file);
		$reader->toDB($db);

		// defrag & optimize table
		$db->raw('ALTER TABLE `'.$table.'` ENGINE=InnoDB');
		$db->raw('OPTIMIZE TABLE `'.$table.'`');

		$logger->info('success: '.$reader->prettyname. ' ('.$file.')');
	}
	catch(Throwable $e){
		$logger->error($e->getMessage());
	}

}
