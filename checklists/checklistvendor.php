<?php
include_once("../config/symbini.php");

$isEditor = false;
$clid = intval($_REQUEST['cl']);
if($IS_ADMIN || (array_key_exists("ClAdmin",$USER_RIGHTS) && in_array($clid,$USER_RIGHTS["ClAdmin"]))){
	$isEditor = true;
}
if (!$isEditor) {#send them to the login page
	header("Location: " . $CLIENT_ROOT . "/profile/index.php?refurl=" .  $_SERVER['REQUEST_URI']);
}


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
      <div id="react-explore-vendor-app"></div>
				<script 
					src="<?php echo $CLIENT_ROOT?>/js/react/dist/explore-vendor.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/explore-vendor.js'); ?>"
					type="text/javascript">
				</script>
    </div>

    <?php
      include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>
