<?php
/**
 * @link https://github.com/Taggrin/WildStar-MapMerger/blob/master/mapmerger.py
 * @link https://github.com/Prior99/wildstar-map
 *
 * @filesource   map-build.php
 * @created      23.10.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBCLI;

use DirectoryIterator, Imagick;

use function explode, hexdec, in_array, min, str_split;

require_once __DIR__.'/common-cli.php';

/**
 * @var \Psr\Log\LoggerInterface      $logger
 * @var string                        $wildstar_path
 */

$include_maps = [
	'AdventureGaleras',
	'AdventureHycrest',
	'AdventureLevianBay',
	'AdventureMalgrave',
	'AdventureNorthernWilds',
	'AdventureWhitevale',
	'Arcterra',
	'DruseraInstance4',
	'Eastern',
	'Farsidesque',
	'HalloftheHundred',
	'NewCentral',
	'OsunDungeon',
	'ShadesEveInstance',
	'Skullcano',
	'TorineDungeon',
	'Western',
];

$outpath = __DIR__.'/maps';

foreach(new DirectoryIterator($wildstar_path.'/Patch/ClientData/Map') as $dir){
	$map = $dir->getFilename();

	if(!$dir->isDir() || $dir->isDot() || (!empty($include_maps) && !in_array($map, $include_maps))){
		continue;
	}

	$textures = [];
	$xmin     = null;
	$ymin     = null;
	$xmax     = 0;
	$ymax     = 0;

	foreach(new DirectoryIterator($dir->getPathname()) as $file){

		if($file->getExtension() !== 'bmp'){
			continue;
		}

		$hex = str_split(explode('.', $file->getFilename())[1], 2);
		$x   = hexdec($hex[1]);
		$y   = hexdec($hex[0]);

		// exclude some empty tiles to not exaggerate map/image size
		if(
			($map === 'Eastern' && $y < 3)
			|| ($map === 'AdventureMalgrave' && ($x > 80 || $y < 65))
			|| ($map === 'NewCentral' && ($x > 80 || $y < 3))
			|| ($map === 'MordechaiReturns' && $y < 6)
			|| (in_array($map, ['Western', 'AdventureLevianBay']) && ($x > 74 || $y < 46 || $y > 74))
			|| ($map === 'HalonRingNew' && ($x > 56 || $y > 66))
		){
			continue;
		}

		$xmin = min($xmin ?? $x, $x);
		$ymin = min($ymin ?? $y, $y);
		$xmax = max($xmax, $x);
		$ymax = max($ymax, $y);

		$textures[$y][$x] = $file->getRealPath();
	}

	if(empty($textures)){
		continue;
	}

	$im = new Imagick;
	$im->newImage(($xmax - $xmin + 1) * 512, ($ymax - $ymin + 1) * 512, '#757575');
	$im->setImageFormat('png');

	foreach($textures as $y => $col){
		foreach($col as $x => $file){
			$im->compositeImage(new Imagick($file), Imagick::COMPOSITE_OVER, ($x - $xmin) * 512, ($y - $ymin) * 512);
			$logger->info($file);
		}
	}

	$out = $outpath.'/'.$map.'.png';
	$im->writeImage($out);
	$im->destroy();
	$logger->info($out);
}
