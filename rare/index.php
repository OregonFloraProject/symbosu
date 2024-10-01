<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>

<html>
  <head>
    <title><?php echo $DEFAULT_TITLE?> Rare Plant Guide</title>
    <meta charset="utf-8">

    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/theme.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/theme.css'); ?>" />
    <?php
      if (isset($RPG_FLAG) && $RPG_FLAG === 1) {
        $css_filemtime = filemtime($SERVER_ROOT . '/css/compiled/rare.css');
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$CLIENT_ROOT}/css/compiled/rare.css?{$css_filemtime}\" />";
      }
    ?>

  </head>
  <body>
    <?php
      include_once("$SERVER_ROOT/header.php");
    ?>

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
        <?php
          if (isset($RPG_FLAG) && $RPG_FLAG === 1) {
            $js_filemtime = filemtime($SERVER_ROOT . '/js/react/dist/rare.js');
            echo "<script
              src=\"{$CLIENT_ROOT}/js/react/dist/rare.js?{$js_filemtime}\"
              type=\"text/javascript\"
              ></script>";
          }
        ?>
    </div>

    <?php
      include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>
