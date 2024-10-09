<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
if(!$RPG_FLAG && !$SYMB_UID) header('Location: ../profile/index.php?refurl=../rare/policy.php?'.htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES));
?>

<!doctype html>
<html>
  <head>
    <title><?php echo $DEFAULT_TITLE?> Rare Plant Guide - Use Policy</title>
    <meta charset="utf-8">

    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/theme.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/theme.css'); ?>">
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

    <div id="page-content" style="min-height: 50em;">
      <div id="react-rare-policy"></div>
        <?php
          if (isset($RPG_FLAG) && $RPG_FLAG === 1) {
            $js_filemtime = filemtime($SERVER_ROOT . '/js/react/dist/rare-policy.js');
            echo "<script
              src=\"{$CLIENT_ROOT}/js/react/dist/rare-policy.js?{$js_filemtime}\"
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
