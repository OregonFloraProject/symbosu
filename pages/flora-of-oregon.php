<?php
//error_reporting(E_ALL);
include_once( "../config/symbini.php" );
header( "Content-Type: text/html; charset=" . $charset );
?>
<html>
<head>
    <title><?php echo $defaultTitle ?> Flora of Oregon</title>
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

<div class="info-page page2022 flora-of-oregon">
    <section id="titlebackground" class="title-publications">
        <div class="inner-content">
            <h1>Flora of Oregon</h1>
        </div>
    </section>
    <section>
        <!-- if you need a full width column, just put it outside of .inner-content -->
        <!-- .inner-content makes a column max width 1100px, centered in the viewport -->
        <div class="inner-content">
            <!-- place static page content here. -->
            <h2>Learn about the diverse plants of Oregon with our beautiful, comprehensive, and research-based reference tools.</h2>

						<div class="row">
								<div id="column-main" class="col-lg-8">
									<p>
									The Flora of Oregon is a three-volume reference that will be the state’s only flora published in the past half century and the first illustrated floristic work that exclusively addresses Oregon. 
									Volumes 1 and 2 were published in 2015 and 2020, respectively, and can be purchased directly from the publisher, the <a href="https://shopbritpress.org/collections/all" target="_blank">Botanical Research Institute of Texas Press</a>, or from other vendors. 
									Volume 3 has a projected publication date of 2023. Upon completion, we estimate detailed descriptions of 4,380 native and naturalized taxa will be published in the three volumes.
									</p>
									<div class="inset updates">
										<h3>Stay informed and keep your Flora up-to-date</h3>
										<?php /* <em>(Click an icon to download)</em> */ ?>
										<ul class="downloads">
											<li>
												<div class="title">Errata for volumes 1&amp;2</div>
												<div class="icon pdf"><a href="pdfs/FloraOfOregon_Errata.pdf" download><img src="../images/Adobe_PDF_file_icon_32x32.png"></a></div>
												<?php /* <div class="icon csv"></div> */ ?>
											</li>
											<li>
												<div class="title">Volume 1 &amp; 2 taxa not treated in the printed Flora, but present on our website</div>
												<?php /* <div class="icon pdf"><img src="../images/Adobe_PDF_file_icon_32x32.png"></div> */ ?>
												<div class="icon csv"><a href="data/AcceptedTaxa_NotTreatedInFlora.csv" download>CSV</a></div>
											</li>
											<li>
												<div class="title">Taxonomic changes since publication of the flora volumes</div>
												<?php /* <div class="icon pdf"><img src="../images/Adobe_PDF_file_icon_32x32.png"></div> */ ?>
												<div class="icon csv"><a href="data/Flora_ChangeSincePublication.csv" download>CSV</a></div>
											</li>
