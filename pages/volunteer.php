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
    <title><?php echo $defaultTitle ?> Volunteer</title>
    <meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/base.css?<?php echo filemtime($SERVER_ROOT . '/css/base.css'); ?>">    
		<link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/main.css?<?php echo filemtime($SERVER_ROOT . '/css/main.css'); ?>">
    <meta name='keywords' content=''/>
    <script type="text/javascript">
		<?php include_once( $serverRoot . '/config/googleanalytics.php' ); ?>
    </script>

    <script>
        Rot13 = {
            map: null,

            convert: function(a) {
                Rot13.init();

                var s = "";
                for (i=0; i < a.length; i++) {
                    var b = a.charAt(i);
                    s += ((b>='A' && b<='Z') || (b>='a' && b<='z') ? Rot13.map[b] : b);
                }
                return s;
            },

            init: function() {
                if (Rot13.map != null)
                    return;

                var map = new Array();
                var s   = "abcdefghijklmnopqrstuvwxyz";

                for (i=0; i<s.length; i++)
                    map[s.charAt(i)] = s.charAt((i+13)%26);
                for (i=0; i<s.length; i++)
                    map[s.charAt(i).toUpperCase()] = s.charAt((i+13)%26).toUpperCase();

                Rot13.map = map;
            },

            write: function(a) {
                document.write(Rot13.convert(a));
            }
        }
    </script>
</head>
<body>
<?php
      include("$SERVER_ROOT/header.php");
?>
<div class="info-page">
    <section id="titlebackground" class="title-redberry">
        <div class="inner-content">
            <h1>Volunteer</h1>
        </div>
    </section>
    <section>
        <!-- if you need a full width column, just put it outside of .inner-content -->
        <!-- .inner-content makes a column max width 1100px, centered in the viewport -->
        <div class="inner-content">
            <!-- place static page content here. -->
            <div class="row two-col-row">
                <div class="column-right col-md-4 order-1 order-md-2 pt-5">
                    <figure class="figure">
                        <img src="images/volunteer3.png" class="figure-img img-fluid z-depth-1" alt="Volunteer"">
                        <figcaption class="figure-caption">Volunteering with OregonFlora is fun!</figcaption>
                    </figure>
                </div>
                <div class="column-main col-md-8 order-2 order-md-1 pr-md-5">
                    <h2>Be a part of the OregonFlora team!</h2>
                    <p>Nature lovers, computer geeks, artists, field workers, history buffs… People with widely different backgrounds and interests find satisfaction in helping OregonFlora. Join the nearly 1,000 people of all ages and skills that have volunteered since our program began! There are a variety of ways to participate: data entry, technical editing and writing, program assistance, and event planning can be done at our location or remotely. Field work opportunities of weed control, data gathering, and planting are periodically scheduled; check our <a href="<?php echo $CLIENT_ROOT; ?>news-events.php">News and Events</a> page for details.</p>
                    <p>If you would like to contribute species lists, photographs, or other information to OregonFlora,  or if you would like to volunteer, contact us at: <?php echo obfuscate("info@oregonflora.org") ?>.</p>
                </div>
            </div>
			<div class="row two-col-row">
                <div class="column-right col-md-4 order-1 order-md-2 pt-5">
                    <figure class="figure">
                        <img src="images/volunteer5.jpg" class="figure-img img-fluid z-depth-1" alt="Volunteer"">
                        <figcaption class="figure-caption">Photographing plants in the field</figcaption>
                    </figure>
                </div>
                <div class="column-main col-md-8 order-2 order-md-1 pr-md-5">
                    <h2>Opportunity for botanist-photographers</h2>
                    <p>Are you confident of your plant identification skills as well as your photographic skills? We invite you to submit images of plant taxa having fewer than five field photos for inclusion on our taxon profile pages. A list of those taxa can be found <a href="data/OregonFlora_Taxa_Needing_FieldPhotos.csv">here</a>. We will consider digital image submissions of identified, uncultivated plants growing in the wild. Please include location information and photograph date as well.  Photos featuring diagnostic characters, flowers, plant habit, and habitat add significantly to the knowledge shared through the oregonflora website; we hope you consider sharing your expertise to make it even better! Contact us at <?php echo obfuscate("info@oregonflora.org") ?> for more information.</p>
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