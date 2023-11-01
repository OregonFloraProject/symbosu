// function to show glossary tooltips
function showTooltip(tmp, id) {

  // Stop the onclick event to prevent opening a menu
  event.stopPropagation();

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
          '"></img>' + (img.createdBy ? '<span class="credit">Image credit: ' + img.createdBy + '</span>' : '') +  ' <div>';
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