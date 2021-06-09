<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>

<html>
  <head>
    <title><?php echo $DEFAULT_TITLE?> Gardening with Natives</title>
    <meta charset="utf-8">
    
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/theme.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/theme.css'); ?>">    
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/garden.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/garden.css'); ?>">    

  </head>
  <body>
    <?php
      include_once("$SERVER_ROOT/header.php");
    ?>

    <!-- Header includes jquery, so add jquery scripts after header
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/css/bootstrap-slider.min.css"
      integrity="sha256-G3IAYJYIQvZgPksNQDbjvxd/Ca1SfCDFwu2s2lt0oGo="
      crossorigin="anonymous" />
    <script
      src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/bootstrap-slider.min.js"
      integrity="sha256-oj52qvIP5c7N6lZZoh9z3OYacAIOjsROAcZBHUaJMyw="
      crossorigin="anonymous">
    </script> -->

    <!-- Canned search carsousel -->
    <link rel="stylesheet" type="text/css" charset="UTF-8" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" />
    
    <!-- Sliders -->
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/js/react/node_modules/@blueprintjs/core/lib/css/blueprint.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/js/react/node_modules/@blueprintjs/icons/lib/css/blueprint-icons.css">

    <div id="page-content" style="min-height: 50em;">
      <div id="react-garden"></div>
				<script 
					src="<?php echo $CLIENT_ROOT?>/js/react/dist/garden.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/garden.js'); ?>"
					type="text/javascript">
				</script>
    </div>

    <?php
    include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>