"use strict";

class GardenPageApp extends React.Component {
  render() {
    return "Hello!";
  }
}

const domContainer = document.getElementById("react-app");
ReactDOM.render(<GardenPageApp />, domContainer);
