<?php
include_once("config/symbini.php");#do I need this?
?>
<!doctype html>
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

    
    <!-- carousel -->
    <link rel="stylesheet" type="text/css" charset="UTF-8" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" />
    
    <!-- This is inner text! -->
    <div id="innertext">
      <div id="react-home-app"></div>
				<script 
					src="<?php echo $CLIENT_ROOT?>/js/react/dist/home.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/home.js'); ?>"
					type="text/javascript">
				</script>
    </div>

    <?php
      include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>
