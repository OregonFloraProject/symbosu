<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=" . $CHARSET);

$baseCss = "$CLIENT_ROOT/css/base.css?ver=$CSS_VERSION";

$cssLocalArg = isset($CSS_VERSION_LOCAL) ? "?ver=$CSS_VERSION_LOCAL" : '';
$mainCss = "$CLIENT_ROOT/css/main.css" . $cssLocalArg;
$gardenCss = "$CLIENT_ROOT/css/garden.css" . $cssLocalArg;
$gardenReactCss = "$CLIENT_ROOT/css/garden-react.css" . $cssLocalArg;
?>

<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $DEFAULT_TITLE ?>Gardening with Natives</title>
    <meta charset="utf-8">

    <link href="<?php echo $baseCss; ?>" type="text/css" rel="stylesheet" />
    <link href="<?php echo $mainCss; ?>" type="text/css" rel="stylesheet" />
    <link href="<?php echo $gardenCss; ?>" type="text/css" rel="stylesheet" />
    <link href="<?php echo $gardenReactCss; ?>" type="text/css" rel="stylesheet" />

    <!-- Page style -->
    <style>
      #page-content {
        min-height: 40em;
      }

    </style>
  </head>

  <body>
    <?php
    include("$SERVER_ROOT/header.php");
    include_once("include/searchResults.php");
    ?>

    <div id="page-content">
      <div id="results"></div>
    </div>

    <!-- React -->
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    <script src="https://unpkg.com/react@16/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@16/umd/react-dom.development.js" crossorigin></script>
    <script src="<?php echo $CLIENT_ROOT; ?>/js/garden-react.jsx" type="text/babel"></script>

    <?php
    include("$SERVER_ROOT/footer.php");
    ?>

  </body>

</html>
