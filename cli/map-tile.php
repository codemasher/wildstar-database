<?php
/**
 * @filesource   map-tile.php
 * @created      24.10.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace codemasher\WildstarDBCLI;

use chillerlan\Imagetiler\{Imagetiler, ImagetilerException, ImagetilerOptions};
use ImageOptimizer\OptimizerFactory;

require_once __DIR__.'/common-cli.php';

/**
 * @var \Psr\Log\LoggerInterface $logger
 */

const utils   = __DIR__.'/../../../utils/%s.exe';
const mapdir  = __DIR__.'/maps';
const tiledir = __DIR__.'/../public/tiles';

$maps = [
	'Arcterra'                         => 'continents/arcterra',
	'Eastern'                          => 'continents/alizar',
	'Farsidesque'                      => 'continents/farside',
	'NewCentral'                       => 'continents/isigrol',
	'Western'                          => 'continents/olyssia',
	'AdventureGaleras'                 => 'adventures/galeras',
	'AdventureHycrest'                 => 'adventures/hycrest',
	'AdventureLevianBay'               => 'adventures/levianbay',
	'AdventureMalgrave'                => 'adventures/malgrave',
	'AdventureNorthernWilds'           => 'adventures/northernwilds',
	'AdventureWhitevale'               => 'adventures/whitevale',
	'AdventureAstrovoidPrison'         => 'adventures/astrovoid',
	'EthnDunon'                        => 'dungeons/stormtalon', // ???
	'OsunDungeon'                      => 'dungeons/kelvoreth',
	'Skullcano'                        => 'dungeons/skullcano',
	'TorineDungeon'                    => 'dungeons/swordmaiden',
	'UltimateProtogamesJuniors'        => 'dungeons/protogames',
	'AugmentorsRaid'                   => 'raids/y83',
	'Datascape2'                       => 'raids/datascape', // edit
	'GeneticArchives'                  => 'raids/geneticarchives',
	'RedMoonTerror'                    => 'raids/redmoonterror',
	'ShiphandLevel6'                   => 'shiphands/fragmentzero',
	'ShiphandAsteroidMining'           => 'shiphands/m13', // edit
	'ShiphandDeepSpaceDisappearance'   => 'shiphands/deepspace',
	'ShiphandGauntlet'                 => 'shiphands/gauntlet',
	'ShiphandHungerFromtheVoid'        => 'shiphands/ether',
	'ShiphandInfestation'              => 'shiphands/infestation',
	'ShiphandRageLogic'                => 'shiphands/ragelogic',
	'ShiphandSpaceMadness'             => 'shiphands/spacemadness',
	'BattlegroundHallsoftheBloodsworn' => 'pvp/bloodsworn',
	'KevinVortexQuarry'                => 'pvp/walatiki',
	'PvPArena'                         => 'pvp/pvparena',
	'PvPArena2'                        => 'pvp/pvparena2',
	'WarplotSkyMap'                    => 'pvp/warplotskymap',
	'WarplotsMap'                      => 'pvp/warplotsmap',
	'CommunityHousing'                 => 'misc/communityhousing',
	'DruseraInstance4'                 => 'misc/druserainstance4',
	'ExcavationSabotage'               => 'misc/excavationsabotage',
	'GrimvaultCore'                    => 'misc/grimvaultcore',
	'HalonRingNew'                     => 'misc/halonringnew',
	'ProtostarWinterfest'              => 'misc/winterfest',
	'ShadesEveInstance'                => 'misc/shadeseve',
];

$tilerOptions = new ImagetilerOptions([
	// ImagetilerOptions
	'zoom_min'               => 0,
	'zoom_max'               => 8,
	'zoom_normalize'         => 7,
	'tms'                    => false,
	'fill_color'             => '#757575',
	'tile_format'            => 'png',
	'overwrite_tile_image'   => true,
#	'overwrite_base_image'   => true,
#	'clean_up'               => false,
	'fast_resize'            => false,
	'optimize_output'        => true,
	'resize_blur_upsample'   => 0.85,
	'resize_blur_downsample' => 0.7,
	'memory_limit'           => '8G',
]);

$optimizer_settings = [
	'execute_only_first_png_optimizer' => false,
	'advpng_bin'    => sprintf(utils, 'advpng'),
	'optipng_bin'   => sprintf(utils, 'optipng'),
	'pngcrush_bin'  => sprintf(utils, 'pngcrush'),
	'pngquant_bin'  => sprintf(utils, 'pngquant'),
	'execute_only_first_jpeg_optimizer' => false,
	'jpegoptim_bin' => sprintf(utils, 'jpegoptim'),
	'jpegtran_bin'  => sprintf(utils, 'jpegtran'),
];

$optimizer = (new OptimizerFactory($optimizer_settings, $logger))->get($tilerOptions->tile_format);
$map_tiler = new Imagetiler($tilerOptions, $optimizer, $logger);

foreach($maps as $map => $dir){

	try{
		$map_tiler->process(mapdir.'/'.$map.'.png', tiledir.'/'.$dir);
	}
	catch(ImagetilerException $e){
		echo $e->getMessage();
		echo $e->getTraceAsString();
	}

}
