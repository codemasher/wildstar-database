<?php
/**
 * @filesource   common-cli.php
 * @created      24.10.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBCLI;

use chillerlan\Database\{Database, DatabaseOptions, Drivers\MySQLiDrv};
use chillerlan\DotEnv\DotEnv;
use chillerlan\SimpleCache\MemoryCache;
use Psr\Log\AbstractLogger;

use function date, mb_internal_encoding, sprintf, substr, trim;

mb_internal_encoding('UTF-8');

require_once __DIR__.'/../vendor/autoload.php';

$wildstar_path = '/wildstar';

$env = (new DotEnv(__DIR__.'/../config', '.env', false))->load();

$o = [
	// DatabaseOptions
	'driver'   => MySQLiDrv::class,
	'host'     => $env->DB_HOST,
	'port'     => $env->DB_PORT,
	'socket'   => $env->DB_SOCKET,
	'database' => $env->DB_DATABASE,
	'username' => $env->DB_USERNAME,
	'password' => $env->DB_PASSWORD,
];

$logger = new class() extends AbstractLogger{

	public function log($level, $message, array $context = []){
		echo sprintf('[%s][%s] %s', date('Y-m-d H:i:s'), substr($level, 0, 4), trim($message))."\n";
	}

};

$db = new Database(new DatabaseOptions($o), new MemoryCache, $logger);
