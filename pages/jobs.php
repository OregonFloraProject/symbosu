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
    <title><?php echo $defaultTitle ?> Hiring</title>
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
            <h1>Hiring</h1>
        </div>
    </section>
    <section>
        <div class="inner-content">
            <h2>Full stack web developer opening now for OregonFlora, a program sharing plant information!</h2>
            <img
              class="img-fluid my-3"
              src="images/jobs-banner.jpg"
              alt="Banner with photos of four different plant taxa"
            />
            <p>
                OregonFlora is seeking a full stack web developer to support the <a href="https://oregonflora.org">oregonflora.org</a> website and its continued development.
            </p>
            <p>
                PHP, JavaScript, React, MySQL (or Doctrine ORM), CSS, and HTML are used in this work. Tasks include both general updates and bug fixes as well as development of new tools &amp;/or customization of existing platform functionality across the full stack.
            </p>
            <p>
                You are able to work independently and interface with OregonFlora database managers and software platform resources.
            </p>
            <p>
                We’re looking for a person to join our team long-term. Commitment would be 12-20 hrs/week ongoing, with a flexible schedule. We are housed in the Dept. of Botany &amp; Plant Pathology on the OSU campus; work can be accomplished on site or remotely.
            </p>
            <p>
                About <a href="https://oregonflora.org">OregonFlora</a>:  We are a small program, passionate about our mission to provide plant diversity information to a broad audience. Our resources inform land managers in non-profits, businesses, and government; native plant gardeners, conservation workers, researchers, and plant lovers. Our website is a portal of <a href="https://symbiota.org/">Symbiota</a>, an open source software project focused on managing and sharing biodiversity data. Here is our <a href="https://github.com/OregonFloraProject/symbosu">GitHub</a> link.
            </p>
            <p>
                For further information, or to submit a cover letter and resum&eacute;, please reach out:
            </p>
            <p>
            	<b>Linda Hardison, Director</b><br />
							<a href="mailto:linda.hardison@oregonstate.edu">linda.hardison@oregonstate.edu</a><br />541-737-4338 
						</p>
        </div> <!-- .inner-content -->
    </section>
</div> <!-- .info-page -->
<?php
include( $serverRoot . "/footer.php" );
?>

</body>
</html>
