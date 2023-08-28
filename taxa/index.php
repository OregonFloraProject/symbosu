<?php
include_once("../config/symbini.php");
?>
<html>
  <head>
    <meta charset="utf-8"/>
    <title><?php echo $DEFAULT_TITLE; ?></title>
  </head>

  <body>
    <?php
      include("$SERVER_ROOT/header.php");
    ?>
    <!-- Include page style here to override anything in header -->
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/theme.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $CLIENT_ROOT?>/css/compiled/taxa.css">
    
    
    <!-- image carousel -->
    <link rel="stylesheet" type="text/css" charset="UTF-8" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" />

    <script src="../js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <link href="../js/jquery-ui/jquery-ui.min.css" type="text/css" rel="Stylesheet" />
    <script>

        // function to show glossary tooltips
        function showTooltip(tmp, id) {

          // Adds a blank title to the glossary word, just in time for jquery-ui tooltips (enables tooltip)
          $('#' + tmp.id).prop('title', ' ');

          // Get the glossary definition
          $.get( '../glossary/rpc/getterms.php?id=' + id, function( term ) {

            let content = "";

            // Check if there are images, and if so, add them to the tooltip
            if ( Object.keys(term.images).length ) {

              // Start image div
              content += '<div>';

              for (const [key, img] of Object.entries(term.images)) {

                content += '<div class="img"><img src="' + img.thumbnailurl +
                  '" width="' + img.tn_width + '" height="' + img.tn_height +
                  '"></img><span class="credit">Image credit: ' + img.createdBy + '</span><div>';
              }

              // Iterated through all the images, finish up
              // End image div and add a horizontal rule after the images
              content += '</div><hr/>';

            }

            // If this is a synonym redirected from original term, note that
            if( term.redirect ) {
              content += '<div><span style="font-weight: bold">' + term.term + '</span> (redirected from ' + term.redirect + ')</div><hr/>';
            }

            // Add the definition
            content += '<span>' + term.definition + '</span>';

            // Construct the tooltip and add options
            $( "#" + tmp.id ).tooltip({

              // Tooltip theme classes to add
              classes: {
                "ui-tooltip": "ui-corner-all"
              },

              // Position the tooltip so that the bottom-left of the tooltip coincides with the top-left of the glossary word
              position: {
                my: "left bottom",
                at: "left top"
              },

              // Time to show and hide tooltips, slows down appear/disappear a bit
              show: 200,
              hide: 500,

              // Add content we created to the tooltip
              content: content,

              // When the tooltip closes, disable it, this prevents it from subsequently popping up on hover
              close: function(event, ui) {
                $( "#" + tmp.id ).tooltip("disable");
              }
            })

            // Enable the tooltip (in case it was disabled)
            .tooltip("enable")

            // Open the tooltip manually
            .tooltip("open");

          });
        }

    </script>

    <style>

      /* Glossary words styling */

      /* Set the box shadow for glossary tooltips */
      .ui-tooltip {
        -webkit-box-shadow: 3px 3px 10px 0px rgba(0,0,0,0.5);
        -moz-box-shadow: 3px 3px 10px 0px rgba(0,0,0,0.5);
        box-shadow: 3px 3px 10px 0px rgba(0,0,0,0.5);
      }

      /* Tigher spacing for hr used to set images and redirect apart from glossary tooltip definition */
      .ui-tooltip hr {
        margin: 3px;
      }

      /* Glossary image */
      .ui-tooltip div.img {
        vertical-align: top;
        display: inline-block;
        text-align: center;
      }

      /* Glossary image credit */
      .credit {
        display: block;
        font-size: 0.75em;
      }

    </style>
    <!-- This is inner text! -->
    <div id="innertext">
      <div id="react-taxa-app"></div>
				<script 
					src="<?php echo $CLIENT_ROOT?>/js/react/dist/taxa.js?<?php echo filemtime($SERVER_ROOT . '/js/react/dist/taxa.js'); ?>"
					type="text/javascript">
				</script>
    </div>

    <?php
      include("$SERVER_ROOT/footer.php");
    ?>
  </body>
</html>
