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

$utils = __DIR__.'/../../../utils/%s.exe';

$maps = [
	'continents/alizar'        => 'Eastern.png',
	'continents/olyssia'       => 'Western.png',
	'continents/isigrol'       => 'NewCentral.png',
	'continents/arcterra'      => 'Arcterra.png',
	'continents/farside'       => 'Farsidesque.png',
	'adventures/galeras'       => 'AdventureGaleras.png',
	'adventures/hycrest'       => 'AdventureHycrest.png',
	'adventures/levianbay'     => 'AdventureLevianBay.png',
	'adventures/malgrave'      => 'AdventureMalgrave.png',
	'adventures/northernwilds' => 'AdventureNorthernWilds.png',
	'adventures/whitevale'     => 'AdventureWhitevale.png',
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
	'advpng_bin'    => sprintf($utils, 'advpng'),
	'optipng_bin'   => sprintf($utils, 'optipng'),
	'pngcrush_bin'  => sprintf($utils, 'pngcrush'),
	'pngquant_bin'  => sprintf($utils, 'pngquant'),
	'execute_only_first_jpeg_optimizer' => false,
	'jpegoptim_bin' => sprintf($utils, 'jpegoptim'),
	'jpegtran_bin'  => sprintf($utils, 'jpegtran'),
];

$optimizer = (new OptimizerFactory($optimizer_settings, $logger))->get($tilerOptions->tile_format);
$map_tiler = new Imagetiler($tilerOptions, $optimizer, $logger);

foreach($maps as $mapdir => $map){

	try{
		$map_tiler->process(
			__DIR__.'/maps/'.$map,
			__DIR__.'/../public/tiles/'.$mapdir
		);
	}
	catch(ImagetilerException $e){
		echo $e->getMessage();
		echo $e->getTraceAsString();
	}

}

