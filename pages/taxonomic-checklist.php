<?php
//error_reporting(E_ALL);
include_once( "../config/symbini.php" );
header( "Content-Type: text/html; charset=" . $charset );
?>
<html>
<head>
    <title><?php echo $defaultTitle ?> Taxonomic Checklist</title>
    <meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/base.css?<?php echo filemtime($SERVER_ROOT . '/css/base.css'); ?>">    
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/main.css?<?php echo filemtime($SERVER_ROOT . '/css/main.css'); ?>">   
		<style>
			/* Prototype-only styling for the family/genus report search widget. */
			.taxonomic-checklist .sidebox.search .fg-mode-toggle {
				display: flex;
				flex-flow: row nowrap;
				gap: 1.2rem;
				margin-bottom: 0.5rem;
			}
			.taxonomic-checklist .sidebox.search .fg-mode-option {
				display: inline-flex;
				align-items: center;
				gap: 0.35rem;
				margin-bottom: 0;
				font-size: 14px;
				color: #1e352f;
				cursor: pointer;
			}
			.taxonomic-checklist .sidebox.search .fg-mode-option.is-inactive {
				opacity: 0.5;
				color: #999;
				cursor: not-allowed;
			}
			.taxonomic-checklist .sidebox.search .fg-mode-option.is-inactive input {
				pointer-events: none;
			}
			.taxonomic-checklist .sidebox.search .search-widget {
				display: flex;
				flex-flow: row nowrap;
				align-items: flex-start;
			}
			.taxonomic-checklist .sidebox.search .search-widget input[type='text'] {
				flex: 1 1 auto;
				min-width: 0;
				margin-bottom: 0.5rem;
			}
			.taxonomic-checklist .sidebox.search .search-widget .btn-search {
				flex: 0 0 auto;
				margin-left: -6px;
			}
			.taxonomic-checklist .sidebox.search .search-widget .dropdown-menu {
				position: absolute;
				top: 100%;
				left: 0;
				right: 0;
				width: 100%;
				max-height: 220px;
				overflow-y: auto;
				z-index: 1000;
			}
			.taxonomic-checklist .sidebox.search .search-widget .dropdown-menu .dropdown-item {
				display: block;
				cursor: pointer;
			}
			.taxonomic-checklist .sidebox.search .fg-confirmation {
				margin: 0.25rem 0 0;
				font-size: 14px;
				color: #3c641e;
			}
		</style>
    <meta name='keywords' content=''/>
    <?php include_once($SERVER_ROOT.'/includes/googleanalytics.php'); ?>
    <?php /* <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 
    <script src="https://kit.fontawesome.com/a01aa82192.js" crossorigin="anonymous"></script>*/ ?>
</head>
<body>
<?php
      include("$SERVER_ROOT/header.php");
?>

