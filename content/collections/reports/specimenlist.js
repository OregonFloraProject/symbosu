
document.addEventListener('keydown', function(e) {

  // Check whether editing is enabled
  let isEditable = document.querySelector('.body').contentEditable === 'true';

  // Look for ctrl + modifier on windows or command + modifier on macs
  let ctrl = e.ctrlKey || (e.metaKey && navigator.userAgent.search('Mac'));

  // Ctrl + I
  if (isEditable && ctrl && e.key === 'i') {

    // Prevent default browser action
    e.preventDefault();

    // Italicize text
    document.execCommand('italic', false, null);

  // Ctrl + B
  } else if (isEditable && ctrl && e.key === 'b') {

    // Prevent default browser action
    e.preventDefault();

    // Bold text
    document.execCommand('bold', false, null);

  // Ctrl + U
  } else if (isEditable && ctrl && e.key === 'u') {

    // Prevent default browser action
    e.preventDefault();

    // Underline text
    document.execCommand('underline', false, null);
  }
  
});

var div = document.createElement("div");
div.className = "label";
div.classList.add("header");
div.innerHTML = '<div class="label-header"></div><div class="label-blocks"><div class="field-block"><span class="recordedby">Collector</span><span class="recordnumber">Coll#</span><span class="county">County<span class="countySuffix">&nbsp;</span></span><span class="stateprovince">State</span><span class="speciesname">Taxon</span><span class="taxonrank">&nbsp;</span><span class="infraspecificepithet">&nbsp;</span></div></div>';
let firstlabel = document.querySelector('.label');

var title = document.createElement("div");
title.classname = "title-block"

title.innerHTML = "<h2>Specimen List</h2><span>[Your notes here]</span><br/><br/>"

firstlabel.parentNode.insertBefore(title, firstlabel);

firstlabel.parentNode.insertBefore(div, firstlabel);