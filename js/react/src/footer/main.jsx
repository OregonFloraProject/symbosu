import React from 'react';
import ReactDOM from 'react-dom';

class FooterApp extends React.Component {
  render() {
    let today = new Date();
    const year = today.getFullYear();

    return (
      <div className="mb-4">
        <div className="navbar navbar-expand-lg navbar-dark">
          <nav className="container">
            <ul className="navbar-nav">
              <li className="nav-item">
                <a href={`${this.props.clientRoot}/pages/contact.php`} className="nav-link">
                  Contact
                </a>
              </li>
              <li className="nav-item">
                <a href="https://oregonstate.edu/official-web-disclaimer" className="nav-link">
                  Disclaimer
                </a>
              </li>
              <li className="nav-item">
                <a href={`${this.props.clientRoot}/sitemap.php`} className="nav-link">
                  Site Map
                </a>
              </li>
              {/*
            <li className="nav-item">
              <a href="mailto:info@oregonflora.org" target="_blank" className="nav-link">Site Feedback</a>
            </li>
            */}
              <li className="nav-item login">
                <a
                  href={`${this.props.clientRoot}/profile/index.php?refurl=${window.location.pathname}`}
                  className="nav-link"
                >
                  Login
                </a>
              </li>
            </ul>
            <div className="nav-item copyright">
              All website content &copy; {year} OregonFlora unless otherwise noted
            </div>
          </nav>
        </div>
        <div id="footer-content" className="container-fluid container">
          <div className="row py-4">
            <div className="col-md donate">
              <div>
                <div>
                  <a
                    href={`${this.props.clientRoot}/pages/donate.php`}
                    className="btn btn-primary donate-button"
                    role="button"
                  >
                    Donate!
                  </a>
                </div>
                <p className="mt-2">
                  OregonFlora is based at the{' '}
                  <a href="https://bpp.oregonstate.edu/herbarium" target="_blank" rel="noreferrer">
                    OSU Herbarium
                  </a>{' '}
                  at Oregon State University. Our program is funded through grants and contributions. We welcome your
                  support!
                </p>
              </div>
            </div>
            <div className="col-md osu">
              <a href={'https://oregonstate.edu/'} target="_blank" rel="noreferrer">
                <img src={`${this.props.clientRoot}/images/footer/osu_horizontal_2c_o_over_b.png`} alt="OSU Logo" />
              </a>
              <p className="my-2">
                <strong>OregonFlora</strong>
                <br />
                <a href={'https://bpp.oregonstate.edu/'} target="_blank" rel="noreferrer">
                  Dept. Botany & Plant Pathology
                </a>
                , OSU
                <br />
                2701 SW Campus Way, Corvallis, OR 97331
                <br />
                <a href="mailto:info@oregonflora.org" target="_blank" rel="noreferrer">
                  info@oregonflora.org
                </a>
              </p>
            </div>
            <div className="col-md symbiota">
              <a href={'https://symbiota.org/'} target="_blank" rel="noreferrer">
                <img
                  className="d-block mb-2"
                  style={{ width: '10em' }}
                  src={`${this.props.clientRoot}/images/footer/LogoSymbiotaPNG.png`}
                  alt="Symbiota logo"
                />
              </a>
              <p>
                OregonFlora is built on{' '}
                <a href={'https://symbiota.org/'} target="_blank" rel="noreferrer">
                  <strong>Symbiota</strong>
                </a>
                , a collaborative, open source content management system for curating specimen- and observation-based
                biodiversity data.
              </p>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

FooterApp.defaultProps = {
  clientRoot: '',
};

const domContainer = document.getElementById('footer-app');
const clientRoot = domContainer.getAttribute('data-client-root');
ReactDOM.render(<FooterApp clientRoot={clientRoot} />, domContainer);
