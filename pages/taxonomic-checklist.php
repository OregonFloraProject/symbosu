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
    <style>
      /* Family/genus report search widget. */
      #react-taxonomic-checklist-app .fg-mode-toggle {
        display: flex;
        flex-flow: row nowrap;
        gap: 1.2rem;
        margin-bottom: 0.5rem;
      }
      #react-taxonomic-checklist-app .fg-mode-option {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0;
        font-size: 14px;
        color: #1e352f;
        cursor: pointer;
      }
      #react-taxonomic-checklist-app .fg-mode-option.is-inactive {
        opacity: 0.5;
        color: #999;
        cursor: pointer;
      }
      #react-taxonomic-checklist-app .search-widget {
        display: flex;
        flex-flow: row nowrap;
        align-items: flex-start;
        position: relative;
      }
      #react-taxonomic-checklist-app .search-widget input[type='text'] {
        flex: 1 1 auto;
        min-width: 0;
        margin-bottom: 0.5rem;
      }
      #react-taxonomic-checklist-app .search-widget .btn-search {
        flex: 0 0 auto;
        margin-left: -6px;
      }
      #react-taxonomic-checklist-app .search-widget .dropdown-menu {
        display: block;
        position: absolute;
        top: calc(100% - 1.5rem);
        left: 0;
        right: 0;
        width: calc(100% - 32px);
        max-height: 220px;
        overflow-y: auto;
        z-index: 1000;
      }
      #react-taxonomic-checklist-app .search-widget .dropdown-menu .dropdown-item {
        display: block;
        cursor: pointer;
        overflow-wrap: anywhere;
        white-space: normal;
      }
      #react-taxonomic-checklist-app .fg-confirmation {
        margin: 0.25rem 0 0;
        font-size: 14px;
        color: #3c641e;
      }
    </style>
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
