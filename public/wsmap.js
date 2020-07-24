/**
 * @filesource   wsmap.js
 * @created      24.10.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

class WsMap{

	options = {
		tileBase        : './tiles',
		zoom            : 4,
		minZoom         : 0,
		maxZoom         : 8,
		attribution     : 'Imagery &copy; <a href="https://twitter.com/carbine_studios" target="_blank">Carbine Studios</a>',
		errorTile       : 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQAAAAEACAYAAABccqhmAAADHUlEQVR4nO3UMQEAIAzAsIF/zyBjRxMFvXpm5g2QdLcDgD0GAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEGAGEfdCIC/5NkVo8AAAAASUVORK5CYII=',
	};

	// galeras: [[0,0], [9216, 10240]]
	// hycrest: [[0,0], [14336, 14336]]

	mapData = [
		// continents
		{name: 'Alizar', tiles: 'continents/alizar', size: [25600, 34816]},
		{name: 'Olyssia', tiles: 'continents/olyssia', size: [29696, 29696]},
		{name: 'Isigrol', tiles: 'continents/isigrol', size: [25600, 35840]},
		{name: 'Farside', tiles: 'continents/farside', size: [28672, 30720]},
		{name: 'Arcterra', tiles: 'continents/arcterra', size: [14336, 13312]},

		// adventures
		{name: 'Riot in the Void', tiles: 'adventures/astrovoid', size: [5120, 6144]},
		{name: 'War of the Wilds', tiles: 'adventures/northernwilds', size: [10240, 9216]},

		// dungeons
		{name: 'Stormtalon\'s Lair', tiles: 'dungeons/stormtalon', size: [4096, 3072]},
		{name: 'Ruins of Kel Voreth', tiles: 'dungeons/kelvoreth', size: [7168, 7168]},
		{name: 'Skullcano', tiles: 'dungeons/skullcano', size: [12288, 11264]},
		{name: 'Sanctuary of the Swordmaiden', tiles: 'dungeons/swordmaiden', size: [9216, 12288]},

		// raids
		{name: 'Genetic Archives', tiles: 'raids/geneticarchives', size: [14336, 10240]},
		{name: 'Datascape', tiles: 'raids/datascape', size: [42496, 35840]},
		{name: 'Initialization Core Y-83', tiles: 'raids/y83', size: [1024, 2048]},
		{name: 'Redmoon Terror', tiles: 'raids/redmoonterror', size: [9216, 12288]},

		// shiphands
		{name: 'Fragment Zero', tiles: 'shiphands/fragmentzero', size: [4096, 6144]},
		{name: 'Outpost M-13', tiles: 'shiphands/m13', size: [4096, 4096]},
		{name: 'Infestation', tiles: 'shiphands/infestation', size: [2048, 3072]},
		{name: 'Evil from the Ether', tiles: 'shiphands/ether', size: [5120, 6144]},
		{name: 'Rage Logic', tiles: 'shiphands/ragelogic', size: [8192, 9216]},
		{name: 'Space Madness', tiles: 'shiphands/spacemadness', size: [4096, 4096]},
		{name: 'Deep Space Exploration', tiles: 'shiphands/deepspace', size: [8192, 8192]},
		{name: 'Gauntlet', tiles: 'shiphands/gauntlet', size: [6144, 7168]},

		// pvp
		{name: 'Halls of the Bloodsworn', tiles: 'pvp/bloodsworn', size: [6144, 5120]},
		{name: 'Walatiki Temple', tiles: 'pvp/walatiki', size: [8192, 8192]},
		{name: 'PvPArena', tiles: 'pvp/pvparena', size: [4096, 4096]},
		{name: 'PvPArena2', tiles: 'pvp/pvparena2', size: [4096, 2048]},
		{name: 'WarplotSkyMap', tiles: 'pvp/warplotskymap', size: [6144, 6144]},
		{name: 'WarplotsMap', tiles: 'pvp/warplotsmap', size: [12288, 22528]},

		// misc stuff
		{name: 'CommunityHousing', tiles: 'misc/communityhousing', size: [7168, 7168]},
		{name: 'DruseraInstance4', tiles: 'misc/druserainstance4', size: [9216, 8192]},
		{name: 'ExcavationSabotage', tiles: 'misc/excavationsabotage', size: [7168, 8192]},
		{name: 'GrimvaultCore', tiles: 'misc/grimvaultcore', size: [10240, 11264]},
		{name: 'HalonRingNew', tiles: 'misc/halonringnew', size: [25600, 23552]},
		{name: 'Shades Eve', tiles: 'misc/shadeseve', size: [7168, 7168]},
		{name: 'Winterfest', tiles: 'misc/winterfest', size: [4096, 3072]},
	];

	tileLayers = {};

	constructor(container, options){

		for(let property in options){
			if(options.hasOwnProperty(property) && this.options.hasOwnProperty(property)) {
				this.options[property] = options[property];
			}
		}

		this.map = L.map(container, {
			crs               : L.CRS.Simple,
			minZoom           : this.options.minZoom,
			maxZoom           : this.options.maxZoom,
			zoomControl       : true,
		});

		this.map.setMaxBounds(this._getBounds([[0,0], [49152, 49152]]).pad(0.25));
		this.mapData.forEach(m => this._createTileLayer(m));
		this.controls = L.control.layers(this.tileLayers, null, {collapsed: false}).addTo(this.map);
	}

	_createTileLayer(map){

		let tileLayerOptions = {
			continuousWorld : true,
			bounds          : this._getBounds([[0,0], map.size]),
			minZoom         : this.options.minZoom,
			maxZoom         : this.options.maxZoom,
			attribution     : this.options.attribution,
			errorTileUrl    : this.options.errorTile,
			tilebase        : this.options.tileBase,
			maptiles        : map.tiles,
		};

		this.tileLayers[map.name] = L.tileLayer('{tilebase}/{maptiles}/{z}/{x}/{y}.png', tileLayerOptions);

		if (map.name === 'Isigrol'){
			this.map.fitBounds(tileLayerOptions.bounds).setView(tileLayerOptions.bounds.getCenter(), this.options.zoom);
			this.tileLayers[map.name].addTo(this.map);
		}
	}

	_getBounds(rect){
		return new L.LatLngBounds([
			this.map.unproject([rect[0][0], rect[1][1]], this.options.maxZoom),
			this.map.unproject([rect[1][0], rect[0][1]], this.options.maxZoom)
		]);
	}

}
