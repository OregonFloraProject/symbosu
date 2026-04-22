// This file overloads and customizes the default Symbiota leaflet maps for OregonFlora.org
// It uses some additional Leaflet plugins: googleMutant and KML
// - James Mickley 2025-01-02

// Entrypoint function to add all OregonFlora customizations to the map
function addOregonFlora(map, fitBounds = true){
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

	if (typeof MAP_KML_IMPORT_FLAG !== 'undefined' && MAP_KML_IMPORT_FLAG) {
		setUpUserFiles(map);
	}
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

	// remove default expand/collapse handlers and add new ones with a timeout on mouseleave;
	// otherwise it's too easy to collapse the control while trying to use it
	L.DomEvent.off(map.mapLayer.layerControl.getContainer(), 'mouseenter');
	L.DomEvent.off(map.mapLayer.layerControl.getContainer(), 'mouseleave');
	let timeout;
	L.DomEvent.on(map.mapLayer.layerControl.getContainer(), 'mouseenter', () => {
		if (timeout) {
			clearTimeout(timeout);
			timeout = null;
		}
		map.mapLayer.layerControl.expand();
	});
	L.DomEvent.on(map.mapLayer.layerControl.getContainer(), 'mouseleave', () => {
		timeout = setTimeout(() => map.mapLayer.layerControl.collapse(), 300);
	});

	// Bring macrostrat layer to the front so it's not behind the new basemaps
	map.mapLayer.macro_strat.setZIndex(11);

	// Change the default layer: first remove the old default, then re-add a layer.
	map.mapLayer.layerControl._layers[0].layer.remove();
	USGS_Topo.addTo(map.mapLayer);
}

function addLandOwnershipOverlays(map, clientRoot) {
	const lands = [
		{ label: 'BLM', file: 'blm_coordinates.geojson', color: '#f5e723' },
		{ label: 'USFS', file: 'USFS_coordinates.geojson', color: '#23f52e' },
		{ label: 'State of OR',  file: 'OR_coordinates.geojson',  color: '#23e7f5' },
		{ label: 'Local Govt',  file: 'Local_Govt_coordinates.geojson',  color: '#A9D5B8' },
		{ label: 'Other Federal',  file: 'Other_Federal_coordinates.geojson',  color: '#1A9B8E' },
		{ label: 'Tribal',  file: 'Tribal_coordinates.geojson',  color: '#4B0082' },
		// { label: 'Private',  file: 'Private_coordinates.geojson',  color: '#C1666B' },
	];

	const groups = {};  // label -> layerGroup mapping for grouping function

	lands.forEach(function({ label, file, color }) {
		// Add overlay that is lazy-loaded on first toggle-on
		let group = L.layerGroup();
		let data = null;  // cached after first fetch, kept in memory across toggles

		const layerOptions = {
			style: {
				color: color,
				weight: 2,
				opacity: 0.8,
				fillColor: color,
				fillOpacity: label !== 'Private' ? 0.4 : 0
			},
			onEachFeature: function(feature, layer) {
				let p = feature.properties;
				layer.bindPopup(
					'<strong>' + (p.FeeTitleHolder || 'Unknown') + '</strong><br>' +
					'Land Owner: ' + label
				);
			}
		};

		map.mapLayer.on('overlayadd', function(e) {
			if (e.layer !== group) return;
			if (data) {
				// Data already fetched: just re-render into the group
				L.geoJson(data, layerOptions).addTo(group);
				return;
			}
			fetch(clientRoot + 'js/leaflet.OregonFlora/layers/' + file)
				.then(response => response.json())
				.then(function(fetched) {
					data = fetched;
					L.geoJson(data, layerOptions).addTo(group);
				});
		});

		map.mapLayer.on('overlayremove', function(e) {
			if (e.layer !== group) return;
			group.clearLayers();  // free Leaflet/DOM objects; data stays in memory
		});

		map.mapLayer.layerControl.addOverlay(group, label);
		groups[label] = group;
	});

	// Group the three land layers under a "Land Owner" parent checkbox
	groupLandLayers(map, groups);
}

