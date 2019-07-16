<?php
include_once('../config/symbini.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>

<html>
	<head>
		<title><?php echo $defaultTitle?> Gardening with Natives</title>
		<link href="<?php echo $clientRoot; ?>/css/base.css?ver=<?php echo $CSS_VERSION; ?>" type="text/css" rel="stylesheet" />
		<link href="<?php echo $clientRoot; ?>/css/main.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />
		<link href="<?php echo $clientRoot; ?>/css/garden.css<?php echo (isset($CSS_VERSION_LOCAL)?'?ver='.$CSS_VERSION_LOCAL:''); ?>" type="text/css" rel="stylesheet" />
	</head>
	<body>
		<?php
			include("$SERVER_ROOT/header.php");
		?>
		<!-- Header includes jquery, so add page script after header -->
		<script type="text/javascript" src="<?php echo "$clientRoot/js/garden.js"; ?>"></script>

		<div
			id="choose-native-dropdown"
			class="container-fluid choose-native-dropdown-expanded"
			style="background-image: url(<?php echo "$clientRoot/images/garden/DIG4082-green@2x.png" ?>);"
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
				src="<?php echo "$clientRoot/images/garden/collapse-arrow.png" ?>"
				value="Expand/Collapse Dropdown"
			>
		</div>

		<!-- This is inner text! -->
		<div id="innertext">

			<div id="search-sidebar">
				<h3>Search for plants</h3>
				<p>Start applying characteristics, and the matching plants will appear at right.</p>

				<form name="sidebar-search" class="form-inline">
					<div class="input-group w-100">
						<input type="text" placeholder="Search plants by name" class="form-control">
						<input
							id="search-plants-btn"
							type="image"
							src="<?php echo $clientRoot; ?>/images/garden/search-green.png"
							class="mt-auto mb-auto"
							alt="search plants">
					</div>
				</form>

				<div id="plant-needs">
					<h4>Plant needs</h4>
					<div id="filter-container"></div>
					<div class="input-group">
						<label for="sunlight">Sunlight</label>
						<select id="sunlight" name="sunlight" class="form-control ml-auto">
							<option value="" selected disabled hidden>Select...</option>
							<!-- TODO: Javascript to populate with a PHP api endpoint -->
						</select>
					</div>

					<div class="input-group">
						<label for="moisture">Moisture</label>
						<select id="moisture" name="moisture" class="form-control ml-auto">
							<option value="" selected disabled hidden>Select...</option>
							<!-- TODO: Javascript to populate with a PHP api endpoint -->
						</select>
					</div>
				</div>

				<div id="plant-size" class="mt-2">
					<h4>Mature Size</h4>
					<div class="input-group">
						<label for="plant-height">Height (ft)</label>
						<input type="range" min="0" max="50" step="10" id="plant-height" name="plant-height">
					</div>

					<div class="input-group">
						<label for="plant-width">Width (ft)</label>
						<input type="range" min="0" max="50" step="10" id="plant-width" name="plant-width">
					</div>
				</div>

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
