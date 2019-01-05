<?php
/**
 * @filesource   common.php
 * @created      04.01.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\WildstarDBExamples;

use chillerlan\Database\{Database, DatabaseOptionsTrait, Drivers\MySQLiDrv};
use chillerlan\DotEnv\DotEnv;
use chillerlan\Logger\{Log, LogOptionsTrait, Output\ConsoleLog};
use chillerlan\Settings\SettingsContainerAbstract;
use chillerlan\SimpleCache\MemoryCache;

mb_internal_encoding('UTF-8');

require_once __DIR__.'/../vendor/autoload.php';

$env = (new DotEnv(__DIR__.'/../config', '.env', false))->load();

$o = [
	// DatabaseOptions
	'driver'      => MySQLiDrv::class,
	'host'        => $env->DB_HOST,
	'port'        => $env->DB_PORT,
	'socket'      => $env->DB_SOCKET,
	'database'    => $env->DB_DATABASE,
	'username'    => $env->DB_USERNAME,
	'password'    => $env->DB_PASSWORD,
	// LogOptions
	'minLogLevel' => 'info',
];

$options = new class($o) extends SettingsContainerAbstract{
	use DatabaseOptionsTrait, LogOptionsTrait;
};

$logger = (new Log)->addInstance(new ConsoleLog($options), 'app-log');
$cache  = new MemoryCache;
$db     = new Database($options, $cache, $logger);

$db->connect();


