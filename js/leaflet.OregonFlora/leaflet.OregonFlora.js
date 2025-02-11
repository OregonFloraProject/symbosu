// This file overloads and customizes the default Symbiota leaflet maps for OregonFlora.org
// It uses some additional Leaflet plugins: googleMutant and KML
// - James Mickley 2025-01-02

// Entrypoint function to add all OregonFlora customizations to the map
function addOregonFlora(map, fitBounds = true){

	// Show the map object
	//console.log(map);

	// Set the map zoom/center unless told not to
	if (fitBounds) {
		// Zoom/center to default place
		//map.mapLayer.flyTo({lat: 44.30413, lng: -122.55420}, 7); // Original map center for old spatial module
		//map.mapLayer.flyTo({lat: 44, lng: -124.5}, 7);
		map.mapLayer.fitBounds([
			[46.3, -126],
			[42, -116.4]
		]);

	}

	// Move the scale bar to the bottom right
	map.mapLayer.scaleControl.setPosition('bottomright');

	// Add/rename/reorder basemaps
	addBasemaps(map);

	// Add overlay layers
	addOverlays(map);

	// Set an event listener to trigger whenever the map is zoomed in or out
	map.mapLayer.on('zoomend', function (e) {

		// Get current zoom level
		let zoomLevel = map.mapLayer.getZoom();

		// Offload to the onZoom function
		onZoom(e, zoomLevel);
			
	});
}


// Add/rename/reorder basemap layers
function addBasemaps(map) {

	// Google satellite, without using API key
	const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
		attribution: 'Google',
		subdomains:['mt1','mt2','mt3'],
		maxZoom: 20, 
		noWrap:true,
		displayRetina:true,
		tileSize: 256,
		sort: 3,
	});

	// Google streets, without using API key
	const googleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
		attribution: 'Google',
		subdomains:['mt0','mt1','mt2','mt3'],
		maxZoom: 20, 
		noWrap:true,
		displayRetina:true,
		tileSize: 256,
		sort: 4,
	});

	// // Google streets, using API key via googleMutant (slow)
	// var googleStreets = L.gridLayer.googleMutant({
	//    type: "roadmap", // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
	//    attribution: "Google",
	//    noWrap: true,
	//    displayRetina:true,
	//    tileSize: 256,
	// });

	// // Legal google hybrid, using API key via using googleMutant (slow)
	// var googleHybrid = L.gridLayer.googleMutant({
	//    type: "hybrid", // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
	//    attribution: "Google",
	//    maxZoom: 21, // Levels 22-23 have issues for google mutant
	//    noWrap: true,
	//    displayRetina:true,
	//    tileSize: 256,
	// });

	var USGS_Topo = L.tileLayer('https://basemap.nationalmap.gov/arcgis/rest/services/USGSTopo/MapServer/tile/{z}/{y}/{x}', {
		displayRetina:true,
		maxZoom: 16,
		sort: 1,
		attribution: 'U.S. Department of the Interior | U.S. Geological Survey'
	});

	var USGS_Imagery = L.tileLayer('https://basemap.nationalmap.gov/arcgis/rest/services/USGSImageryTopo/MapServer/tile/{z}/{y}/{x}', {
		displayRetina:true,
		maxZoom: 16,
		sort: 7,
		attribution: 'U.S. Department of the Interior | U.S. Geological Survey',
	});

	var Esri_Topo = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
		sort: 6,
		attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community',
	});

	// Add additional baselayers to layers control
	map.mapLayer.layerControl.addBaseLayer(Esri_Topo, "ESRI Topo");
	map.mapLayer.layerControl.addBaseLayer(googleHybrid, "Google Satellite");
	map.mapLayer.layerControl.addBaseLayer(googleStreets, "Google Streets");
	map.mapLayer.layerControl.addBaseLayer(USGS_Topo, "USGS Topo");
	map.mapLayer.layerControl.addBaseLayer(USGS_Imagery, "USGS Satellite");

	// Rename existing layers
	map.mapLayer.layerControl._layers[0].name = "Google Terrain";
	map.mapLayer.layerControl._layers[1].name = "OpenStreetMap";
	map.mapLayer.layerControl._layers[2].name = "OpenTopoMap";
	map.mapLayer.layerControl._layers[3].name = "ESRI Satellite";

	// Fix Google Terrain attribution
	map.mapLayer.layerControl._layers[0].layer.options.attribution = "Google";

	// Add sort order to the existing layers
	map.mapLayer.layerControl._layers[0].layer.options.sort = 2;
	map.mapLayer.layerControl._layers[1].layer.options.sort = 8;
	map.mapLayer.layerControl._layers[2].layer.options.sort = 9;
	map.mapLayer.layerControl._layers[3].layer.options.sort = 5;

	// Set layercontrol options
	map.mapLayer.layerControl.options = {

		// Collapse the layers control to start
		collapsed: true, 

		// Sort the layers using a comparison function
		sortLayers: true, 
		sortFunction: function(layer1, layer2, name1, name2){
		
			// Return the difference of the two sorts: If positive, sort layer 1 after layer 2
			return layer1.options.sort - layer2.options.sort;
		}
	};

	// Bring macrostrat layer to the front so it's not behind the new basemaps
	map.mapLayer.macro_strat.setZIndex(11);

	// Change the default layer: first remove the old default, then re-add a layer.
	map.mapLayer.layerControl._layers[0].layer.remove();
	USGS_Topo.addTo(map.mapLayer);
}


