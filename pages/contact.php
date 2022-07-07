<?php
//error_reporting(E_ALL);
include_once( "../config/symbini.php" );
header( "Content-Type: text/html; charset=" . $charset );

function obfuscate($email) {
  //build the mailto link
  $unencrypted_link = '<a href="mailto:'.$email.'">'.$email.'</a>';
  $noscript_link = "email";
  //put them together and encrypt
  return '<script type="text/javascript">Rot13.write(\''.str_rot13($unencrypted_link).'\');</script><noscript>'.$noscript_link . '</noscript>';
}
?>
<html>
<head>
    <title><?php echo $defaultTitle ?> Contact Us</title>
    <meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/base.css?<?php echo filemtime($SERVER_ROOT . '/css/base.css'); ?>">    
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/main.css?<?php echo filemtime($SERVER_ROOT . '/css/main.css'); ?>">  
    <meta name='keywords' content=''/>
    <script type="text/javascript">
		<?php include_once( $serverRoot . '/config/googleanalytics.php' ); ?>
    </script>

</head>
<body>
<?php
  include("$SERVER_ROOT/header.php");
?>
<div class="info-page">
    <section id="titlebackground" class="title-leaf">
        <!-- if you need a full width column, just put it outside of .inner-content -->
        <!-- .inner-content makes a column max width 1100px, centered in the viewport -->
        <div class="inner-content">
            <h1>Contact Us</h1>
        </div>
    </section>
    <section>
        <div class="inner-content">
            <!-- place static page content here. -->
            <h2>Location:</h2>
            <p>Rooms 2625, 2627 Cordley Hall<br />Oregon State University<br />2701 SW Campus Way<br />Corvallis, OR 97331</p>
            <h2>Mailing Address:</h2>
            <p>OSU Dept. Botany &amp; Plant Pathology<br />Attn: OregonFlora<br />2701 SW Campus Way<br />Corvallis, OR 97331</p>
            <h2>Correspondence:</h2>
            <p>OSU Dept. Botany &amp; Plant Pathology<br/>Attn: Dr. Linda Hardison, Director, OregonFlora<br/>2701 SW Campus Way<br/>Corvallis, OR 97331<br/>541-737-4338</p>
            <h2>Send contributions of species lists, digital images, and other data to:</h2>
            <p><a href="mailto:info@oregonflora.org">info@oregonflora.org</a></p>
            <h2>Staff:</h2>
            <p>Linda Hardison, Director<br />linda.hardison@oregonstate.edu      541-737-4338</p>
            <p>Tanya Harvey, <i>Flora of Oregon</i> graphic designer<br />tanya@westerncascades.com</p>
            <p>Thea Jaster, Database manager, Botanist<br />theodora.jaster@oregonstate.edu      541-737-2445</p>
            <p>Stephen Meyers, Taxonomic Director<br />stephen.meyers@oregonstate.edu      541-737-2445</p>
            <p>Katie Mitchell, Database manager, Botanist<br />katie.mitchell@oregonstate.edu</p>
            <p>John Myers, <i>Flora of Oregon</i> principal illustrator<br />myersj8@oregonstate.edu</p>

        </div> <!-- .inner-content -->
    </section>
</div> <!-- .info-page -->
<?php
include( $serverRoot . "/footer.php" );
?>

</body>
</html>