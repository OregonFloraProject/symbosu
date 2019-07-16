<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>

<html>
	<head>
		<title><?php echo $defaultTitle?> Gardening with Natives</title>
		<link href="<?php echo $CLIENT_ROOT; ?>/css/base.css?ver=<?php echo $CSS_VERSION; ?>" type="text/css" rel="stylesheet" />
		<link href="<?php echo $CLIENT_ROOT; ?>/css/main.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />
		<link href="<?php echo $CLIENT_ROOT; ?>/css/garden.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />
	</head>
	<body>
		<?php
			include("$SERVER_ROOT/header.php");
		?>
		<!-- Header includes jquery, so add page script after header -->
		<script type="text/javascript" src="<?php echo "$CLIENT_ROOT/js/garden.js"; ?>"></script>

		<div
			id="choose-native-dropdown"
			class="container-fluid choose-native-dropdown-expanded"
			style="background-image: url(<?php echo "$CLIENT_ROOT/images/garden/DIG4082-green@2x.png" ?>);"
			>

			<div id="choose-native-dropdown-text">
				<div>
					<h1 style="font-weight: bold;">Choose native plants for a smart, beautiful and truly Oregon garden</h1>
					<h3 class="will-hide-on-collapse" style="width: 75%;">
						Native plants thrive in Oregon’s unique landscapes and growing
						conditions, making them both beautiful and wise gardening choices.
						Use the tools below to find plants best suited to your tastes and your yard.
					</h3>
				</div>

				<div class="will-hide-on-collapse">
					<h2 style="font-weight: bold;">Why native plants?</h2>
					<h4>They need less water and fewer chemicals when established.</h4>
					<h4>They attract native pollinators, birds and other helpful creatures.</h4>
					<h4>They preserve our natural landscape and support a healthy and diverse ecosystem.</h4>
					<h4>They provide critical habitat connections for birds and wildlife.</h4>
				</div>
			</div>

			<input
				id="choose-native-dropdown-button"
				type="image"
				src="<?php echo "$CLIENT_ROOT/images/garden/collapse-arrow.png" ?>"
				value="Expand/Collapse Dropdown"
			>
		</div>

		<!-- This is inner text! -->
		<div id="innertext">

			<div id="search-sidebar">
				<h3>Search for plants</h3>
				<p>Start applying characteristics, and the matching plants will appear at right.</p>
				<form name="sidebar-search" class="form-inline">
					<input type="text" placeholder="Search plants by name" class="form-control">
					<button type="submit">
						<!-- <img src="" -->
					</button>
				</form>
			</div>

			<div id="canned-searches">

			</div>

			<div id="search-results">

			</div>

		</div>

		<?php
			include("$SERVER_ROOT/footer.php");
		?>
	</body>
</html>
