<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>

<html>
	<head>
		<title><?php echo $defaultTitle?> Gardening with Natives</title>
		<link href="<?php echo $CLIENT_ROOT; ?>/css/base.css?ver=<?php echo $CSS_VERSION; ?>" type="text/css" rel="stylesheet" />
		<link href="<?php echo $CLIENT_ROOT; ?>/css/main.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />
		<script type="text/javascript">

		</script>
	</head>
	<body>
		<?php
		include($SERVER_ROOT.'/header.php');
		?>

		<!-- This is inner text! -->
		<div id="innertext">

			Add static, dynamic and form content here.<br/>

		</div>
		<?php
			include($SERVER_ROOT.'/footer.php');
		?>
	</body>
</html>
