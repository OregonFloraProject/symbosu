<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>

<html>
  <head>
    <title><?php echo $DEFAULT_TITLE?> Rare Plants</title>
    <meta charset="utf-8">

    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/theme.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/theme.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/rare.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/rare.css'); ?>" />

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

    <!-- Sliders -->
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/js/react/node_modules/@blueprintjs/core/lib/css/blueprint.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/js/react/node_modules/@blueprintjs/icons/lib/css/blueprint-icons.css">

    <!-- Enable Glossary Tooltips -->
    <script src="../js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <link href="../js/jquery-ui/jquery-ui.min.css" type="text/css" rel="Stylesheet" />
    <link rel="stylesheet" type="text/css" href="../css/tooltip.css?<?php echo $CSS_VERSION; ?>" />
    <script type="text/javascript" src="../js/symb/glossary.tooltip.js"></script>

    <!-- DOMPurify -->
    <script type="text/javascript" src="../js/purify.min.js"></script>

    <div id="page-content" style="min-height: 50em;">
      <div id="react-rare"></div>
        <script
          src="<?php echo $CLIENT_ROOT?>/js/react/dist/rare.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/rare.js'); ?>"
          type="text/javascript">
        </script>
    </div>

    <?php
    include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>
