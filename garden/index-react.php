<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>

<html>
  <head>
    <title><?php echo $DEFAULT_TITLE?> | Gardening with Natives</title>
    <meta charset="utf-8">
    <link href="<?php echo $CLIENT_ROOT; ?>/css/base.css?ver=<?php echo $CSS_VERSION; ?>" type="text/css" rel="stylesheet" />
    <link href="<?php echo $CLIENT_ROOT; ?>/css/main.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />
    <link href="<?php echo $CLIENT_ROOT; ?>/css/garden.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />

    <script type="text/javascript" src="https://unpkg.com/react@16/umd/react.development.js" crossorigin></script>
    <script type="text/javascript" src="https://unpkg.com/react-dom@16/umd/react-dom.development.js" crossorigin></script>
    <script type="text/javascript" src="https://unpkg.com/@babel/standalone/babel.min.js" crossorigin></script>
  </head>
  <body>
    <?php
    include("$SERVER_ROOT/header.php");
    ?>

    <div id="page-content" style="min-height: 50em;">
      <div id="react-app" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"></div>
      <script type="text/babel" src="<?php echo $CLIENT_ROOT ?>/js/garden-react.js"></script>
    </div>

    <?php
    include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>