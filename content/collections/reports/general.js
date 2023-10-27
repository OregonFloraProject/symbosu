
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

// Insert OSU databased note
let labels = document.getElementsByClassName("label");
for (let label of labels) {
  var div = document.createElement("div");
  div.className = "label-databased";
  div.innerHTML = "[OSC Databased]";
  label.appendChild(div);
}