// Group land ownership layers under a "Land Owner" parent checkbox
function groupLandLayers(map, groups) {
	const control = map.mapLayer.layerControl;

	// Function to apply grouping to the current DOM state
	function applyLandOwnerGrouping() {
		const overlaysList = control._overlaysList;
		if (!overlaysList) return;  // control not yet rendered

		// Get the land ownership layers to a separate list landOwnLayers
		// for state tracking
		const landOwnLayers = {};
		overlaysList.querySelectorAll('label').forEach(label => {
			const span = label.querySelector('span');
			if (span) {
				const text = span.textContent.trim();
				if (groups[text]) {
					landOwnLayers[text] = label;
				}
			}
		});

		// Check if grouping already applied (look for existing parent)
		if (overlaysList.querySelector('.leaflet-land-owner-parent-checkbox')) {
			return;
		}

		// Create parent checkbox row: "Land Owner" with checkbox (match other overlays structure)
		const parentLabel = document.createElement('label');
		const parentCheckbox = document.createElement('input');
		parentCheckbox.type = 'checkbox';
		parentCheckbox.className = 'leaflet-control-layers-selector leaflet-land-owner-parent-checkbox';
		parentCheckbox.checked = false;

		const parentSpan = document.createElement('span');
		parentSpan.textContent = ' Land Owner';

		parentLabel.appendChild(parentCheckbox);
		parentLabel.appendChild(parentSpan);

		// Create indented container for child checkboxes
		const childrenDiv = document.createElement('div');
		childrenDiv.className = 'leaflet-land-owner-children';

		// Move land layer <label> elements into children container, in defined order
		Object.keys(landOwnLayers).forEach(name => {
			childrenDiv.appendChild(landOwnLayers[name]);
		});

		// Insert parent and children at end of overlays list
		overlaysList.appendChild(parentLabel);
		overlaysList.appendChild(childrenDiv);

		// Update parent checkbox state based on which child layers are currently on the map
		function updateParentCheckboxState() {
			const landLayerNames = Object.keys(groups);
			const checkedCount = landLayerNames.filter(name => map.mapLayer.hasLayer(groups[name])).length;
			const totalCount = landLayerNames.length;

			if (checkedCount === 0) {
				parentCheckbox.checked = false;
				parentCheckbox.indeterminate = false;
			} else if (checkedCount === totalCount) {
				parentCheckbox.checked = true;
				parentCheckbox.indeterminate = false;
			} else {
				parentCheckbox.checked = false;
				parentCheckbox.indeterminate = true;
			}
		}

		// Listen for overlayadd/overlayremove on land layers to update parent state
		map.mapLayer.on('overlayadd overlayremove', function(e) {
			if (Object.values(groups).includes(e.layer)) {
				updateParentCheckboxState();
			}
		});

		// Wire up parent checkbox: toggle all three child layers
		parentCheckbox.addEventListener('click', function(e) {
			e.stopPropagation();
			const shouldAdd = this.checked;

			Object.entries(landOwnLayers).forEach(([name, childCheckbox]) => {
				if (shouldAdd) {
					groups[name].addTo(map.mapLayer);
					childCheckbox.checked = true;
				} else {
					map.mapLayer.removeLayer(groups[name]);
					childCheckbox.checked = false;
				}
			});
		});
		
		// Initial state
		updateParentCheckboxState();
	}

	// Override the layer control's _update method to reapply grouping after Leaflet rebuilds
	// Prevent "Land Owner" checkbox from disappearing when clicked on
	const originalUpdate = control._update.bind(control);
	control._update = function() {
		originalUpdate();
		applyLandOwnerGrouping();
	};

	// Inject CSS for indentation (only once)
	// Id "leaflet-land-owner-styles" avoids duplicating head CSS
	if (!document.getElementById('leaflet-land-owner-styles')) {
		const style = document.createElement('style');
		style.id = 'leaflet-land-owner-styles';
		style.textContent = `
			.leaflet-land-owner-children {
				padding-left: 15px;
			}
			.leaflet-land-owner-parent-checkbox {
				cursor: pointer;
			}
		`;
		document.head.appendChild(style);
	}

	// Apply initial grouping
	applyLandOwnerGrouping();
}

