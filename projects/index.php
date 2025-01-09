<?php
include_once("../config/symbini.php");
?>
<html>
  <head>
    <meta charset="utf-8"/>
    <title><?php echo $DEFAULT_TITLE; ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
      crossorigin=""></script>
  </head>

  <body>
    <?php
      include("$SERVER_ROOT/header.php");
    ?>
    <!-- Include page style here to override anything in header -->
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/theme.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/theme.css'); ?>">    
 		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/inventory.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/inventory.css'); ?>">    
 
    <!-- This is inner text! -->
    <div id="innertext">
      <div id="react-inventory-app"></div>
				<script 
					src="<?php echo $CLIENT_ROOT?>/js/react/dist/inventory.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/inventory.js'); ?>"
					type="text/javascript">
				</script>
    </div>

    <?php
      include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>