// Add any overlay layers
function addOverlays(map) {

	// Get client root directory so that we can find files
	let thisScript = document.querySelector('script[src*="leaflet.OregonFlora.js"]');
	let clientRoot = thisScript.src.substring(window.location.origin.length).split('js/leaflet.OregonFlora')[0];
   
   	// Get counties geoJSON and add to map
	fetch(clientRoot + 'js/leaflet.OregonFlora/layers/oregon.counties.json')
		.then(response => response.json())
		.then(counties => {
			let countiesLayer = L.geoJson(counties, {

				// Style the polygon and borders
				style: {
					"color": "#3c641e",
					"weight": 3,
					"opacity": 0.8,
      				"fillOpacity": 0
      			},
      			// Add county name in center of county
				onEachFeature: function(feature, layer) {

               		// Add county names as tooltips
					layer.bindTooltip(feature.properties. Name, {permanent: true, direction: "center", className: "county-labels"});
				}
			// Add to the map
			}).addTo(map.mapLayer);

			// Add the counties layer to the layer controls 
			map.mapLayer.layerControl.addOverlay(countiesLayer, "Counties");
		});

	// Add ecoregions from KML using the KML plugin if not on the dynamicMap page:
	fetch(clientRoot + 'js/leaflet.OregonFlora/layers/ecoregions.kml')
		.then(res => res.text())
		.then(kmltext => {

			// Create new kml overlay
			const parser = new DOMParser();
			const kml = parser.parseFromString(kmltext, 'text/xml');
			const ecoregions = new L.KML(kml);

			// Add the ecoregions layer to the layer controls
			map.mapLayer.layerControl.addOverlay(ecoregions, "Ecoregions");

			// Adjust map to show the kml
			//const bounds = track.getBounds();
			//map.fitBounds(bounds);
		});
}


// Function that runs when the map is zoomed
function onZoom(e, zoomLevel) {
	//console.log("Zoomed to:", zoomLevel);

	// Adjust county label font size and display by zoom level
	if(zoomLevel > 7 && zoomLevel < 11) {
		$('.county-labels').css({'font-size': (zoomLevel + 3) + 'pt', display: 'block'});
	} else {
		$('.county-labels').css({'font-size': '1pt', display: 'none'});
	}
}


// Function to create custom SVG icons for points on the map (e.g., square, diamond)
// Copied in part from what Symbiota does for the observations icon
function getOregonFloraSvg(opts = {color: "#7A8BE7", size: 24, className: "", icon: "osc"}) {

	const default_ops = {color: "#7A8BE7", size: 24};
	opts = {...default_ops, ...opts};

	const half = opts.size/2;
	let markerIcon = '';

	// Choose which svg icon to use
	if(opts.icon == 'osc') {
		// Diamond
		markerIcon = `<polygon class="${opts.className}" points="${half},0 ${opts.size},${half} ${half},${opts.size} 0,${half}" ${opts.size},${opts.size} style="fill:${opts.color};stroke:black;stroke-width:3" />`;
	} else { // icon == 'ofphoto'
		// Square
		markerIcon = `<polygon class="${opts.className}" points="0,0 ${opts.size},0 ${opts.size},${opts.size} 0,${opts.size}" ${opts.size},${opts.size} style="fill:${opts.color};stroke:black;stroke-width:3" />`;

		// Plus
		//markerIcon = `<polygon class="${opts.className}" points="${opts.size/3},0 ${opts.size/3*2},0 ${opts.size/3*2},${opts.size/3} ${opts.size},${opts.size/3} ${opts.size},${opts.size/3*2} ${opts.size/3*2},${opts.size/3*2} ${opts.size/3*2},${opts.size} ${opts.size/3},${opts.size} ${opts.size/3},${opts.size/3*2} 0,${opts.size/3*2} 0,${opts.size/3} ${opts.size/3},${opts.size/3}" style="fill:${opts.color};stroke:black;stroke-width:3" />`;

	}

	return L.divIcon({
    	html: `
<svg
width="${opts.size}"
height="${opts.size}"
viewBox="-10 -10 ${opts.size + 20} ${opts.size + 20}"
version="1.1"
preserveAspectRatio="none"
xmlns="http://www.w3.org/2000/svg"
>
${markerIcon}
</svg>`,
		className: "",
		observation: true,
		iconSize: [opts.size, opts.size],
		iconAnchor: [half, half],
	});
}


// Function to allow turning sets of markers on and off (e.g. OSU herbarium, OF photos, etc.)
function toggleMarkers(type){

	// TODO for future: leafletMarkerCluster recommends using clearLayers() and then adding all the points back
	// This should be faster than using removeLayers. But it's fast enough for now. 

	// For each taxon, go through and add or remove points from clusters
	map.taxaClusters.forEach(cluster =>{

		// Get the list of markers for that taxon and type
		let markers = map.markerGroups[type][cluster.options.tid];
		
		// If the box is checked, add points if they exist
		if($('#' + type).is(':checked')) {
			if(markers) cluster.addLayers(markers);

		// Otherwise, remove points if they exist.
		} else {
			if(markers) cluster.removeLayers(markers);
		}
	})
}
     
// TODO:
// - Highlight counties with points
//   - This could be just a polygon styled like the counties but higher opacity
//   - either based on county name data (shaky), or on the points themselves (what about inaccurate points?)
// - KML import, select ecoregion to search
