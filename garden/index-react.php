<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>

<html>
  <head>
    <title><?php echo $DEFAULT_TITLE?> | Gardening with Natives</title>
    <meta charset="utf-8">

    <!-- Core symbiota -->
    <link href="<?php echo $CLIENT_ROOT; ?>/css/base.css?ver=<?php echo $CSS_VERSION; ?>" type="text/css" rel="stylesheet" />
    <link href="<?php echo $CLIENT_ROOT; ?>/css/main.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />
    <link href="<?php echo $CLIENT_ROOT; ?>/css/garden-react.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />

    <script type="text/javascript" src="https://unpkg.com/react@16/umd/react.development.js" crossorigin></script>
    <script type="text/javascript" src="https://unpkg.com/react-dom@16/umd/react-dom.development.js" crossorigin></script>
    <script type="text/javascript" src="https://unpkg.com/@babel/standalone/babel.min.js" crossorigin></script>
  </head>
  <body>
    <?php
    include("$SERVER_ROOT/header.php");
    ?>

    <div id="page-content" style="min-height: 50em;">
      <div id="react-app"></div>
      <script type="text/babel" src="<?php echo $CLIENT_ROOT ?>/js/garden/infographic-dropdown.jsx"></script>
      <script type="text/babel" src="<?php echo $CLIENT_ROOT ?>/js/garden/sidebar.jsx"></script>
      <script type="text/babel" src="<?php echo $CLIENT_ROOT ?>/js/garden/garden-react.jsx"></script>
    </div>

    <!-- Header includes jquery, so add jquery scripts after header -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/css/bootstrap-slider.min.css"
      integrity="sha256-G3IAYJYIQvZgPksNQDbjvxd/Ca1SfCDFwu2s2lt0oGo="
      crossorigin="anonymous" />
    <script
      src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/bootstrap-slider.min.js"
      integrity="sha256-oj52qvIP5c7N6lZZoh9z3OYacAIOjsROAcZBHUaJMyw="
      crossorigin="anonymous">
    </script>

    <?php
    include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>