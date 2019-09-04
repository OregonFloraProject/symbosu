"use strict";

class InfographicDropdown extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isCollapsed: false
    };
  }

  onButtonClicked() {
    this.setState({ isCollapsed: !this.state.isCollapsed });
  }

  render() {
    return (
      <div
        id="infographic-dropdown"
        className="container-fluid p-5 d-print-none">

        <div className="row">
          <div className="col">
            <h1
              style={{ fontWeight: "bold", width: "80%" }}>
              Choose native plants for a smart, beautiful and truly Oregon garden
            </h1>
            <h3 className={ "w-50 will-collapse" + (this.state.isCollapsed ? " is-collapsed" : "") }>
              Native plants thrive in Oregon’s unique landscapes and growing
              conditions, making them both beautiful and wise gardening choices.
              Use the tools below to find plants best suited to your tastes and
              your yard.
            </h3>
          </div>

          <div className={ "col col-sm-3 will-collapse" + (this.state.isCollapsed ? " is-collapsed" : "")}>
            <h2 style={{ fontWeight: "bold" }}>Why native plants?</h2>
            <h4>They need less water and fewer chemicals when established.</h4>
            <h4>
              They attract native pollinators, birds and other helpful
              creatures.
            </h4>
            <h4>
              They preserve our natural landscape and support a healthy and
              diverse ecosystem.
            </h4>
            <h4>
              They provide critical habitat connections for birds and
              wildlife.
            </h4>
          </div>
        </div>

        <button
          style={{
            position: "absolute",
            bottom: 0,
            right: 0,
            background: "none",
            marginRight: "1em",
            marginBottom: "0.5em"
          }}
          onClick={ this.onButtonClicked.bind(this) }>
          <img
            src="/images/garden/collapse-arrow.png"
            className={ "will-v-flip" + (this.state.isCollapsed ? " v-flip" : "") }
            style={{
              width: "4em",
              height: "4em",
              opacity: "0.5"
            }}
            alt="toggle collapse"
          />
        </button>
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

class SideBar extends React.Component {
  constructor(props) {
    super(props);

  }

  render() {
    return (
      <div className="col-sm-3 m-2 p-5 rounded-border" style={{ background: "green", minHeight: "20em" }}>

      </div>
    );
  }
}

class SearchResultContainer extends React.Component {
  constructor(props) {
    super(props);

  }

  render() {
    return (
      <div className="col m-2 p-5 rounded-border" style={{ background: "blue", minHeight: "20em" }}>
        <div className="row my-4 rounded-border" style={{ background: "green", minHeight: "5em" }}>
        </div>
        <div className="row my-4 rounded-border" style={{ background: "green", minHeight: "10em" }}>
        </div>
      </div>
    );
  }
}

class GardenPageApp extends React.Component {
  render() {
    return (
      <div>
        <InfographicDropdown />
        <MainContentContainer>
          <SideBar />
          <SearchResultContainer />
        </MainContentContainer>
      </div>
    );
  }
}

const domContainer = document.getElementById("react-app");
ReactDOM.render(<GardenPageApp />, domContainer);
