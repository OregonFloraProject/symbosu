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
					<h2>Taxonomy is the science of organism relatedness. New discoveries change our understanding of those relationships and consequently how we name plants. Our Checklist captures this knowledge and is periodically updated to reflect the latest botanical research.</h2>

					<div class="row columns">
						<div id="column-main" class="col-lg-8">
							<div class="inset access">
								<h3>Access the information of the Oregon Vascular Plant Checklist in three ways:</h3>
								<ul>
									<li><a href="<?php echo $CLIENT_ROOT?>/pages/data/Checklist_ForWeb_April2022.csv" download>Download</a> the current checklist (Version 2.0)</li>
									<li><a href="<?php echo $CLIENT_ROOT?>/pages/data/CLrevisions_v2-0.csv" download>Download</a> to view taxonomic changes, additions, and deletions between published versions of the Checklist. </li>
									<li>Access the <a href="<?php echo $CLIENT_ROOT?>/taxa/taxonomy/taxonomydisplay.php">Interactive Taxon Search</a>.</li>
								</ul>
							</div>
							<h2>The birth of a flora starts with a checklist of names</h2>
							<p>The Oregon Vascular Plant Checklist provides a framework of scientific names for all vascular plant taxa growing in the wild in our state for which a confirmed voucher exists, including native, naturalized, non-naturalized, and sporadic taxa. 
							The Checklist’s taxonomy is reflected throughout the OregonFlora website, and it serves as a primary source for the Flora of Oregon.
							</p>
							<p>
							The Checklist presents the plant diversity of Oregon. 
							It is a living document that is continuously revised to reflect current research and information. 
							Preparation of the original Checklist began in 1994; it involved many contributing taxonomists (most of whom volunteered their expertise and are listed here), as well as volunteers, student workers, and OregonFlora staff.
							</p>
							<h2>What’s the difference between the Checklist and Flora of Oregon?</h2>
							<figure>
							<img src="../pages/images/flora_vs_checklist_image.jpg" alt="comparing the OregonFlora checklist against the Flora of Oregon printed volumes">
							<figcaption>
								<div>All Oregon taxa growing in the wild:  native, naturalized, not naturalized (exotic), sporadic </div>
								<div>All Oregon taxa from the Checklist that are native or naturalized</div>
							</figcaption>
							</figure>
							<p>The Flora of Oregon recognizes 4,710 taxa (distinct vascular plants) that grow in our state in the wild and without cultivation. 
							We estimate 4,380 taxa will have detailed descriptions once Flora of Oregon is completed.
							</p>
							<p>
							The Oregon Vascular Plant Checklist presents 10,487 scientific names that are reflected throughout our website tools. 
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
								<div class="search-wrapper">
									<input type="text" placeholder="Family Reports Coming Soon!" disabled><button class="btn-search" disabled></button>
								</div>
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