// Add any overlay layers
function addOverlays(map) {

	// Get client root directory so that we can find files
	let thisScript = document.querySelector('script[src*="leaflet.OregonFlora.js"]');
	let clientRoot = thisScript.src.substring(window.location.origin.length).split('js/leaflet.OregonFlora')[0];
   
	// Add counties layer, on by default; DOM cleared when unchecked, data kept in memory
	let countiesGroup = L.layerGroup();
	let countiesData = null;  // cached after first fetch, kept across toggles
	
	const countiesLayerOptions = {
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
			layer.bindTooltip(feature.properties.Name, {permanent: true, direction: "center", className: "county-labels"});
		}
	};

	map.mapLayer.on('overlayadd', function(e) {
		if (e.layer !== countiesGroup) return;
		if (countiesData) {
			// Data already fetched, just re-render into the group
			L.geoJson(countiesData, countiesLayerOptions).addTo(countiesGroup);
			return;
		}
		fetch(clientRoot + 'js/leaflet.OregonFlora/layers/oregon.counties.json')
			.then(response => response.json())
			.then(data => {
				countiesData = data;
				L.geoJson(countiesData, countiesLayerOptions).addTo(countiesGroup);
			});
	});

	map.mapLayer.on('overlayremove', function(e) {
		if (e.layer !== countiesGroup) return;
		countiesGroup.clearLayers();  // free DOM; countiesData stays in memory
	});

	// Add to map first (so it appears checked), then to layer control
	countiesGroup.addTo(map.mapLayer);
	map.mapLayer.layerControl.addOverlay(countiesGroup, "Counties");

	// Load counties data immediately for the default-on state
	fetch(clientRoot + 'js/leaflet.OregonFlora/layers/oregon.counties.json')
		.then(response => response.json())
		.then(data => {
			countiesData = data;
			L.geoJson(countiesData, countiesLayerOptions).addTo(countiesGroup);
		});

	// Add ecoregions layer from KML using the KML plugin if not on the dynamicMap page.
	// Lazy loaded on first toggle-on
	let ecoregionsGroup = L.layerGroup();
	let ecoregionsText = null;  // cached KML text after first fetch

	map.mapLayer.on('overlayadd', function(e) {
		if (e.layer !== ecoregionsGroup) return;
		if (ecoregionsText) {
			const kml = new DOMParser().parseFromString(ecoregionsText, 'text/xml');
			let layer = new L.KML(kml);
			processLayersAndPopups(layer, false);
			layer.addTo(ecoregionsGroup);
			return;
		}
		fetch(clientRoot + 'js/leaflet.OregonFlora/layers/ecoregions.kml')
			.then(res => res.text())
			.then(kmltext => {
				ecoregionsText = kmltext;
				const kml = new DOMParser().parseFromString(ecoregionsText, 'text/xml');
				let layer = new L.KML(kml);
				processLayersAndPopups(layer, false);
				layer.addTo(ecoregionsGroup);
			});
	});

	map.mapLayer.on('overlayremove', function(e) {
		if (e.layer !== ecoregionsGroup) return;
		ecoregionsGroup.clearLayers();
	});

	map.mapLayer.layerControl.addOverlay(ecoregionsGroup, 'Ecoregions');

	addLandOwnershipOverlays(map, clientRoot);
}

let _userAddedKMLLayers = [];

