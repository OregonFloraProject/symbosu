<?php
//error_reporting(E_ALL);
include_once( "../config/symbini.php" );
header( "Content-Type: text/html; charset=" . $charset );
?>
<html>
<head>
    <title><?php echo $defaultTitle ?> Rare Plant Guide</title>
    <meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/base.css?<?php echo filemtime($SERVER_ROOT . '/css/base.css'); ?>">    
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/main.css?<?php echo filemtime($SERVER_ROOT . '/css/main.css'); ?>">   
    <meta name='keywords' content=''/>
    <script type="text/javascript">
        <?php include_once( $serverRoot . '/config/googleanalytics.php' ); ?>
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/a01aa82192.js" crossorigin="anonymous"></script>
</head>
<body>
<?php
      include("$SERVER_ROOT/header.php");
?>

<div class="info-page rare-plant-guide">
    <section id="titlebackground" class="title-rare">
        <div class="inner-content">
            <h1>Rare Plant Guide &mdash; coming soon</h1>
        </div>
    </section>
    <section>
        <!-- if you need a full width column, just put it outside of .inner-content -->
        <!-- .inner-content makes a column max width 1100px, centered in the viewport -->
        <div class="inner-content" id="tutorials-content">
            <!-- place static page content here. -->
            <h2>Oregon’s rare plants are precious natural resources. Knowledge about them helps us better
conserve and appreciate these botanical treasures. </h2>
            <p>OregonFlora is developing an interactive tool to explore rare species. Similar to the <a href="<?php echo $CLIENT_ROOT; ?>/garden/index.php">Grow Natives</a> tool, users will be able to filter on characters, scroll through field photos, and view a distribution map. Information for each species is summarized in a printable profile page; three examples are shown below.</p>
						<p>The Rare Plant Guide will launch with the presentation of <a href="https://inr.oregonstate.edu/orbic/rare-species/rare-species-oregon-publications" target="_blank">ORBIC</a> List 1 taxa—plant species at greatest risk and threatened with extinction or presumed to be extinct throughout their entire range. With continued support, OregonFlora will expand this resource to include List 2 species—those threatened with extirpation in Oregon. </p>
						<p>Thanks to the <a href="https://npsoregon.org/" target="_blank">Native Plant Society of Oregon</a>, <a href="https://inr.oregonstate.edu/orbic/rare-species/rare-species-oregon-publications" target="_blank">Oregon Biodiversity Information Center</a>, <a href="https://www.blm.gov/oregon-washington" target="_blank">OR/WA Bureau of Land Management</a>, <a href="https://www.fs.usda.gov/r6" target="_blank">US Forest Service Region 6</a>, and <a href="https://www.oregon.gov/oda/programs/PlantConservation/Pages/Default.aspx" target="_blank">Oregon Dept. Agriculture Plant Conservation</a> program for their partnership and sharing of data as we develop this public resource.</p>
					<h2>Example Rare Plant Profile Pages</h2>
            <p><img src="<?php echo $CLIENT_ROOT; ?>/pages/images/DELLEU.jpg"/></p>
            <hr/>
            <p><img src="<?php echo $CLIENT_ROOT; ?>/pages/images/CAMHOW.jpg"/></p>
            <hr/>
            <p><img src="<?php echo $CLIENT_ROOT; ?>/pages/images/CASRUB.jpg"/></p>
            
        </div> <!-- .inner-content -->
    </section>
</div>
<?php
include( $serverRoot . "/footer.php" );
?>

</body>
</html>