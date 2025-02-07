<?php
// Block from Symbiota header
if($LANG_TAG == 'en' || !file_exists($SERVER_ROOT.'/content/lang/templates/header.' . $LANG_TAG . '.php'))
  include_once($SERVER_ROOT . '/content/lang/templates/header.en.php');
else include_once($SERVER_ROOT . '/content/lang/templates/header.' . $LANG_TAG . '.php');
$SHOULD_USE_HARVESTPARAMS = $SHOULD_USE_HARVESTPARAMS ?? false;
$collectionSearchPage = $SHOULD_USE_HARVESTPARAMS ? '/collections/index.php' : '/collections/search/index.php';
?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<link
  href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700"
  rel="stylesheet"
  type="text/css">
<link
  rel="stylesheet"
  href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
  integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
  crossorigin="anonymous">

<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/theme.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/theme.css'); ?>"> 
<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/header.css?<?php echo filemtime($SERVER_ROOT . '/css/compiled/header.css'); ?>"> 

 <!--   
<script
  type="text/javascript"
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous">
</script>
-->

<!-- Use Symbiota's version of jQuery, and only load it if it is not already loaded in <head> by the page, checking first. -->
<script> window.jQuery || document.write('<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-3.7.1.min.js" type="text/javascript">\x3C/script>')</script>

<script
  type="text/javascript"
  src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
  integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
  crossorigin="anonymous">
</script>
<script
  type="text/javascript"
  src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
  integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
  crossorigin="anonymous">
</script>
<script
  type="text/javascript"
  src="https://cdn.jsdelivr.net/gh/xcash/bootstrap-autocomplete@v2.2.2/dist/latest/bootstrap-autocomplete.min.js">
</script>

<!-- Render header -->
<div
  id="react-header"
  data-props='{ "defaultTitle": "<?php echo $DEFAULT_TITLE; ?>", "currentPage": "<?php echo $_SERVER['SCRIPT_NAME']; ?>", "googleMapKey": "<?php echo $GOOGLE_MAP_KEY ?? ""; ?>", "clientRoot": "<?php echo "$CLIENT_ROOT" ?>", "userName": "<?php echo ($USER_DISPLAY_NAME ? $USER_DISPLAY_NAME : '') ?>" }'>
</div>

<script
	src="<?php echo $CLIENT_ROOT?>/js/react/dist/header.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/header.js'); ?>"
	type="text/javascript">
</script>
<?php
/*
<div class="urgent-banner">
	<p>
	Oregon Legislature to consider HB3173 to support OregonFlora in hearing Wednesday Feb 5. Learn more <a href="https://www.npsoregon.org/wp/legislative-support-for-oregonflora-lets-make-it-a-reality/" target="_blank">here</a>.
	</p>
</div>
*/
?>
<!-- Global site tag (gtag.js) - Google Analytics 4-->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-98WFW6HYV2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-98WFW6HYV2');
</script>

<div id="site-content">
