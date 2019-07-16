const chooseNativeDropdownId = "choose-native-dropdown";
const chooseNativeDropdownButtonId = "choose-native-dropdown-button";
const searchResultsId = "search-results";

$(document).ready(() => {
  main();
});

function main() {
  const fadeIn = { opacity: 100, transition: "opacity 0.5s" };
  const fadeOut = { opacity: 0, transition: "opacity 0.5s" };

  const chooseNativeDropdown = $("#" + chooseNativeDropdownId);
  const chooseNativeDropdownButton = $("#" + chooseNativeDropdownButtonId);
  const chooseNativeDropdownCollapsing = $("#choose-native-dropdown .will-hide-on-collapse");

  chooseNativeDropdownButton.unbind("click").click(() => {
    const origImgUrl = chooseNativeDropdownButton.attr("src");
    let newImgUrl;

    // Collapse
    if (origImgUrl.includes("collapse-arrow.png")) {
      newImgUrl = origImgUrl.replace("collapse-arrow.png", "expand-arrow.png");
      chooseNativeDropdownCollapsing.css(fadeOut).slideUp("2s");

    // Expand
    } else {
      newImgUrl = origImgUrl.replace("expand-arrow.png", "collapse-arrow.png");
      chooseNativeDropdownCollapsing.slideDown(() => { chooseNativeDropdownCollapsing.css(fadeIn); });
    }

    chooseNativeDropdownButton.attr("src", newImgUrl);
  });
}

function addSearchResult(resultJson) {
  
}