function addKMLLayer(text, name, map, userAdded = true) {
	const parser = new DOMParser();
	const kml = parser.parseFromString(text, 'text/xml');
	let layer = new L.KML(kml);

	// Check all the layers in the KML and remove non-polygon layers.
	// If there are no polygons in the KML, abort and alert the user
	if (!processLayersAndPopups(layer, userAdded)) {
		alert('No polygons were present in the KML file. To search using a KML file, make sure it contains at least one polygon');
		return false;
	}

	if (typeof MAP_KML_IMPORT_FLAG !== 'undefined' && MAP_KML_IMPORT_FLAG) {
		// select on click for user-added polygons, on double-click for ours (since they have a popup)
		const selectEvent = userAdded ? 'click' : 'dblclick';
		layer.on(selectEvent, (event) => {
			if (event.layer instanceof L.Polygon) {
				map.clearMap();
				map.drawShape({ type: 'polygon', latlngs: event.layer.getLatLngs()[0] });
				setQueryShape(getShapeCoords('polygon', event.layer));
			}
		});
	}

	// Add to layer controls
	map.mapLayer.layerControl.addOverlay(layer, name);

	if (userAdded) {
		// The user has added this file to the map themselves; we standardize the style before
		// displaying it in case the default style is unhelpful or poorly defined.
		layer.setStyle({
			stroke: true,
			color: '#000000',
			weight: 1.5,
			fill: true,
			fillColor: '#aaaaaa',
			fillOpacity: 0.2
		});
		layer.addTo(map.mapLayer);
		map.mapLayer.fitBounds(layer.getBounds());
		_userAddedKMLLayers.push(layer);
	}

	return true;
}

function clearKMLLayers(map) {
	while (_userAddedKMLLayers.length) {
		const layer = _userAddedKMLLayers.pop();
		layer.remove();
		map.mapLayer.layerControl.removeLayer(layer);
	}
	$('#kmlinstructions').hide();
	$('#shapetoolsinstructions').show();
}

// Function to remove non-polygon layers from a KML LayerGroup
// and modify or remove the popups
function processLayersAndPopups(layers, userAdded) {
	let hasPolygon = false;
	// Get an array of layers and iterate over it
	layers.getLayers().forEach((layer) => {

		// If it's a LayerGroup, recurse
		if(layer instanceof L.LayerGroup) {
			hasPolygon = processLayersAndPopups(layer, userAdded);

		// It's a polygon, so the KML has at least one polygon
		} else if (layer instanceof L.Polygon) {
			hasPolygon = true;

			// Remove layer popup for user-added KML files
			if (userAdded) {
				layer.unbindPopup();
			} else if (typeof MAP_KML_IMPORT_FLAG !== 'undefined' && MAP_KML_IMPORT_FLAG) {
				// for our KML files, add double-click instructions to popup
				const popupContent = layer?.getPopup()?.getContent();
				if (popupContent) {
					const title = popupContent.substring(0, popupContent.indexOf('</h2>') + 5);
					layer.setPopupContent(`${title}Double-click to search this polygon`);
				}
			}

		// If it's not a layer group and not a polygon, remove it
		} else {
			layers.removeLayer(layer);
		}
	});
	return hasPolygon;
}

function setUpUserFiles(map) {
	// hook up file upload input to map object
	document.addEventListener('fileinput', (event) => {
		if (event?.detail?.file) {
			processFile(event.detail.file, map);
		}
	});

	// set up drag & drop
	document.getElementById('site-content').ondrop = (event) => {
		event.preventDefault();

		if (event.dataTransfer.items) {
			[...event.dataTransfer.items].forEach((item) => {
				// If dropped items aren't files, ignore them
				if (item.kind === 'file') {
					const file = item.getAsFile();
					processFile(file, map);
				}
			});
		} else {
			[...event.dataTransfer.files].forEach((file) => {
				processFile(file, map);
			});
		}
	};

	document.getElementById('site-content').ondragover = (event) => {
		event.preventDefault();
	};

	document.addEventListener('clearkmllayers', () => {
		clearKMLLayers(map);
	});
}

