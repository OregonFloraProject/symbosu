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
		<!-- Header includes jquery, so add jquery scripts after header -->
		<link
			rel="stylesheet"
			href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/css/bootstrap-slider.min.css"
			integrity="sha256-G3IAYJYIQvZgPksNQDbjvxd/Ca1SfCDFwu2s2lt0oGo="
			crossorigin="anonymous" />
		<script
			src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/bootstrap-slider.min.js"
			integrity="sha256-oj52qvIP5c7N6lZZoh9z3OYacAIOjsROAcZBHUaJMyw="
			crossorigin="anonymous">
		</script>
		<script type="text/javascript" src="<?php echo "$clientRoot/js/garden.js"; ?>"></script>

		<div
			id="choose-native-dropdown"
			class="container-fluid choose-native-dropdown-expanded"
			style="background-image: url(<?php echo "$clientRoot/images/garden/DIG4082-green@2x.png" ?>);"
			>

			<div id="choose-native-dropdown-text">
				<div>
					<h1 id="page-title" style="font-weight: bold;">Choose native plants for a smart, beautiful and truly Oregon garden</h1>
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

				<div class="input-group w-100 mb-4 p-2">
					<input
						name="plant-name"
						type="text"
						placeholder="Search plants by name"
						class="form-control search-param">
					<input
						id="search-plants-btn"
						type="image"
						src="<?php echo $clientRoot; ?>/images/garden/search-green.png"
						class="mt-auto mb-auto"
						alt="search plants">
				</div>

				<div id="plant-needs">
					<h4>Plant needs</h4>
					<div id="filter-container"></div>
					<div class="input-group">
						<label for="sunlight">Sunlight</label>
						<select id="sunlight" name="sunlight" class="form-control ml-auto search-param">
							<option value="" selected disabled hidden>Select...</option>
							<option value="sun">Sun</option>
							<option value="part-shade">Part-Shade</option>
							<option value="full-shade">Full-Shade</option>
						</select>
					</div>

					<div class="input-group">
						<label for="moisture">Moisture</label>
						<select id="moisture" name="moisture" class="form-control ml-auto search-param">
							<option value="" selected disabled hidden>Select...</option>
							<option value="dry">Dry</option>
							<option value="moist">Moist</option>
							<option value="wet">Wet</option>
						</select>
					</div>
				</div>

				<div id="plant-size" class="mt-4">
					<h4 class="d-inline mr-2">Mature Size</h4><span>(Just grab the slider dots)</span><br>
					<div class="mt-2" id="plant-size-sliders">
						<div id="plant-height-container">
							<label for="plant-height">Height (ft)</label>
							<input
								type="text"
								class="bootstrap-slider search-param"
								id="plant-height"
								name="plant-height"
								data-provide="slider"
								data-slider-value="[0, 50]"
								data-slider-ticks="[0, 10, 20, 30, 40, 50]"
								data-slider-ticks-labels='["0", "", "", "", "", "50+"]'
								data-slider-ticks-snap-bounds="1"
								value="">
							<label id="plant-height-display" for="plant-height">(Any size)</label>
						</div>
						<div id="plant-width-container">
							<label for="plant-width">Width (ft)</label>
							<input
								type="text"
								class="bootstrap-slider search-param"
								id="plant-width"
								name="plant-width"
								data-provide="slider"
								data-slider-value="[0, 50]"
								data-slider-ticks="[0, 10, 20, 30, 40, 50]"
								data-slider-ticks-labels='["0", "", "", "", "", "50+"]'
								data-slider-ticks-snap-bounds="1"
								value="">
							<label id="plant-width-display" for="plant-width">(Any size)</label>
						</div>
					</div>
				</div>

				<div id="plant-features">

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
