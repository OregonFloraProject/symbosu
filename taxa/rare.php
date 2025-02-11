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
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/theme.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/theme.css'); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/taxa.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/taxa.css'); ?>" />

    <!-- Enable Glossary Tooltips -->
    <script src="../js/jquery-ui.min.js" type="text/javascript"></script>
    <link href="../css/jquery-ui.min.css" type="text/css" rel="Stylesheet" />
    <link rel="stylesheet" type="text/css" href="../css/tooltip.css?<?php echo $CSS_VERSION; ?>" />
    <script type="text/javascript" src="../js/symb/glossary.tooltip.js"></script>

    <!-- DOMPurify -->
    <script type="text/javascript" src="../js/purify.min.js"></script>

    <!-- image carousel -->
    <link rel="stylesheet" type="text/css" charset="UTF-8" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" />

    <!-- This is inner text! -->
    <div id="innertext">
      <div id="react-taxa-rare-app"></div>
        <?php
          if (isset($RPG_FLAG) && $RPG_FLAG === 1) {
            $js_filemtime = filemtime($SERVER_ROOT . '/js/react/dist/taxa-rare.js');
            echo "<script
              src=\"{$CLIENT_ROOT}/js/react/dist/taxa-rare.js?{$js_filemtime}\"
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
