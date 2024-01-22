<?php
//error_reporting(E_ALL);
include_once( "../config/symbini.php" );

$rareplantCSV = __DIR__ . '/pdfs/rareplantfactsheets.csv';
$factsheets = [];
if (($handle = fopen($rareplantCSV, "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$factsheets[] = $data;
	}
	fclose($handle);
}
array_shift($factsheets);#remove header
usort($factsheets,function($a,$b) {
	return $a[2] <=> $b[2];
});
$currIndex = null;
$indexes = [];
foreach ($factsheets as $factsheet) {
	$first = substr(strtolower($factsheet[2]),0,1);#first letter of title
	if ($first != $currIndex) {
		$currIndex = $first;
		$indexes[] = $currIndex;
	}
}


header( "Content-Type: text/html; charset=" . $charset );
?>
<html>
<head>
    <title><?php echo $defaultTitle ?> Rare Plant Fact Sheets</title>
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

<div class="info-page page2022 rare-plant-factsheets">
    <section id="titlebackground" class="title-publications">
        <div class="inner-content">
            <h1>Rare Plant Fact Sheets</h1>
        </div>
    </section>
    <section>
        <!-- if you need a full width column, just put it outside of .inner-content -->
        <!-- .inner-content makes a column max width 1100px, centered in the viewport -->
        <div class="inner-content">
					<!-- place static page content here. -->
					<?php
					echo '<h2 class="temp">Coming soon to oregonflora.org: an <a href="' . $CLIENT_ROOT . '/pages/rare-plant-guide.php">interactive Rare Plant Guide</a>!</h2>';
					?>
					<h2>Here are printable factsheets to help identify 100 rare taxa in Oregon. Each has features such as ‘look-alike’ species, best survey times, and illustrations highlighting important characters.</h2>
					<div class="inset index">
						<?php /* <h3>Index</h3> */ ?>
						<p><em>Jump to a section:</em></p>
						<ul>
						<?php
							foreach ($indexes as $index) {
								echo '<li><a href="#index-' .$index  . '" class="btn btn-primary active" role="button" aria-pressed="true">' .$index  . '</a></li>';
							}
						?>
							
						</ul>
					</div>
					<p>To download a rare plant fact sheet, click on the taxon name or its PDF icon. We’ve also included a link to each taxon’s profile page. </p>

					
					<div class="row columns">
						<div id="column-main" class="col-lg-12">
							<ul class="factsheets">
							<?php
								foreach ($factsheets as $factsheet) {
									echo '<li>';
										$first = substr(strtolower($factsheet[2]),0,1);#first letter of title
										if ($first != $currIndex) {
											$currIndex = $first;
											echo '<a name="index-' . $currIndex . '" class="anchor"></a>';
										}
										echo '<div class="pdf"><a href="https://oregonflora.org' . $factsheet[1] . '" download><img src="../images/Adobe_PDF_file_icon_32x32.png"></a></div>';
										echo '<div class="taxon-name"><a href="https://oregonflora.org' . $factsheet[1] . '" download>' . $factsheet[2] . '</a></div>';
										echo '<div class="taxon-link"><a href="' . $CLIENT_ROOT . '/taxa/index.php?taxon=' . $factsheet[3] . '" class="btn active" role="button" aria-pressed="true">Profile Page</a></div>';
									echo '</li>';
								}
							
							?>
							
							</ul>
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