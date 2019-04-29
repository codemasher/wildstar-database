<?php
/**
 * @filesource   AIDX.php
 * @created      06.01.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBExamples;

use codemasher\WildstarDB\AIDXReader;

/** @var \chillerlan\Database\Database $db */
$db = null;

/** @var \Psr\Log\LoggerInterface $logger */
$logger = null;

require_once __DIR__.'/common.php';

$reader = new AIDXReader($logger);

$reader->read('./WildStar/Patch/ClientData.index')->toJSON(__DIR__.'/ClientData.json', JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
#$reader->read('./WildStar/Patch/ClientDataDE.index')->toJSON(__DIR__.'/ClientDataDE.json', JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
#$reader->read('./WildStar/Patch/ClientDataEN.index');
#$reader->read('./WildStar/Patch/ClientDataFR.index');
#$reader->read('./WildStar/Patch/Client64.index');
#$reader->read('./WildStar/Patch/Patch.index');

