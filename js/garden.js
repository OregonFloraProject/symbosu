const chooseNativeDropdownId = "choose-native-dropdown";
const chooseNativeDropdownButtonId = "choose-native-dropdown-button";
const searchResultsId = "search-results";
const plantSearchId = "plant-search";
const plantHeightSliderId = "plant-height";
const plantWidthSliderId = "plant-width";
const plantHeightDisplayId = "plant-height-display";
const plantWidthDisplayId = "plant-width-display";
const searchParamClassId = "search-param";
const searchButtonId = "search-plants-btn";
const searchHelpId = "search-help";
const allDropDownArrowsClassId = "arrow";
const availabilityDropdownId = "availability";

jQuery(() => {
  gardenMain();
});

class SearchResult {
  constructor(plantTid, plantName, plantImage) {
    this._plantName = plantName;
    this._plantImage = plantImage;
    this._plantLink = `../taxa/garden.php?taxon=${plantTid}`;
  }

  getHTML() {
    return `
      <div class="card search-result p-0">
        <div class="card-body w-100 h-100 p-0">
          <a href="${this._plantLink}">
            <img class="w-100 h-100" src="${this._plantImage}">
            <div class="search-result-overlay"><p>${this._plantName}</p></div>
          </a>
        </div>
      </div>
`;
  }
}

/**
 * Main method for the garden page
 */
function gardenMain() {
  const fadeIn = { opacity: 100, transition: "opacity 0.5s" };
  const fadeOut = { opacity: 0, transition: "opacity 0.5s" };

  const paddingTransitionSmall = { padding: "0.5em", transition: "padding 1s" };
  const paddingTransitionBig = { padding: "2em", transition: "padding 1s" };

  const chooseNativeDropdown = $("#" + chooseNativeDropdownId);
  const chooseNativeDropdownButton = $("#" + chooseNativeDropdownButtonId);
  const chooseNativeDropdownCollapsing = $("#choose-native-dropdown .will-hide-on-collapse");

  const searchHelp = $("#" + searchHelpId);

  const plantWidthSlider = $("#" + plantWidthSliderId);
  const plantHeightSlider = $("#" + plantHeightSliderId);
  const plantWidthDisplay = $("#" + plantWidthDisplayId);
  const plantHeightDisplay = $("#" + plantHeightDisplayId);

  const searchPlantsBtn = $("#" + searchButtonId);

  const allSearchParams = $("." + searchParamClassId);
  const allDropDownArrows = $("." + allDropDownArrowsClassId);

  const availabilityDropdown = $("#" + availabilityDropdownId);

  // Search
  $(searchPlantsBtn).click(() => {
    const searchParamObj = {};
    allSearchParams.each((idx, val) => {
      searchParamObj[$(val).attr("name")] = $(val).val();
    });
    console.log(searchParamObj);
    pullSearchResults(searchParamObj)
      .then((res) => {
        populateSearchResults(res);
      })
      .catch((err) => {
        console.error("Search returned '" + err + "'");
      });
  });
  searchHelp.popover({
    title: "Search for plants",
    html: true,
    content: `
      <ul>
        <li>As you make selections, the filtered results are immediately displayed in “Your search results”.</li>
        <li>Any number of search options may be selected, but too many filters may yield no results because no plant meets all the criteria you selected. If so, try removing filters.</li>
        <li>To remove a search filter, simply click its close (X) button</li>
        <li>Clicking on any image in the results will open that plants’ garden profile page; the page can be downloaded and printed.</li>
      </ul>
    `,
    trigger: "focus"
  });

  // Sliders
  plantWidthSlider.on("input change", () => {
    updateSliderDisplay(plantWidthSlider, plantWidthDisplay);
  });

  plantHeightSlider.on("input change", () => {
    updateSliderDisplay(plantHeightSlider, plantHeightDisplay);
  });

  // Free up all arrow buttons for custom events
  allDropDownArrows.unbind("click");

  // Infographic dropdown
  chooseNativeDropdownButton.click(() => {
    const origImgUrl = chooseNativeDropdownButton.attr("src");

    // Collapse
    if (origImgUrl.includes("collapse-arrow.png")) {
      $("#page-title").addClass("collapsed");
      chooseNativeDropdown.css(paddingTransitionSmall);
      chooseNativeDropdownCollapsing.css(fadeOut).slideUp("2s", () => {
      });

    // Expand
    } else {
      chooseNativeDropdown.css(paddingTransitionBig);
      chooseNativeDropdownCollapsing.slideDown(() => {
        chooseNativeDropdownCollapsing.css(fadeIn);
        $("#page-title").removeClass("collapsed");
      });
    }
  });

  // Dropdown arrows
  allDropDownArrows.click((e) => {
    const origImgUrl = $(e.target).attr("src");
    let newImgUrl;

    if (origImgUrl.includes("collapse-arrow.png")) {
      newImgUrl = origImgUrl.replace("collapse-arrow.png", "expand-arrow.png");
    } else {
      newImgUrl = origImgUrl.replace("expand-arrow.png", "collapse-arrow.png");
    }

    $(e.target).attr("src", newImgUrl);
  });

  // Disable the "Availability" dropdown in the sidebar
  availabilityDropdown.find("*")
    .off("click.*")
    .prop("disabled", true);
}