<div class="info-page page2022 taxonomic-checklist">
    <section id="titlebackground" class="title-publications">
        <div class="inner-content">
            <h1>Taxonomic Checklist</h1>
        </div>
    </section>
    <section>
        <!-- if you need a full width column, just put it outside of .inner-content -->
        <!-- .inner-content makes a column max width 1100px, centered in the viewport -->
        <div class="inner-content">
					<!-- place static page content here. -->
					<h2>Taxonomy is the science of organism relatedness. 
					New discoveries change our understanding of those relationships and consequently how we name plants. 
					Our Checklist captures this knowledge and is periodically updated to reflect the latest botanical research.</h2>

					<div class="row columns">
						<div id="column-main" class="col-lg-8">
							<div class="inset access">
								<h3>Access the information of the Oregon Vascular Plant Checklist in these ways:</h3>
								<ul>
									<li><a href="<?php echo $CLIENT_ROOT?>/pages/data/Checklist_ForWeb.csv" download>Download</a> the current checklist (Version 2.4)</li>
									

									<li><a href="<?php echo $CLIENT_ROOT?>/pages/data/Natives_OregonFlora.csv" download>Download</a> the checklist including only plants native to Oregon.</li>
									<li><a href="<?php echo $CLIENT_ROOT?>/pages/data/CLrevisions.csv" download>Download</a> to view taxonomic changes, additions, and deletions between published versions of the Checklist. </li>
									<li>Access the <a href="<?php echo $CLIENT_ROOT?>/taxa/taxonomy/taxonomydisplay.php">Interactive Taxon Search</a>.</li>
									<li><a href="<?php echo $CLIENT_ROOT?>/pages/data/terms-used-in-oregonflora-org-publications-pages.pdf" download>Download</a> to view glossary of terms used. </li>
								</ul>
							</div>
							<h2>The birth of a flora starts with a checklist of names</h2>
							<p>The Oregon Vascular Plant Checklist provides a framework of scientific names for all vascular plant taxa growing in the wild in our state for which a confirmed voucher exists, including native, naturalized, non-naturalized, and sporadic taxa. 
							The Checklist’s taxonomy is reflected throughout the OregonFlora website, and it serves as a primary source for the <em>Flora of Oregon</em>.
							</p>
							<p>
							The Checklist presents the plant diversity of Oregon. 
							It is a living document that is continuously revised to reflect current research and information. 
							Preparation of the original Checklist began in 1994; it involved many contributing taxonomists 
							(most of whom volunteered their expertise<?php /*and are listed here*/ ?>), as well as volunteers, student workers, and OregonFlora staff.
							</p>
							<h2>What’s the difference between the Checklist and <em>Flora of Oregon</em>?</h2>
							<figure>
							<img src="../pages/images/flora_vs_checklist_image.jpg" alt="comparing the OregonFlora checklist against the Flora of Oregon printed volumes">
							<figcaption>
								<div>All Oregon taxa growing in the wild:  native, naturalized, not naturalized (exotic), sporadic </div>
								<div>All Oregon taxa from the Checklist that are native or naturalized</div>
							</figcaption>
							</figure>
							<p>The <em>Flora of Oregon</em> recognizes 4,790 taxa (distinct vascular plants) that grow in our state in the wild and without cultivation. 
							We estimate 4,380 taxa will have detailed descriptions once <em>Flora of Oregon</em> is completed.
							</p>
							<p>
							The Oregon Vascular Plant Checklist presents 10,736 scientific names that are reflected throughout our website tools. 
							These include accepted native and naturalized taxa and their synonyms, as well as non-naturalized (exotic), and sporadic taxa having a confirmed voucher that documents its occurrence in our state.
							</p>
							<h2>What are confirmed vouchers?</h2>
							<img class="vouchers" src="../pages/images/osc163340_delphinium_andersonii.jpg" alt="Example of voucher for a specific taxon">
							<p>Herbarium specimens (vouchers) of Oregon collections give physical evidence that a taxon exists in our state. 
							If identified or reviewed by a botanist for OregonFlora, we call them “confirmed vouchers”. 
							These give tangible proof that the taxon has been found in Oregon. 
							The OSU Herbarium is our primary repository for Oregon confirmed vouchers. 
							Like most herbaria, the collections are accessible to the public through arranged visits or online.
							</p>
						</div>
						<div id="column-right" class="col-lg-4">

							<div class="sidebox inset search">
								<h3>Search for plant families</h3>
								<div class="search-wrapper" id="fg-search-wrapper">
									<div class="fg-mode-toggle">
										<label class="fg-mode-option" for="fg-mode-family">
											<input type="checkbox" id="fg-mode-family" checked> Family
										</label>
										<label class="fg-mode-option is-inactive" for="fg-mode-genus">
											<input type="checkbox" id="fg-mode-genus" disabled> Genus
										</label>
									</div>
									<div class="search-widget">
										<input type="text" class="form-control" id="fg-search-input" placeholder="Search families" autocomplete="off">
										<button class="btn-search" id="fg-search-btn" type="button" aria-label="Search"></button>
										<div class="dropdown-menu" id="fg-suggestions" style="display: none;"></div>
									</div>
									<p class="fg-confirmation" id="fg-confirmation"></p>
								</div>
								<p>The family reports list each Oregon taxon, evidence supporting its occurrence in Oregon, and the following information:</p>
								<ul>
									<li>The scientific name chosen to represent each taxon in the Oregon Checklist</li>
									<li>A common name </li>
									<li>Its origin (native or exotic) </li>
									<li>The herbarium which houses a primary confirmed voucher specimen</li>
									<li>The year the taxon was most recently collected, using OSU Herbarium collections as the primary source </li>
									<li>Citations for each Oregon taxon in several local floras </li>
									<li>Synonyms for each Oregon taxon and their citations, mostly originating from accepted names in the same local floras mentioned above </li>
									<li>Excluded names and their synonyms </li>
									<li>Pertinent <a href="<?php echo $CLIENT_ROOT?>/pages/data/primary-regional-floras.pdf" download>references</a></li>
								</ul>
							</div>
							

							
						</div>
					</div>  
        </div> <!-- .inner-content -->
    </section>
