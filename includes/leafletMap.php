<!-- Importing Leaflet and Leaflet Styles--> 
<link 
   rel="stylesheet" 
   href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
   integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
   crossorigin="" 
/>
<script 
   src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
   integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
   crossorigin="">
</script>

<!-- Importing Leaflet Draw Plugin --> 
<link 
   rel="stylesheet" 
   href="<?php echo $CLIENT_ROOT?>/js/leaflet-draw/dist/leaflet.draw.css"
/>
<script 
   src="<?php echo $CLIENT_ROOT?>/js/leaflet-draw/dist/leaflet.draw.js"
   type="text/javascript">
</script>

<!-- Importing Leaflet Draw Drag --> 
<script 
   src= "<?php echo $CLIENT_ROOT?>/js/leaflet-draw-drag/dist/Leaflet.draw.drag.js" 
   type="text/javascript">
</script>

<!-- Importing Cluster Plugin --> 
<script 
   src= "<?php echo $CLIENT_ROOT?>/js/Leaflet.markercluster-1.4.1/dist/leaflet.markercluster.js"
   type="text/javascript">
</script>

<link 
   rel="stylesheet" 
   href="<?php echo $CLIENT_ROOT?>/js/Leaflet.markercluster-1.4.1/dist/MarkerCluster.css"
/>
<link 
   rel="stylesheet" 
   href="<?php echo $CLIENT_ROOT?>/js/Leaflet.markercluster-1.4.1/dist/MarkerCluster.Default.css"
/>

<!-- Leaflt Heatmap Plugin --> 
<script 
   src="<?php echo $CLIENT_ROOT?>/js/heatmap/heatmap.js"
   type="text/javascript">
</script>
<script 
   src="<?php echo $CLIENT_ROOT?>/js/heatmap/leaflet-heatmap.js"
   type="text/javascript">
</script>

<!-- Importing Leaflet Related Functions--> 
<script 
   src= "<?php echo $CLIENT_ROOT?>/js/symb/leafletMap.js"
   type="text/javascript">
</script>

<!-- Leaflet Specific Styling --> 
<link 
   rel="stylesheet" 
   href="<?php echo $CLIENT_ROOT?>/css/leafletMap.css"
/>
<!-- Use Symbiota's version of jQuery, and only load it if it is not already loaded in <head> by the page, checking first. -->
<script> window.jQuery || document.write('<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-3.7.1.min.js" type="text/javascript">\x3C/script>')</script>

<!-- OregonFlora Leaflet Related Functions -->
<script src= "<?php echo $CLIENT_ROOT?>/js/leaflet.OregonFlora/leaflet.OregonFlora.js?<?php echo filemtime($SERVER_ROOT . '/js/leaflet.OregonFlora/leaflet.OregonFlora.js'); ?>" type="text/javascript"></script>

<!-- Leaflet GoogleMutant Plugin for legal Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?loading=async&key=<?php echo $GOOGLE_MAP_KEY ?>" async defer></script>
<script src="<?php echo $CLIENT_ROOT?>/js/leaflet.OregonFlora/plugins/leaflet.GoogleMutant.js"></script>

<!-- Leaflet KML Plugin -->
<script src="<?php echo $CLIENT_ROOT?>/js/leaflet.OregonFlora/plugins/leaflet.KML.js"></script>