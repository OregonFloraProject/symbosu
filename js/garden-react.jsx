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
        className="container-fluid">

        <div
          style={{
            display: "grid",
            gridTemplateColumns: "75% 25%",
            gridAutoFlow: "column",
            gridGap: "2em",
            padding: "3em"
          }}
        >
          <div>
            <h1 style={{ width: this.state.isCollapsed ? "100%" : "50%", fontWeight: "bold" }}>
              Choose native plants for a smart, beautiful and truly Oregon garden
            </h1>
            <h3 className={ "will-collapse" + (this.state.isCollapsed ? " is-collapsed" : "") }>
              Native plants thrive in Oregon’s unique landscapes and growing
              conditions, making them both beautiful and wise gardening choices.
              Use the tools below to find plants best suited to your tastes and
              your yard.
            </h3>
          </div>

          <div className={ "will-collapse" + (this.state.isCollapsed ? " is-collapsed" : "")}>
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
            className={ "will-v-flip" + (this.state.isCollapsed ? " v-flipped" : "") }
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

class GardenPageApp extends React.Component {
  render() {
    return (
      <InfographicDropdown />
    )
  }
}

const domContainer = document.getElementById("react-app");
ReactDOM.render(<GardenPageApp />, domContainer);
