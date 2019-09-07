"use strict";

import InfographicDropdown from "./infographic-dropdown.jsx";
import SideBar from "./sidebar.jsx";

class SearchResultContainer extends React.Component {
  constructor(props) {
    super(props);

  }

  render() {
    return (
      <div className="col m-2 p-5 rounded-border" style={{ background: "#DFEFD3", minHeight: "20em" }}>
        {/*<div className="row my-4 rounded-border" style={{ background: "green", minHeight: "5em" }}>*/}
        {/*</div>*/}
        {/*<div className="row my-4 rounded-border" style={{ background: "green", minHeight: "10em" }}>*/}
        {/*</div>*/}
      </div>
    );
  }
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
  render() {
    return (
      <div>
        <InfographicDropdown />
        <MainContentContainer>
          <SideBar style={{ minWidth: "25em", background: "#DFEFD3" }} />
          <SearchResultContainer />
        </MainContentContainer>
      </div>
    );
  }
}

const domContainer = document.getElementById("react-app");
ReactDOM.render(<GardenPageApp />, domContainer);