function processFile(file, map) {
	const filenameComponents = file.name.split('.');
	const type = filenameComponents.pop();
	const name = filenameComponents.join('');
	if (type.toLowerCase() === 'kml') {
		file.text().then((text) => {
			const success = addKMLLayer(text, name, map);
			if (success) {
				userAddedKML = true;
				changeSelectInstructions();
			} else {
				alert('The KML file you uploaded has no valid polygons.');
			}
		});
		return true;
	}
	// TODO: add geojson, shp, dbf support
	return false;
}

// listener for file input form element
function onFileInputChange(element) {
	if (element?.files?.[0]) {
		document.dispatchEvent(new CustomEvent('fileinput', { detail: { file: element.files[0] } }));
	}
}

function changeSelectInstructions() {
	$('#tabs1').tabs('option', 'active', 1);
	$('#shapetoolsinstructions').hide();
	$('#kmlinstructions').show();
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
	} else if(opts.icon == 'inat') {
		// Plus
		markerIcon = `<polygon class="${opts.className}" points="${opts.size/3},0 ${opts.size/3*2},0 ${opts.size/3*2},${opts.size/3} ${opts.size},${opts.size/3} ${opts.size},${opts.size/3*2} ${opts.size/3*2},${opts.size/3*2} ${opts.size/3*2},${opts.size} ${opts.size/3},${opts.size} ${opts.size/3},${opts.size/3*2} 0,${opts.size/3*2} 0,${opts.size/3} ${opts.size/3},${opts.size/3}" style="fill:${opts.color};stroke:black;stroke-width:3" />`;
	} else { // icon == 'ofphoto'
		// Square
		markerIcon = `<polygon class="${opts.className}" points="0,0 ${opts.size},0 ${opts.size},${opts.size} 0,${opts.size}" ${opts.size},${opts.size} style="fill:${opts.color};stroke:black;stroke-width:3" />`;
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

/**
 * Customized version of LeafletMapGroup that clusters all layers together in a single cluster
 * instead of having overlapping, per-layer (e.g. per-taxon) clusters. As much as possible, this is
 * meant to be a drop-in replacement for LeafletMapGroup and has the same API.
 * 
 * Since the map object and heatmap state are not available in the scope of the class declaration,
 * we need a few extra parameters in the constructor.
 */
class LeafletSingleClusterMapGroup {
	map;
	markers = {};
	layer_groups = {};
	group_name;
	group_map;
	cluster;
	getIsHeatmapEnabled;

	constructor(group_name, group_map, map, getIsHeatmapEnabled) {
		this.group_name = group_name;
		this.group_map = group_map;
		this.map = map;
		this.getIsHeatmapEnabled = getIsHeatmapEnabled;
	}

	addMarker(id, marker) {
		if (!this.markers[id]) {
			this.markers[id] = [marker];
		} else {
			this.markers[id].push(marker);
		}
	}

	genLayer(id, cluster) {
		// noop, moved functionality to genClusters
	}

	drawGroup() {
		if (clusteroff) {
			for (let id in this.group_map) {
				this.layer_groups[id].addTo(this.map.mapLayer);
			}
		} else if (!this.map.mapLayer.hasLayer(this.cluster)) {
			// 2025-03-21(eric): see below comment in `removeGroup`; the clearLayers() call there forces
			// us to re-add all the layers here (e.g. when toggling heatmap)
			for (let id in this.layer_groups) {
				this.cluster.addLayer(this.layer_groups[id]);
			}
			this.cluster.addTo(this.map.mapLayer);
		}
	}

	removeGroup() {
		if (clusteroff) {
			for (let id in this.group_map) {
				this.map.mapLayer.removeLayer(this.layer_groups[id]);
			}
		} else {
			this.map.mapLayer.removeLayer(this.cluster);
			// 2025-03-21(eric): this is necessary for the "reset symbology" buttons to work correctly,
			// otherwise clusters sometimes double in size; I haven't yet figured out why
			this.cluster.clearLayers();
		}
	}

	resetGroup() {
		this.cluster.clearLayers();
		for (let id of Object.keys(this.group_map)) {
			this.layer_groups[id].clearLayers();
			this.markers[id] = [];
		}
	}

	removeLayer(id) {
		this.cluster.removeLayer(this.layer_groups[id]);
		this.map.mapLayer.removeLayer(this.cluster);
	}

	addLayer(id) {
		// First, add layer as both regular layer group and to cluster
		this.layer_groups[id] = L.layerGroup(this.markers[id]);
		this.cluster.addLayer(this.layer_groups[id]);

		// Then, decide which is visible
		if (!this.getIsHeatmapEnabled()) {
			if (clusteroff) {
				this.map.mapLayer.addLayer(this.layer_groups[id]);
			} else if (!this.map.mapLayer.hasLayer(this.cluster)) {
				this.cluster.addTo(this.map.mapLayer);
			}
		}
	}

	toggleClustering() {
		if (clusteroff) {
			if (this.map.mapLayer.hasLayer(this.cluster)) {
				this.map.mapLayer.removeLayer(this.cluster);
			}
			for (let id in this.group_map) {
				this.map.mapLayer.addLayer(this.layer_groups[id]);
			}
		} else {
			for (let id in this.group_map) {
				this.map.mapLayer.removeLayer(this.layer_groups[id]);
			}
			if (!this.map.mapLayer.hasLayer(this.cluster)) {
				this.cluster.addTo(this.map.mapLayer);
			}
		}
	}

	genClusters() {
		const clusterRendered =
			this.cluster && this.map.mapLayer.hasLayer(this.cluster);
		if (clusterRendered) {
			this.map.mapLayer.removeLayer(this.cluster);
		}

		const firstId = Object.keys(this.group_map)[0]; // just use first taxon color
		this.cluster = L.markerClusterGroup({
			iconCreateFunction: (cluster) => {
				// this has to be accessed inside the function in order to get changes from updateColor --
				// which is very janky, but it works like this in the original LeafletMapGroup
				const clusterColor = this.group_map[firstId].color;
				let childCount = cluster.getChildCount();
				cluster.bindTooltip(
					`<div style="font-size:1rem">Click to expand</div>`
				);
				cluster.on('click', (e) => e.target.spiderfy());
				return new L.DivIcon.CustomColor({
					html:
						`<div class="symbiota-cluster" style="background-color: #${clusterColor};"><span>` +
						childCount +
						'</span></div>',
					className: `symbiota-cluster-div`,
					iconSize: new L.Point(20, 20),
					color: `#${clusterColor}77`,
					mainColor: `#${clusterColor}`,
				});
			},
			maxClusterRadius: cluster_radius,
			zoomToBoundsOnClick: false,
			chunkedLoading: true,
		});

		for (let id in this.group_map) {
			if (!this.layer_groups[id]) {
				this.layer_groups[id] = L.layerGroup(this.markers[id]);
			}
			this.cluster.addLayer(this.layer_groups[id]);
		}

		if (!clusteroff && clusterRendered) {
			this.cluster.addTo(this.map.mapLayer);
		}
	}

	updateColor(id, color) {
		this.group_map[id].color = color;

		for (let marker of this.markers[id]) {
			if (marker.options.icon && marker.options.icon.options.observation) {
				marker.setIcon(getObservationSvg({ color: `#${color}`, size: 30 }));
			} else {
				marker.setIcon(getSpecimenSvg({color: `#${color}`, size: 8 }));
			}
		}
	}
}
     
// TODO:
// - Highlight counties with points
//   - This could be just a polygon styled like the counties but higher opacity
//   - either based on county name data (shaky), or on the points themselves (what about inaccurate points?)
// - Select KML region to search
