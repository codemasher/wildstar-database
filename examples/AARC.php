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

use codemasher\WildstarDB\AARCReader;

/** @var \chillerlan\Database\Database $db */
$db = null;

/** @var \Psr\Log\LoggerInterface $logger */
$logger = null;

require_once __DIR__.'/common.php';

$reader = new AARCReader($logger);

$reader->read('./WildStar/Patch/ClientDataDE.archive');

