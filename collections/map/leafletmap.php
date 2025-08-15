<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceMapManager.php');
include_once($SERVER_ROOT.'/content/lang/collections/map/simplemap.'.$LANG_TAG.'.php');
if($LANG_TAG != 'en' && file_exists($SERVER_ROOT.'/content/lang/collections/map/leafletmap.' . $LANG_TAG . '.php')) include_once($SERVER_ROOT.'/content/lang/collections/map/leafletmap.' . $LANG_TAG . '.php');
else include_once($SERVER_ROOT . '/content/lang/collections/map/leafletmap.en.php');


header("Content-Type: text/html; charset=".$CHARSET);

$clid = array_key_exists('clid',$_REQUEST)?$_REQUEST['clid']:0;
$gridSize = array_key_exists('gridSizeSetting',$_REQUEST)?$_REQUEST['gridSizeSetting']:10;
$minClusterSize = array_key_exists('minClusterSetting',$_REQUEST)?$_REQUEST['minClusterSetting']:50;

$occurManager = new OccurrenceMapManager();
$coordArr = $occurManager->getMappingData(0);

//Build taxa mapping key
$taxaKey = Array();
$taxaArr = $occurManager->getTaxaArr();
if(array_key_exists('taxa', $taxaArr)){
	foreach($taxaArr['taxa'] as $scinameStr => $snArr){
		if(isset($snArr['tid'])){
			$snTid = key($snArr['tid']);
			$taxaKey[$snTid]['t'] = $scinameStr;
			if(array_key_exists('TID_BATCH', $snArr)){
				foreach($snArr['TID_BATCH'] as $synTid => $synValue){
					$taxaKey[$synTid]['s'] = $snTid;
				}
			}
			if(array_key_exists('synonyms', $snArr)){
				foreach($snArr['synonyms'] as $synTid => $synSciname){
					$taxaKey[$synTid]['s'] = $snTid;
					$taxaKey[$synTid]['t'] = $synSciname;
				}
			}
		}
	}
}

$markerCnt = 0;
$spCnt = 0;
$minLng = 180; $minLat = 90; $maxLng = -180; $maxLat = -90;
$defaultColor = 'B2BEB5';
$iconColors = array('FC6355','5781FC','FCf357','00E13C','E14f9E','55D7D7','FF9900','7E55FC');
$legendArr = Array();
foreach($coordArr as $sciName => $valueArr){
   $tid = 0;
   if(array_key_exists('tid', $valueArr)){
      $tid = $valueArr['tid'];
      unset($valueArr['tid']);
      if(isset($taxaKey[$tid])){
         if(isset($taxaKey[$tid]['s'])){
            $correctedTid = $taxaKey[$tid]['s'];
            if(isset($taxaKey[$tid]['t'])) $legendArr[$correctedTid]['s'][] = $taxaKey[$tid]['t'];
            $tid = $correctedTid;
         }
         if(!isset($legendArr[$tid]['t'])){
            $legendArr[$tid]['t'] = $taxaKey[$tid]['t'];
            $legendArr[$tid]['c'] = $iconColors[(count($legendArr)%8)];
         }
      }
   }
   $iconColor = 0;
   if(isset($legendArr[$tid])){
      $iconColor = $legendArr[$tid]['c'];
      $legendArr[$tid]['points'][$spCnt] = $valueArr;
   }
   else{
      foreach($legendArr as $lTid => $legArr){
         if(isset($legArr['t']) && strpos($sciName, $legArr['t']) === 0){
            $iconColor = $legArr['c'];
            $legendArr[$lTid]['points'][$spCnt] = $valueArr;
            break;
         }
      }
      if(!$iconColor){
         foreach($taxaKey as $tkTid => $tkArr){
            if(isset($tkArr['t']) && strpos($sciName, $tkArr['t']) === 0){
               $legendArr[$tkTid]['t'] = $tkArr['t'];
               $iconColor = $iconColors[(count($legendArr)%8)];
               $legendArr[$tkTid]['c'] = $iconColor;
               $legendArr[$tkTid]['points'][$spCnt] = $valueArr;
               break;
            }
         }
      }
      if(!$iconColor){
         $legendArr['last']['c'] = $defaultColor;
         $legendArr['last']['points'][$spCnt] = $valueArr;
         $iconColor = $defaultColor;
      }
   }
   $spCnt++;
}

