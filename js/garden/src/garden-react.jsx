"use strict";

import InfographicDropdown from "./infographic-dropdown.jsx";
import SideBar from "./sidebar.jsx";
import { SearchResultGrid, SearchResult } from "./search-results.jsx";

/**
 * @param url URL to GET
 * @returns {Promise<string>} Either the response text or error code/text
 */
function httpGet(url) {
  return new Promise((resolve, reject) => {
    const req = new XMLHttpRequest();
    req.onload = () => {
      if (req.status === 200) {
        resolve(req.responseText);
      } else {
        reject(`${req.status.toString()} ${req.statusText}`);
      }
    };

    req.open("GET", url);
    req.send();
  });
}

function getTaxaPage(tid) {
  return `../taxa/garden.php?taxon=${tid}`;
}

function MainContentContainer(props) {
  return (
    <div className="container-fluid">
      <div className="row">
        {props.children}
      </div>
    </div>
  );
}

class GardenPageApp extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isLoading: false,
      sunlight: "",
      moisture: "",
      height: [0, 50],
      width: [0, 50],
      searchResults: [],
    };

    this.onSearch = this.onSearch.bind(this);
    this.onSearchResults = this.onSearchResults.bind(this);
    this.onSunlightChanged =  this.onSunlightChanged.bind(this);
    this.onMoistureChanged =  this.onMoistureChanged.bind(this);
    this.onHeightChanged =  this.onHeightChanged.bind(this);
    this.onWidthChanged =  this.onWidthChanged.bind(this);
  }

  // On search start
  onSearch(searchText) {
    this.setState({ isLoading: true });
    httpGet(`/garden/rpc/api.php?search=${searchText}`)
      .then((res) => {
        this.onSearchResults(JSON.parse(res));
      })
      .catch((err) => {
        console.error(err);
      })
      .finally(() => {
        this.setState({ isLoading: false });
      });
  }

  // On search end
  onSearchResults(results) {
    this.setState({ searchResults: results });
  }

  onSunlightChanged(event) {
    this.setState({ sunlight: event.target.value }, () => {
      console.log(`sunlight: ${this.state.sunlight}`);
    });
  }

  onMoistureChanged(event) {
    this.setState({ moisture: event.target.value }, () => {
      console.log(`moisture: ${this.state.moisture}`);
    });
  }

  onHeightChanged(event) {
    this.setState({ height: event.target.value }, () => {
      console.log(`height: ${this.state.height}`);
    });
  }

  onWidthChanged(event) {
    this.setState({ width: event.target.value }, () => {
      console.log(`width: ${this.state.width}`);
    });
  }

  render() {
    return (
      <div>
        <InfographicDropdown />
        <MainContentContainer>
          <SideBar
            style={{ background: "#DFEFD3" }}
            isLoading={ this.state.isLoading }
            sunlight={ this.state.sunlight }
            moisture={ this.state.moisture }
            height={ this.state.height }
            width={ this.state.width }
            onSearch={ this.onSearch }
            onSunlightChanged={ this.onSunlightChanged }
            onMoistureChanged={ this.onMoistureChanged }
            onHeightChanged={ this.onHeightChanged }
            onWidthChanged={ this.onWidthChanged }
          />
          <SearchResultGrid>
            {
              this.state.searchResults.map((result, idx) =>
                <SearchResult
                  style={{ display: (idx < 19 ? "initial" : "none") }}
                  key={ result.tid }
                  href={ getTaxaPage(result.tid) }
                  src={ result.image }
                  title={ result.sciname }
                  text={ result.vernacularname ? result.vernacularname : result.sciname }
                />
              )
            }
          </SearchResultGrid>
        </MainContentContainer>
      </div>
    );
  }
}

const domContainer = document.getElementById("react-app");
ReactDOM.render(<GardenPageApp />, domContainer);
