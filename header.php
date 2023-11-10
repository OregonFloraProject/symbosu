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
    
<script
  type="text/javascript"
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous">
</script>
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
  data-props='{ "defaultTitle": "<?php echo $DEFAULT_TITLE; ?>", "currentPage": "<?php echo $_SERVER['SCRIPT_NAME']; ?>", "googleMapKey": "<?php echo $GOOGLE_MAP_KEY; ?>", "clientRoot": "<?php echo "$CLIENT_ROOT" ?>", "userName": "<?php echo ($USER_DISPLAY_NAME ? $USER_DISPLAY_NAME : '') ?>" }'>
</div>

<script
	src="<?php echo $CLIENT_ROOT?>/js/react/dist/header.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/header.js'); ?>"
	type="text/javascript">
</script>

<div class="urgent-banner">
	<p>
	Through the support of many individuals, NPSO, and OSU Extension, we now have funding to operate at half-capacity through June 2024. 
	We'll continue to seek support to fully fund our program, so we can keep providing our resources. 
	Thank you &mdash; your <a href="https://www.paypal.com/donate/?hosted_button_id=ELVFJLHX3T9JU" target="_blank">donations</a> are greatly appreciated! 

	</p>
  <!--
  <p style="color: black; text-align: center; background-color: yellow; font-weight: bold;">
    Currently undergoing site maintainence. Expect some disruptions, things will be fixed soon. 
  </p>
  -->
</div>

<!-- Global site tag (gtag.js) - Google Analytics 4-->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-98WFW6HYV2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-98WFW6HYV2');
</script>

<div id="site-content">
