"use strict";

class GardenPageApp extends React.Component {
  render() {
    return "Hello!";
  }
}

window.onload = () => {
  const domContainer = document.getElementById("react-app");
  ReactDOM.render(<GardenPageApp />, domContainer);
};