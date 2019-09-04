const chooseNativeDropdownId = "choose-native-dropdown";
const chooseNativeDropdownButtonId = "choose-native-dropdown-button";
const searchResultsId = "search-results";
const plantSearchId = "plant-search";
const plantHeightSliderId = "plant-height";
const plantWidthSliderId = "plant-width";
const plantHeightDisplayId = "plant-height-display";
const plantWidthDisplayId = "plant-width-display";
const searchButtonId = "search-plants-btn";
const searchHelpId = "search-help";
const allDropDownArrowsClassId = "arrow";
const availabilityDropdownId = "availability";

jQuery(() => {
  gardenMain();
});

class SearchResult {
  constructor(plantTid, plantNameCommon, plantNameSci, plantImage) {
    this._plantTid = plantTid;
    this._plantNameCommon = plantNameCommon;
    this._plantNameSci = plantNameSci;
    this._plantImage = plantImage;
  }

  getCommonName() {
    return this._plantNameCommon;
  }

  getSciName() {
    return this._plantNameSci;
  }

  getImageUrl() {
    return this._plantImage;
  }

  getTaxaLink() {
    return `../taxa/garden.php?taxon=${this._plantTid}`;
  }

  getTid() {
    return this._plantTid;
  }

  getHTML() {
    return `
      <div class="card">
        <a href="${this.getTaxaLink()}">
          <img class="card-img-top search-result-img" src="${this._plantImage}" alt="${this._plantNameCommon}">
          <div class="card-body">
            <h5 class="card-title">${this._plantNameCommon !== null ? this._plantNameCommon : this._plantNameSci}</h5>
            <p class="card-text">${this._plantNameSci}</p>
          </div>
        </a>
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

  const plantSearchField = $("#" + plantSearchId);
  const plantWidthSlider = $("#" + plantWidthSliderId);
  const plantHeightSlider = $("#" + plantHeightSliderId);
  const plantWidthDisplay = $("#" + plantWidthDisplayId);
  const plantHeightDisplay = $("#" + plantHeightDisplayId);

  const searchPlantsBtn = $("#" + searchButtonId);

  const allDropDownArrows = $("." + allDropDownArrowsClassId);

  const availabilityDropdown = $("#" + availabilityDropdownId);

  // Search help
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

  // Search button click
  searchPlantsBtn.click(() => {
   if (plantSearchField.val() != null && plantSearchField.val() !== '') {
     searchPlantsBtn.attr("disabled", true);
     $(`#${searchButtonId} .spinner-border`).show();
     $(`#${searchButtonId} img`).hide();
     populateSearchResults({ search: "abe" })
       .then(() => {
         searchPlantsBtn.attr("disabled", false);
         $(`#${searchButtonId} img`).show();
         $(`#${searchButtonId} .spinner-border`).hide();
       });
   } else {
     // TODO: Bootstrap error
     alert("Enter a plant name");
   }
  });

  // Sliders
  plantWidthSlider.on("input change", () => {
    updateSliderDisplay(plantWidthSlider, plantWidthDisplay);
  });

  $(`#${plantWidthSliderId}-container .slider-handle`).mouseup(() => {
    console.log(`Plant Width: ${plantWidthSlider.val()}`);
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
 * Sets a cookie named name with value value that expires in exprDays days
 */
function setCookie(name, value, exprDays) {
  const expDate = new Date();
  expDate.setTime(expDate.getTime() + (exprDays * 24 * 60 * 60 * 1000));
  document.cookie = `${name}=${value}; expires=${expDate.toUTCString()}; path=/`;
}

/**
 * Returns the cookie with name name as a Javascript object
 */
function getCookie(name) {
  let cookies = decodeURIComponent(document.cookie).split(";");
  for (let i = 0; i < cookies.length; i++) {
    let [key, val] = cookies[i].split("=");
    if (key.trim().toLowerCase() === name.toLowerCase()) {
      if (typeof val === "string") {
        return val.trim();
      }
      return val;
    }
  }
  return null;
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

function httpGet(url) {
  return new Promise((resolve, reject) => {
    const req = new XMLHttpRequest();
    req.addEventListener("load", (e) => {
      if (req.status === 200) {
        resolve(req.responseText);
      } else {
        reject(req.code);
      }
    });

    req.open("GET", url);
    req.send();
  });
}

/**
 * Pull search results from ./rpc/api.php
 * @param props Object Dictionary of GET parameters
 *  - search: string Search term for plant name
 *  - width Array 2-element array in the form [min_width, max_width]
 *  - height Array 2-element array in the form [min_height, max_height]
 */
function pullSearchResults(props) {
  let reqUrl = "./rpc/api.php";

  const searchParams = [];
  const propKeys = Object.keys(props);
  for (let i = 0; i < propKeys.length; i++) {
    let key = propKeys[i];
    let val = props[key];
    searchParams.push(`${key}=${val}`);
  }

  if (searchParams.length > 0) {
    reqUrl += "?" + searchParams.join("&");
  }

  return new Promise((resolve) => {
    httpGet(reqUrl)
      .then((res) => { resolve(JSON.parse(res)); })
      .catch((err) => {
        console.error(reqUrl + " returned status " + err);
        resolve(null);
      });
  });
}

/**
 * Populate the search results based upon the given JSON object
 */
function populateSearchResults(props) {
  const searchResultsContainer = $('#' + searchResultsId);
  searchResultsContainer.empty();

  return pullSearchResults(props)
    .then((res) => {
      for (let i = 0; i < res.length; i++) {
        let resultCard = new SearchResult(
          res[i].tid,
          res[i].vernacularname,
          res[i].sciname,
          res[i].image,
        );
        searchResultsContainer.append(resultCard.getHTML());
      }
    });
}