//Loop Through all coords have color, pos and display string
$boundLatMin = -90;
$boundLatMax = 90;
$boundLngMin = -180;
$boundLngMax = 180;
$latCen = 41.0;
$longCen = -95.0;
if(isset($MAPPING_BOUNDARIES)){
   $coorArr = explode(";",$MAPPING_BOUNDARIES);
   if($coorArr && count($coorArr) == 4){
      $boundLatMin = $coorArr[2];
      $boundLatMax = $coorArr[0];
      $boundLngMin = $coorArr[3];
      $boundLngMax = $coorArr[1];
      $latCen = ($boundLatMax + $boundLatMin)/2;
      $longCen = ($boundLngMax + $boundLngMin)/2;
   }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $LANG_TAG ?>">
<head>
	<title><?php echo $DEFAULT_TITLE; ?> - <?php echo $LANG['LEAFLET_MAP']; ?></title>
	<?php
	   include_once($SERVER_ROOT.'/includes/head.php');
	   include_once($SERVER_ROOT.'/includes/leafletMap.php');
	?>
	<style type="text/css">
		legend {
			font-size: 1.2rem !important;
			font-weight: bold;
		}
		#service-container {
			position: relative;
		}
		#panel {
			height: 100%;
			width: 20rem;
			max-width: 100%;
			position: absolute;
			z-index: 20;
			top: 0;
			left: 0;
			padding: 0.5rem;
			background-color: #ffffff;
			box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.16);
			overflow: scroll;
			transition: left 0.5s;
			transition-timing-function: ease;
		}
		@media (max-width: 576px) {
			#panel {
				left: -20rem;
			}
		}
		.panel-heading {
			font-size: 1.5rem;
			padding: 0.6rem 1rem 1rem 1rem;
			color: #5fb021;
			display: flex;
		}
		.panel-heading svg {
			width: 0.875em;
		}
		.panel-heading a {
			color: #5fb021 !important;
		}
		.panel-heading a:hover {
			color: black !important;
		}
		.legend-button {
			position: absolute;
			left: 0;
			margin: 10px;
			z-index: 10;
			gap: 0.2rem;
			border: 2px solid rgba(0,0,0,0.2) !important;
		}
		#legend div {
			text-indent: 1.7rem hanging;
		}
	</style>
	<script type="text/javascript">
      let occurCoords;
      let colorLegend;
      let clid;
      let map;
		let useLLDecimal = true;

      function leafletInit() {
         L.DivIcon.CustomColor = L.DivIcon.extend({
            createIcon: function(oldIcon) {
               var icon = L.DivIcon.prototype.createIcon.call(this, oldIcon);
               icon.style.backgroundColor = this.options.color;
               return icon;
            }
         })

         let bounds = new L.featureGroup();

         map = new LeafletMap('map_canvas');
				 map.mapLayer.zoomControl.setPosition('topright');

				// Add all the OregonFlora leaflet customizations
				addOregonFlora(map);

         const checkLatLng = (latlng) => {
            return (
               (!isNaN(latlng[0]) && latlng[0] <= 90 && latlng[0] >= -90) &&
               (!isNaN(latlng[1]) && latlng[1] <= 180 && latlng[1] >= -180)
            )
         }

         for(let tid of Object.keys(colorLegend)) {
            let colorGroup = colorLegend[tid]

            //Leaftlet Cluster Override
            function colorCluster(cluster) {
               let childCount = cluster.getChildCount();
               return new L.DivIcon.CustomColor({
               html: `<div style="background-color: #${colorGroup.c}CC;"><span>` + childCount + '</span></div>',
                  className: 'marker-cluster',
                  iconSize: new L.Point(40, 40),
                  // OregonFlora: remove transparent outer border of cluster icons
                  //color: `#${colorGroup.c}77`, // Old
                  color: `#${colorGroup.c}00`,
               });
            }

            let taxaCluster = L.markerClusterGroup({
               iconCreateFunction: colorCluster,
               tid: tid, // Save the taxon ID to the cluster
               maxClusterRadius: <?php echo $gridSize . ',' ?>
            });

            for(let groupId of Object.keys(colorGroup.points)) {
               let taxaGroup = colorGroup.points[groupId];
               for(let occid of Object.keys(taxaGroup)) {
                  const occur = taxaGroup[occid];
                  const latlng = [parseFloat(occur.lat), parseFloat(occur.lng)];
                  // let displayStr = `${occur.instcode}`;

                  // // Account for missing collection codes
                  // if(occur.collcode) displayStr = `${displayStr}-${occur.collcode}`;

                  // if(!checkLatLng(latlng)) continue;

                  // if(occur.catnum) {
                  //    if(!isNaN(occur.catnum)) {
                  //       displayStr = `${displayStr}-${occur.catnum}`;
                  //    } else {
                  //       displayStr = occur.catnum;
                  //    }
                  // } else if(occur.collector) {
                  //    displayStr = `${displayStr}-${occur.collector}`;
                  // } else if(occur.ocatnum) {
                  //    displayStr = `${displayStr}-${occur.ocatnum}`;
                  // }

						// Alternative display string format for OregonFlora
						let displayStr = `<div><strong>Collection: </strong>${occur.instcode}`;

						// Account for missing collection codes
						if(occur.collcode) displayStr += `-${occur.collcode}`;

						if(!checkLatLng(latlng)) continue;
						displayStr += '</div>';
						if(occur.catnum) displayStr += `<div><strong>Catalog #: </strong>${occur.catnum}</div>`;
						if(occur.ocatnum) displayStr += `<div><strong>Secondary Catalog #: </strong>${occur.ocatnum}</div>`;
						let recordedByType = occur.colltype == 'spec' ? 'Collector' : 'Observer';
						if(occur.collector) displayStr += `<div><strong>${recordedByType}: </strong>${occur.collector}</div>`;

						//Add marker based on occurence type
						// OSU herbarium specimen: diamond
						let marker = {};
						if(occur.instcode == 'OSU' && occur.colltype == 'spec') {
							marker = L.marker(latlng, {
								icon: getOregonFloraSvg({
									color: `#${colorGroup.c}`,
									size: 30,
									icon: 'osc'
								})
							});

							// Add marker to markerGroup
							map.markerGroups['osc'][tid] = (map.markerGroups['osc'][tid] || []).concat(marker);

						// OregonFlora Photo: square
						} else if (occur.instcode == 'OF' && occur.collcode == 'FP') {
							marker = L.marker(latlng, {
								icon: getOregonFloraSvg({
									color: `#${colorGroup.c}`,
									size: 30,
									icon: 'ofphoto'
								})
							});

							// Add marker to markerGroup
							map.markerGroups['ofphoto'][tid] = (map.markerGroups['ofphoto'][tid] || []).concat(marker);
					<?php if ($ENABLE_INAT_SEARCH) { /* Show option to exclude iNat observations */ ?>
						// iNaturalist Unvouchered Observation: plus
						} else if (occur.inat == 'true' && occur.colltype == 'obs') {
							marker = L.marker(latlng, {
								icon: getOregonFloraSvg({
									color: `#${colorGroup.c}`,
									size: 30,
									icon: 'inat'
								})
							});

							// Add marker to markerGroup
							map.markerGroups['inat'][tid] = (map.markerGroups['inat'][tid] || []).concat(marker);
					<?php } ?>
						// Other Herbarium Specimens: circle
						} else if (occur.colltype == 'spec') {
							marker = L.circleMarker(latlng, {
								radius : 8,
								color  : '#000000',
								weight: 2,
								fillColor: `#${colorGroup.c}`,
								opacity: 1.0,
								fillOpacity: 1.0
							})

							// Add marker to markerGroup
							map.markerGroups['spec'][tid] = (map.markerGroups['spec'][tid] || []).concat(marker);

						// Other Observations: triangle
						} else {
							marker = L.marker(latlng, {
								icon: getObservationSvg({
									color: `#${colorGroup.c}`,
									size: 30
								})
							});

							// Add marker to markerGroup
							map.markerGroups['obs'][tid] = (map.markerGroups['obs'][tid] || []).concat(marker);
						}

                  // let marker = (occur.colltype === "spec"?
                  // L.circleMarker(latlng, {
                  //    radius : 8,
                  //    color  : '#000000',
                  //    weight: 2,
                  //    fillColor: `#${colorGroup.c}`,
                  //    opacity: 1.0,
                  //    fillOpacity: 1.0
                  // }):
                  // L.marker(latlng, {
                  //    icon: getOregonFloraSvg({
                  //       color: `#${colorGroup.c}`,
                  //       size: 30
                  //    })
                  // }))

                  marker.bindTooltip(`<div style="font-size:1.2rem">${displayStr}</div>`)
                  .on('click', function() { openIndPU(occid, clid) })

                  taxaCluster.addLayer(marker)
                  bounds.addLayer(marker)
               }
            }
            map.mapLayer.addLayer(taxaCluster);

            // Oregonflora Addition: save the taxa markerClusters for later manipulation
            map.taxaClusters[tid] = taxaCluster;
         }
         map.mapLayer.fitBounds(bounds.getBounds());
      }

      function initialize() {
         try {
            let data = document.getElementById('service-container')
            occurCoords = JSON.parse(data.getAttribute('data-occur-coords'));
            colorLegend = JSON.parse(data.getAttribute('data-legend'))
            clid = JSON.parse(data.getAttribute('data-clid'))
         } catch (err) {
            alert("<?php echo $LANG['FAILED_TO_LOAD_OCCR_DATA']; ?>")
         }
         //Keeping Google and leaflet files seperate for sake of saving repeat
         //work when trying to move away from google maps.
         leafletInit();
      }

      function addRefPoint() {
			let lat = document.getElementById("lat").value;
		   let lng = document.getElementById("lng").value;
			let title = document.getElementById("title").value;

			if(!useLLDecimal){
				var latdeg = document.getElementById("latdeg").value;
				var latmin = document.getElementById("latmin").value;
				var latsec = document.getElementById("latsec").value;
				var latns = document.getElementById("latns").value;
				var longdeg = document.getElementById("longdeg").value;
				var longmin = document.getElementById("longmin").value;
				var longsec = document.getElementById("longsec").value;
				var longew = document.getElementById("longew").value;
				if(latdeg != null && longdeg != null){
					if(latmin == null) latmin = 0;
					if(latsec == null) latsec = 0;
					if(longmin == null) longmin = 0;
					if(longsec == null) longsec = 0;
					lat = latdeg*1 + latmin/60 + latsec/3600;
					lng = longdeg*1 + longmin/60 + longsec/3600;
					if(latns == "S") lat = lat * -1;
					if(longew == "W") lng = lng * -1;
				}
			}

			if(lat === null && lng === null){
				window.alert("<?php echo $LANG['ENTER_VALUES_IN_LAT_LONG']; ?>");
         } else if(lat < -180 || lat > 180 || lng < -180 || lng > 180) {
					window.alert("<?php echo $LANG['LAT_LONG_MUST_BE_BETWEEN_VALUES']; ?> (" + lat + ";" + lng + ")");
         } else {
            var addPoint = true;
            if(lng > 0) addPoint = window.confirm("<?php echo $LANG['LONGITUDE_IS_POSITIVE']; ?>?");
            if(!addPoint) lng = -1*lng;

            map.mapLayer.addLayer(
               L.marker([lat, lng])
               .bindTooltip(`<div style="font-size:1.2rem">${title}</div>`)
            )
         }
      }

		function toggleLatLongDivs(){
			var divs = document.getElementsByTagName("div");
			for (i = 0; i < divs.length; i++) {
				var obj = divs[i];
				if(obj.getAttribute("class") == "latlongdiv" || obj.getAttribute("className") == "latlongdiv"){
					if(obj.style.display=="none"){
						obj.style.display="block";
					}
					else{
						obj.style.display="none";
					}
				}
			}
			if(useLLDecimal){
				useLLDecimal = false;
			}
			else{
				useLLDecimal = true;
			}
		}

		function openIndPU(occId,clid){
			newWindow = window.open('../individual/index.php?occid='+occId+'&clid='+clid,'indspec' + occId,'scrollbars=1,toolbar=0,resizable=1,width=1100,height=800,left=20,top=20');
			if (newWindow.opener == null) newWindow.opener = self;
			setTimeout(function () { newWindow.focus(); }, 0.5);
	}

	</script>
