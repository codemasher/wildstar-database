<?php
/**
 * @filesource   functions.php
 * @created      06.01.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDB;

use function is_array, is_float, is_int, pack, unpack;

const WSDB_FUNCTIONS = true;

// http://php.net/manual/en/function.pack.php#119402

/**
 * @param int|string $i
 * @param bool|null  $endianness
 *
 * @return array|string|false
 */
function uint32($i, bool $endianness = null){

	if($endianness === true){ // big-endian
		$f = 'N';
	}
	elseif($endianness === false){ // little-endian
		$f = 'V';
	}
	else{ // machine byte order
		$f = 'L';
	}

	$i = is_int($i) ? pack($f, $i) : unpack($f, $i);

	return is_array($i) ? $i[1] : $i;
}

/**
 * @param int|string $i
 * @param bool|null  $endianness
 *
 * @return array|string|false
 */
function uint64($i, bool $endianness = null){

	if($endianness === true){ // big-endian
		$f = 'J';
	}
	elseif($endianness === false){ // little-endian
		$f = 'P';
	}
	else{ // machine byte order
		$f = 'Q';
	}

	$i = is_int($i) ? pack($f, $i) : unpack($f, $i);

	return is_array($i) ? $i[1] : $i;
}

/**
 * @param float|int|string $i
 * @param bool|null        $endianness
 *
 * @return array|string|false
 */
function float($i, bool $endianness = null){

	if($endianness === true){ // big-endian
		$f = 'G';
	}
	elseif($endianness === false){ // little-endian
		$f = 'g';
	}
	else{ // machine byte order
		$f = 'f';
	}

	$i = (is_float($i) || is_int($i)) ? pack($f, $i) : unpack($f, $i);

	return is_array($i) ? $i[1] : $i;
}