/**
 * Pull search results based on form data from the api endpoint
 * @return {Promise<Object>} Promise to return the results as a JSON object
 */
function pullSearchResults(paramObj) {
  return new Promise(((resolve, reject) => {
    const req = new XMLHttpRequest();
    req.onload = () => {
      if (req.status === 200) {
        try {
          resolve(JSON.parse(req.responseText));
        } catch (e) {
          console.error("Error parsing JSON response: " + e);
        }
      } else {
        reject(req.status + ": " + req.statusText);
      }
    };

    // TODO: ClientRoot
    let url = "./rpc/api.php?";

    if (paramObj.search !== null) {
      url += `search=${paramObj.search}`;
    }
    console.log(url);

    req.open("GET", url, true);
    req.send();
  }));
}

/**
 * Populate the search results based upon the given JSON object
 */
function populateSearchResults(resultJsonArray) {
  const searchResults = $('#' + searchResultsId);
  searchResults.empty();
  for (let i = 0; i < resultJsonArray.length; i++) {
    let resultCard = new SearchResult(
      resultJsonArray[i].tid,
      resultJsonArray[i].vernacularname,
      resultJsonArray[i].image,
    );
    searchResults.append(resultCard.getHTML());
  }
}

/**
 * Updates text label based on slider positions
 * @param  {$(bootstrap-slider)}  slider  The slider jQuery element
 * @param  {$(label)}             display The label to update
 */
function updateSliderDisplay(slider, display) {
  let [sliderValueLow, sliderValueHigh] = slider.val().trim("[]").split(",").map((str) => parseInt(str));
  let displayText;

  if (sliderValueLow > sliderValueHigh) {
    slider.val("[" + sliderValueHigh + "," + sliderValueLow + "]");
    let tmp = sliderValueLow;
    sliderValueLow = sliderValueHigh;
    sliderValueHigh = tmp;
  }

  if (sliderValueLow === 0 && sliderValueHigh === 50) {
    displayText = "(Any size)";
  } else if (sliderValueLow === sliderValueHigh) {
    displayText = "(";
    if (sliderValueLow === 50) {
      displayText += "At least ";
    }
    displayText += sliderValueLow + " ft)";
  } else if (sliderValueHigh === 50) {
    displayText = "(At least " + sliderValueLow + " ft)";
  } else if (sliderValueLow === 0) {
    displayText = "(At most " + sliderValueHigh + " ft)";
  } else {
    displayText = "(" + sliderValueLow + " ft - " + sliderValueHigh + " ft)";
  }

  display.text(displayText);
}
