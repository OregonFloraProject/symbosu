<?php
include_once("../config/symbini.php");
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8"/>
    <title><?php echo $defaultTitle; ?> Taxonomic Checklist</title>
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/base.css?<?php echo filemtime($SERVER_ROOT . '/css/base.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/main.css?<?php echo filemtime($SERVER_ROOT . '/css/main.css'); ?>">
    <meta name="keywords" content=""/>
    <?php include_once($SERVER_ROOT . '/includes/googleanalytics.php'); ?>
  </head>
  <body>
    <?php include("$SERVER_ROOT/header.php"); ?>
    <div id="innertext">
      <div id="react-taxonomic-checklist-app"></div>
      <script
        src="<?php echo $CLIENT_ROOT?>/js/react/dist/taxonomic-checklist.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/taxonomic-checklist.js'); ?>"
        type="text/javascript">
      </script>
    </div>
    <?php include("$SERVER_ROOT/footer.php"); ?>
  </body>
</html>