</div>
<?php
include( $serverRoot . "/footer.php" );
?>

<script>
(function () {
	'use strict';

	var DATA_URL = '<?php echo $CLIENT_ROOT?>/pages/data/family-genera-prototype.json';

	document.addEventListener('DOMContentLoaded', function () {
		var wrapper = document.getElementById('fg-search-wrapper');
		if (!wrapper) return;

		var familyBox = document.getElementById('fg-mode-family');
		var genusBox = document.getElementById('fg-mode-genus');
		var familyLabel = familyBox.closest('.fg-mode-option');
		var genusLabel = genusBox.closest('.fg-mode-option');
		var input = document.getElementById('fg-search-input');
		var button = document.getElementById('fg-search-btn');
		var suggestions = document.getElementById('fg-suggestions');
		var confirmation = document.getElementById('fg-confirmation');

		var data = [];
		var mode = 'family';

		function setMode(nextMode) {
			mode = nextMode;
			var isFamily = mode === 'family';
			familyBox.checked = isFamily;
			genusBox.checked = !isFamily;
			familyBox.disabled = !isFamily;
			genusBox.disabled = isFamily;
			familyLabel.classList.toggle('is-inactive', !isFamily);
			genusLabel.classList.toggle('is-inactive', isFamily);
			input.placeholder = isFamily ? 'Search families' : 'Search genera';
			hideSuggestions();
		}

		function getMatches(query) {
			var needle = query.toLowerCase();
			var matches = [];
			data.forEach(function (entry) {
				if (mode === 'family') {
					if (entry.family.toLowerCase().indexOf(needle) !== -1) {
						matches.push({ label: entry.family, name: entry.family });
					}
				} else {
					entry.genera.forEach(function (genus) {
						if (genus.toLowerCase().indexOf(needle) !== -1) {
							matches.push({ label: genus + ' - ' + entry.family, name: genus });
						}
					});
				}
			});
			return matches;
		}

		function renderSuggestions(query) {
			suggestions.textContent = '';
			if (query.length < 1) {
				hideSuggestions();
				return;
			}
			var matches = getMatches(query);
			if (!matches.length) {
				hideSuggestions();
				return;
			}
			matches.forEach(function (match) {
				var link = document.createElement('a');
				link.className = 'dropdown-item';
				link.setAttribute('href', 'javascript:void(0)');
				link.setAttribute('data-name', match.name);
				link.textContent = match.label;
				suggestions.appendChild(link);
			});
			suggestions.style.display = 'block';
		}

		function hideSuggestions() {
			suggestions.style.display = 'none';
		}

		function selectName(name) {
			input.value = name;
			hideSuggestions();
			confirmation.textContent = 'Selected: ' + name + ' (prototype)';
		}

		function requestMode(targetMode, event) {
			event.preventDefault();
			if (mode !== targetMode) setMode(targetMode);
		}

		familyLabel.addEventListener('mousedown', function (event) {
			requestMode('family', event);
		});
		genusLabel.addEventListener('mousedown', function (event) {
			requestMode('genus', event);
		});

		familyBox.addEventListener('change', function () {
			if (familyBox.checked !== (mode === 'family')) setMode(mode);
		});
		genusBox.addEventListener('change', function () {
			if (genusBox.checked !== (mode === 'genus')) setMode(mode);
		});

		input.addEventListener('input', function () {
			confirmation.textContent = '';
			renderSuggestions(input.value.trim());
		});
		input.addEventListener('focus', function () {
			renderSuggestions(input.value.trim());
		});
		input.addEventListener('blur', function () {
			setTimeout(hideSuggestions, 150);
		});

		button.addEventListener('click', function () {
			renderSuggestions(input.value.trim());
			input.focus();
		});

		suggestions.addEventListener('mousedown', function (event) {
			event.preventDefault();
		});
		suggestions.addEventListener('click', function (event) {
			var item = event.target.closest('.dropdown-item');
			if (!item) return;
			selectName(item.getAttribute('data-name'));
		});

		fetch(DATA_URL)
			.then(function (response) { return response.json(); })
			.then(function (json) { data = json; })
			.catch(function () { confirmation.textContent = 'Prototype data unavailable.'; });

		setMode('family');
	});
})();
</script>

</body>
</html>