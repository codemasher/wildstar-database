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

/** @var \chillerlan\Database\Database $db */
$db = null;

/** @var \Psr\Log\LoggerInterface $logger */
$logger = null;

require_once __DIR__.'/common.php';

$reader = new LTEXReader($logger);

#$reader->read(__DIR__.'/en-US.bin')->toJSON(__DIR__.'/en.json', JSON_PRETTY_PRINT);
$reader->read(__DIR__.'/de-DE.bin')->toCSV(__DIR__.'/de.csv');
#$reader->read(__DIR__.'/fr-FR.bin')->toDB($db);
