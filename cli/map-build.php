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

use function explode, hexdec, in_array, min, sprintf, str_split;

require_once __DIR__.'/common-cli.php';

/**
 * @var \Psr\Log\LoggerInterface      $logger
 * @var string                        $wildstar_path
 */

$include_maps = [
	'AdventureAstrovoidPrison',
	'AdventureGaleras',
	'AdventureHycrest',
	'AdventureLevianBay',
	'AdventureMalgrave',
	'AdventureNorthernWilds',
	'AdventureWhitevale',
	'Arcterra',
	'AugmentorsRaid',
	'BattlegroundHallsoftheBloodsworn',
	'ColdbloodCitadel',
	'CommunityHousing',
	'Datascape', // careful, huge!
	'DominionArkShipTutorial',
	'DruseraInstance4',
	'DruseraMicroInstance1',
	'DruseraMicroInstance2',
	'DruseraMicroInstance3',
	'Eastern',
	'EthnDunon',
	'ExcavationSabotage',
	'ExileArkShipTutorial',
	'Farsidesque',
	'GeneticArchives',
	'GrimvaultCore',
	'HalloftheHundred',
	'HalonRingNew',
	'HousingAlgorocSky', // careful, huge!
	'InfiniteLabs',
	'KevinHTLFort',
	'KevinVortexQuarry',
	'MordechaiReturns',
	'NewCentral',
	'NewPlayerExperience',
	'OsunDungeon',
	'PCPLevianBay',
	'PocketCaps',
	'ProtostarWinterfest',
	'PvPArena',
	'PvPArena2',
	'RedMoonTerror',
	'ShadesEveInstance',
	'ShiphandAsteroidMining', // careful, huge!
	'ShiphandDeepSpaceDisappearance',
	'ShiphandGauntlet',
	'ShiphandHungerFromtheVoid',
	'ShiphandInfestation',
	'ShiphandLevel6',
	'ShiphandRageLogic',
	'ShiphandSpaceMadness',
	'Skullcano',
	'TorineDungeon',
	'UltimateProtogamesJuniors',
	'WarplotSkyMap',
	'WarplotsMap',
	'Western',
	'WorldStory00',
	'WorldStory01',
	'HousingDeraduneSky',
	'HousingAlgorocSky',
	'HousingAlgorocSolo',
];

$outpath = __DIR__.'/maps';

foreach(new DirectoryIterator($wildstar_path.'/Patch/ClientData/Map') as $dir){ // 'C:\\Games\\Wildstar Studio\\out\\Map'
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
			|| ($map === 'GeneticArchives' && ($x < 60 || $x > 74 || $y < 58 || $y > 67))
			|| ($map === 'ShiphandDeepSpaceDisappearance' && ($x < 59 || $x > 67 || $y < 60 || $y > 67))
			|| ($map === 'ShiphandLevel6' && ($x < 81 || $x > 84 || $y < 49 || $y > 54))
			|| ($map === 'ProtostarWinterfest' && ($x < 63 || $x > 66 || $y < 63 || $y > 65))
			|| ($map === 'PvPArena2' && ($x < 63 || $x > 66 || $y < 64 || $y > 65))
			|| ($map === 'AdventureAstrovoidPrison' && ($x < 61 || $x > 65 || $y < 61 || $y > 66))
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

	$logger->info(sprintf('xmin: %s, xmax %s, ymin: %s, ymax: %s', $xmin, $xmax, $ymin, $ymax));

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