<li>
												<div class="title">Glossary of terms used</div>
												<?php /* <div class="icon pdf"><img src="../images/Adobe_PDF_file_icon_32x32.png"></div> */ ?>
												<div class="icon csv"><a href="data/terms-used-in-oregonflora-org-publications-pages.pdf" download><img src="../images/Adobe_PDF_file_icon_32x32.png"></a></div>
											</li>
										
										</ul>
									</div>
									<div class="volumes">
									
										<div class="description">
											<p>Each volume is a beautifully illustrated regional flora that informs plant identification. Included are chapters and appendices providing essential context in topics such as ecology, habitats, gardening with natives, and insect-plant relationships.</p>
											<img src="../pages/images/of-spread_7499-1200px.png" alt="Sample pages from Oregon Flora">
										</div>
										<div class="volume-detail">
											<h2>Flora of Oregon Volume 1:</h2>
											<h3>Pterodophytes, Gymnosperms, and Monocots</h3>
											<div class="detail">
												<img src="./images/flora_vol1.png" alt="Flora of Oregon Vol 1">
												<p>Notable families in Volume 1 include all ferns and fern allies, conifers, sedges (Cyperaceae), grasses (Poaceae), orchids (Orchidaceae), and lilies. 
												Front chapters are richly illustrated with color photographs; they describe the state’s ecology and predominant plant habitats, 50 of the best places to see wildflowers, and biographical sketches of notable Oregon botanists. 
												Appendices detail taxa restricted to a single ecoregion, endemics, and those not collected in more than 50 years.</p>
												<div class="cta"><a href="https://shopbritpress.org/collections/all-titles/products/floraoforegon1" target="_blank" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Purchase Volume 1</a></div>
											</div>
										</div>
										<div class="volume-detail">
											<h2>Flora of Oregon Volume 2:</h2>
											<h3>Dicots Aizoaceae - Fagaceae</h3>
											<div class="detail">
												<img src="./images/flora_vol2.png" alt="Flora of Oregon Vol 2">
												<p>Notable families in Volume 2 include the sunflowers (Asteraceae), mustards (Brassicaceae), heaths (Ericaceae), and legumes (Fabaceae). 
												Filled with photographs, the front chapters cover gardening with native plants and plant-insect interactions with a focus on butterflies and pollinators. 
												Appendices list butterfly-foodplant pairs, pollinator specialists and their targeted plants, native garden plants that support insects, and features of native species used for gardening and landscaping.
												<div class="cta"><a href="https://shopbritpress.org/collections/all-titles/products/floraoforegon2" target="_blank" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Purchase Volume 2</a></div>
											</div>
										</div>
										
										
									</div><!-- volumes -->
								</div><!-- column main -->
								<div id="column-right" class="col-lg-4">
									<div class="sidebox inset sponsor">
										<h3>Help sponsor Volume 3!</h3>
										<img src="../pages/images/flora_vol3.jpg" alt="Flora of Oregon Vol 3">
										<p>Become a part of this lasting legacy - help us complete the series by sponsoring a treatment or illustration for Volume 3 – Dicots: Garryaceae - Zygophyllaceae!</p>
										<p>Get started by simply downloading our Oregon Flora Volume 3 sponsorship form:</p>
										<div class="cta"><a href="pdfs/OregonFlora_Volume_3_sponsorship.pdf" download class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Help sponsor Volume 3</a></div>
										
									</div>
									<div class="sidebox praise">
										<h2>Praise for <em>Flora of Oregon</em></h2>
										<blockquote>There is now a new standard of excellence for a state flora.
										</blockquote>
										<cite>
											Neil Harriman, <em>Plant Science Bulletin</em> 61(4)
										</cite>
										 
										<blockquote>These two well-bound, attractive, and user-friendly publications contain an immense amount of valuable floristic and conservation information that is applicable far beyond their home state.</blockquote>
										<cite>
											Jenifer Penny & Daniel Brunton
										</cite>
										
										<blockquote>From start (fascinating introductory materials) to finish (information-rich appendices) and everything in between (inspired treatments), this is a winner.
										</blockquote>
										<cite>
											Jenifer Penny & Daniel Brunton
										</cite>
										
																			
										<blockquote>We can only hope Volume 3 is not far off so Flora of Oregon can assume its position as the new standard of excellence for the production of regional North American floras.
										</blockquote>										
										<cite>
											Jenifer Penny & Daniel Brunton,<br><em>Canadian Field Naturalist</em> v.135(1)
										</cite>									
										 
										<blockquote>Page layouts for species treatments strikes a good visual balance between illustrations, maps and text. I was struck by how usable this format is, compared to many older floras.</blockquote>
										<cite>
											Kathleen Sayce, <em>Douglasia</em> 45(3)
										</cite>
										 
										<blockquote>Contemporary, authoritative, comprehensive, well-illustrated, beautifully laid-out, and easy-to-use.</blockquote>
										<cite>
											David Giblin, <em>Native Plant Society of Oregon 2015 Book Review</em> 
										</cite>
										
										
										
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