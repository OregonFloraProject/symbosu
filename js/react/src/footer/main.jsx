import React from "react";
import ReactDOM from "react-dom";

class FooterApp extends React.Component {

  render() {
    return (
      <div style={{ height: "20em" }}>
        <nav className="navbar navbar-expand-lg navbar-dark">
          <ul className="navbar-nav">
            <li className="nav-item"><a href={ `${this.props.clientRoot}/pages/contact.php` } className="nav-link">Contact</a></li>
            <li className="nav-item"><a href="#" className="nav-link">Disclaimer</a></li>
            <li className="nav-item"><a href="#" className="nav-link">Site Map</a></li>
            <li className="nav-item"><a href="#" className="nav-link">Site Feedback</a></li>
            <li className="nav-item"><a href="#" className="nav-link">Login</a></li>
          </ul>
          <div className="nav-item ml-auto my-auto">All website content &copy; 2019 OregonFlora unless otherwise noted</div>
        </nav>
        <div id="footer-content" className="container-fluid">
          <div className="row">
            <div className="col">
              <div>
                <p className="mt-3 mx-auto px-5">
                  OregonFlora is based at the OSU Herbarium at Oregon State University.
                  Our program is wholly funded through grants and contributions. We welcome your support!
                </p>
                <div className="mx-auto text-center">
                  <a href={ `${this.props.clientRoot}/pages/donate.php` } className="btn btn-primary" role="button">
                    Donate!
                  </a>
                </div>
              </div>
            </div>
            <div className="col"/>
            <div className="col"/>
          </div>
        </div>
      </div>
    );
  }
}

FooterApp.defaultProps = {
  clientRoot: ''
};

const domContainer = document.getElementById("footer-app");
const clientRoot = domContainer.getAttribute("data-client-root");
ReactDOM.render(<FooterApp clientRoot={ clientRoot }/>, domContainer);