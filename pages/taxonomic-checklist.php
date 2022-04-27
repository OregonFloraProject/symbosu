<?php
//error_reporting(E_ALL);
include_once( "../config/symbini.php" );
header( "Content-Type: text/html; charset=" . $charset );
?>
<html>
<head>
    <title><?php echo $defaultTitle ?> How to get the most our of our site</title>
    <meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/base.css?<?php echo filemtime($SERVER_ROOT . '/css/base.css'); ?>">    
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/main.css?<?php echo filemtime($SERVER_ROOT . '/css/main.css'); ?>">   
    <meta name='keywords' content=''/>
    <script type="text/javascript">
        <?php include_once( $serverRoot . '/config/googleanalytics.php' ); ?>
    </script>
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
					<h2>Taxonomy is the science of organism relatedness, so as we discover more, those relationships and names consequently change and evolve. Our Checklist represents a snapshot in time of that taxonomy.</h2>
					<p>So if you’re curious about the history and rationale behind an Oregon plant’s name (and relationships), you’ve come to the right place.</p>
					
					<div class="row columns">
						<div id="column-main" class="col-lg-8">
							<h2>The birth of a flora starts with a checklist of names</h2>
							<p>The Oregon Vascular Plant Checklist is a framework of scientific names for all vascular plant taxa growing in the wild in our state for which a confirmed voucher exists, including native, naturalized, non-naturalized, and sporadic taxa. The Checklist’s taxonomy is reflected throughout the OregonFlora website, and it serves as a primary source for Flora of Oregon.</p>
							<p>The Checklist presents the plant diversity of Oregon. It is a living document that is continuously revised to reflect current research and information. Preparation of the original Checklist spanned 1994 – 20XX; it involved many contributing taxonomists (most of whom volunteered their expertise and are listed below), as well as volunteers, student workers and OregonFlora staff.</p>

							<h2>What’s the difference between the Checklist and Flora of Oregon?</h2>
							<img src="/images/pages/checklistvsflora_mockupdiagram.jpg" alt="comparing the OregonFlora checklist against the Flora of Oregon printed volumes">
							<p>In general, OregonFlora (and its printed Flora of Oregon) are a subset of the much larger Checklist. </p>
							<p>OregonFlora recognizes ~4,XXX species, subspecies, or varieties of vascular plants (taxa) that grow in the wild and without cultivation in our state. This represents ~XX% of the taxa tracked in the Checklist and throughout the oregonflora.org website. The printed Flora of Oregon volumes contain detailed descriptions of those Y,YYY taxa which are native and naturalized. </p>
							<p>The Oregon Vascular Plant Checklist presents ~XX,XXX scientific names. These include accepted native and naturalized taxa and their synonyms, as well as non-naturalized (exotic) and sporadic taxa reported as occurring in the state. Information about all the XX,XXX plant names are reflected in the Checklist and throughout our website tools.</p>
							
							<h2>What are vouchers?</h2>
							<img class="vouchers" src="../images/pages/osc163340_delphinium_andersonii.jpg" alt="Example of voucher for a specific taxon">
							<p>Oregon collections (vouchers) give physical evidence that a taxon exists in our state, like Achlys californica in Linn County. If identified by a botanist for OregonFlora, we call them “confirmed vouchers”, which show proof the taxon is growing in our state. These collections are publicly accessible so anyone can check and confirm for themselves!</p>

						</div>
						<div id="column-right" class="col-lg-4">
							<div class="sidebox inset access">
								<h3>Access the information of the Oregon Vascular Plant Checklist in three ways:</h3>
								<ul>
									<li>Interactive taxon search. Search for any scientific plant name to view its taxonomic relationships and synonymy.</li>
									<li>Published current version of the Checklist OregonFlora periodically publishes versions of the Checklist on this website. Download the current Checklist (Version X.X).</li>
									<li>Itemized changes between published versions of the Checklist. Download to view taxonomic changes, additions, and deletions.</li>
								</ul>
							</div>
							<div class="sidebox inset search">
								<h3>Search for plant families</h3>
								<p>The family reports list each Oregon taxon, evidence supporting its occurrence in Oregon, and the following information:</p>
								<ul>
									<li>The scientific name chosen to represent each taxon in the Oregon Checklist</li>
									<li>A common name </li>
									<li>Its origin (native or exotic) </li>
									<li>The herbarium which houses a primary confirmed voucher specimen</li>
									<li>The year the taxon was most recently collected, using OSU Herbarium collections as the primary source </li>
									<li>Citations for each Oregon taxon in several local floras [anchor link to list of local floras, below] </li>
									<li>Synonyms for each Oregon taxon and their citations, mostly originating from accepted names in the same local floras [anchor link to list of local floras, below] mentioned above </li>
									<li>Excluded names and their synonyms </li>
									<li>Pertinent references</li>
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

</body>
</html>