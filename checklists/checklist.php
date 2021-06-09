<?php
include_once("../config/symbini.php");
?>
<html>
  <head>
    <meta charset="utf-8"/>
    <title><?php echo $DEFAULT_TITLE; ?></title>
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
      <div id="react-explore-app"></div>
				<script 
					src="<?php echo $CLIENT_ROOT?>/js/react/dist/explore.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/explore.js'); ?>"
					type="text/javascript">
				</script>
    </div>

    <?php
      include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>