</head>
<body class="collapsed-header" style="width:100%" onload="initialize();">
	<?php
	//if($shouldUseMinimalMapHeader) include_once($SERVER_ROOT . '/includes/minimalheader.php');
	include($SERVER_ROOT . '/includes/header.php');
	?>
   <h1 class="page-heading screen-reader-only">Leaflet Map</h1>
	<?php
	if(!$coordArr){
		?>
			<div style="font-size:120%;font-weight:bold;">
            <?php echo $LANG['QUERY_DOES_NOT_CONTAIN_RECORDS']; ?>.
			</div>
			<div style="margin-left:20px;">
				<?php echo $LANG['EITHER_REC_NOT_GEOREF']; ?><br/>
			</div>
			<div style="margin-left:100px;">
				-<?php echo $LANG['OR']; ?>-
			</div>
			<div style="margin-left:20px;">
				<?php echo $LANG['RARE_STATUS_REQUIRES']; ?>.
			</div>
		<?php
	}
?>
   <div id="service-container"
      data-occur-coords="<?= htmlspecialchars(json_encode($coordArr, 4)) ?>"
      data-clid="<?= htmlspecialchars($clid) ?>"
      data-legend="<?= htmlspecialchars(json_encode($legendArr)) ?>"
   />
	 <div>
			<button class="legend-button no-symbiota-placement" onclick="document.getElementById('panel').style.left='0';">
				<span style="font-size:1.3rem;line-height:1rem;margin-top:-0.2rem">
					&#9776;
				</span>
				<b>Open Legend and Controls</b>
			</button>
		</div>
	<div id="map_canvas" style="width:100%;height:calc(100dvh - 60px);z-index:1"></div>
	<div id="panel">
		<div class="panel-heading">
			<div style="flex:1">Legend and Controls</div>
			<a role="button" onclick="document.getElementById('panel').style.left='-20rem'">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" role="img">
					<path fill="currentColor" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
				</svg>
			</a>
		</div>
		<fieldset>
            <legend>
               <?php echo (isset($LANG['LEGEND']) ? $LANG['LEGEND']: 'Legend') ?>
            </legend>
			<div id="legend" style="margin-bottom: 15px">
				<?php
				$tailItem = '';
				foreach($legendArr as $subArr){
					echo '<div>';
					if(isset($subArr['t'])){
						echo '<svg xmlns="http://www.w3.org/2000/svg" style="height:12px;width:12px;margin-bottom:-2px;"><g><rect x="1" y="1" width="11" height="10" fill="#'.$subArr['c'].'" stroke="#000000" stroke-width="1px" /></g></svg> ';
						echo '= <i>'.$subArr['t'].'</i> ';
						if(isset($subArr['s'])) echo ' ('.implode(', ', $subArr['s']).')';
					}
					else{
						$tailItem = '<div>';
						$tailItem .= '<svg xmlns="http://www.w3.org/2000/svg" style="height:12px;width:12px;margin-bottom:-2px;"><g><rect x="1" y="1" width="11" height="10" fill="#'.$subArr['c'].'" stroke="#000000" stroke-width="1px" /></g></svg> ';
						$tailItem .= '= non-indexed taxa';
						$tailItem .= '</div>';
					}
					echo '</div>';
				}
				echo $tailItem;
				?>
			</div>
			<div>
				<div>
					<svg style="height:14px;width:14px;margin-bottom:-2px;">" xmlns="http://www.w3.org/2000/svg">
						<g>
							<path stroke="#000000" d="m0,7 7,-7l 7,7l -7,7z" stroke-width="1px" fill="white"/>
						</g>
					</svg> = OSU Herbarium
				</div>
				<div>
					<svg xmlns="http://www.w3.org/2000/svg" style="height:15px;width:15px;margin-bottom:-2px;">">
						<g>
							<circle cx="7.5" cy="7.5" r="7" fill="white" stroke="#000000" stroke-width="1px" ></circle>
						</g>
					</svg> = Other Herbaria
				</div>
				<div>
					<svg style="height:14px;width:14px;margin-bottom:-2px;">" xmlns="http://www.w3.org/2000/svg">
						<g>
							<path stroke="#000000" d="m0,0l 12,0l 0,12l -12,0l 0,-12z" stroke-width="1px" fill="white"/>
						</g>
					</svg> = OregonFlora Photo
				</div>
			<?php if ($ENABLE_INAT_SEARCH) { /* Show unvouchered iNat observations separately */ ?>
				<div>
					<svg style="height:14px;width:14px;margin-bottom:-2px;">" xmlns="http://www.w3.org/2000/svg">
						<g>
							<path stroke="#000000" d="m4,0l 4,0l 0,4l 4,0l 0,4l -4,0l 0,4l -4,0l 0,-4l -4,0l 0,-4l 4,0l 0,-4z" stroke-width="1px" fill="white"/>
						</g>
					</svg> = iNaturalist Observation
				</div>
			<?php } ?>
				<div>
					<svg style="height:14px;width:14px;margin-bottom:-2px;">" xmlns="http://www.w3.org/2000/svg">
						<g>
							<path stroke="#000000" d="m6.70496,0.23296l-6.70496,13.48356l13.88754,0.12255l-7.18258,-13.60611z" stroke-width="1px" fill="white"/>
						</g>
					</svg> =
               <?php echo (isset($LANG['OBSERVATION']) ? $LANG['OBSERVATION']: 'Observation') ?>
				</div>
			</div>
		</fieldset>
		<fieldset style="margin-top:1rem;">
			<legend>Points to Display</legend>
			<input type="checkbox" id="osc" checked onClick="toggleMarkers(this.id);"> OSU Herbarium Specimens<br/>
			<input type="checkbox" id="spec" checked onClick="toggleMarkers(this.id);"> Other Herbarium Specimens<br/>
			<input type="checkbox" id="ofphoto" checked onClick="toggleMarkers(this.id);"> OregonFlora Photos<br/>
		<?php if ($ENABLE_INAT_SEARCH) { /* Show unvouchered iNat observations separately */ ?>
			<input type="checkbox" id="inat" checked onClick="toggleMarkers(this.id);"> iNaturalist Observations<br/>
		<?php } ?>
			<input type="checkbox" id="obs" checked onClick="toggleMarkers(this.id);"> Unvouchered Observations
		</fieldset>
		<fieldset style="margin-top:1rem">
            <legend>
               <?php echo (isset($LANG['ADD_REFERENCE_POINT']) ? $LANG['ADD_REFERENCE_POINT']: 'Add Point of Reference') ?>
            </legend>
			<div>
				<div>
               <?php echo (isset($LANG['MARKER_NAME']) ? $LANG['MARKER_NAME']: 'Marker Name') ?>:
					<input name='title' id='title' size='15' type='text' />
				</div>
				<div class="latlongdiv">
					<div>
                     <div style="float:left;margin-right:5px">
                        <?php echo (isset($LANG['LATITUDE']) ? $LANG['LATITUDE']: 'Longitude') ?>
                        (<?php echo (isset($LANG['DECIMAL']) ? $LANG['DECIMAL']: 'Decimal') ?>):
                        <input name='lat' id='lat' size='10' type='text' placeholder='34.57' /> </div>
					</div>
					<div style="margin-top:5px;clear:both">
                     <div style="float:left;margin-right:5px">
                        <?php echo (isset($LANG['LONGITUDE']) ? $LANG['LONGITUDE']: 'Longitude') ?>
                        (<?php echo (isset($LANG['DECIMAL']) ? $LANG['DECIMAL']: 'Decimal') ?>):
                        <input name='lng' id='lng' size='10' type='text' placeholder='-112.38' /> </div>
					</div>
					<div style='font-size:80%;margin-top:5px;clear:both'>
                     <a href='#' onclick='toggleLatLongDivs();'> 
                        <?php echo (isset($LANG['ENTER_IN_DMS']) ? $LANG['ENTER_IN_DMS']: 'Enter in D:M:S format') ?>
                     </a>
					</div>
				</div>
				<div class='latlongdiv' style='display:none;clear:both'>
					<div>
                  <?php echo (isset($LANG['LATITUDE']) ? $LANG['LATITUDE']: 'Latitude') ?>:
						<input name='latdeg' id='latdeg' size='2' type='text' />&deg;
						<input name='latmin' id='latmin' size='4' type='text' />&prime;
						<input name='latsec' id='latsec' size='4' type='text' />&Prime;
						<select name='latns' id='latns'>
							<option value='N'><?php echo $LANG['NORTH']; ?></option>
							<option value='S'><?php echo $LANG['SOUTH']; ?></option>
						</select>
					</div>
					<div style="margin-top:5px;">
                  <?php echo (isset($LANG['LONGITUDE']) ? $LANG['LONGITUDE']: 'Longitude') ?>:
						<input name='longdeg' id='longdeg' size='2' type='text' />&deg;
						<input name='longmin' id='longmin' size='4' type='text' />&prime;
						<input name='longsec' id='longsec' size='4' type='text' />&Prime;
						<select name='longew' id='longew'>
							<option value='E'><?php echo $LANG['EAST']; ?></option>
							<option value='W' selected><?php echo $LANG['WEST']; ?></option>
						</select>
					</div>
					<div style='font-size:80%;margin-top:5px;'>
                     <a href='#' onclick='toggleLatLongDivs();'>
                        <?php echo (isset($LANG['ENTER_IN_DECIMAL']) ? $LANG['ENTER_IN_DECIMAL']: 'Enter in decimal format') ?>
                     </a>
					</div>
				</div>
				<div style="margin-top:10px;">
               <button onclick='addRefPoint();'>
                  <?php echo (isset($LANG['ADD_MARKER']) ? $LANG['ADD_MARKER']: 'Add Marker') ?>
               </button>
				</div>
			</div>
		</fieldset>
	</div>
</body>
</html>

