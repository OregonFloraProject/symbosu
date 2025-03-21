import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import SearchWidget from '../common/search.jsx';
import httpGet from '../common/httpGet.js';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faBars } from '@fortawesome/free-solid-svg-icons';
library.add(faBars);

/*
const DROPDOWNS = [
  { title: "Tools" },
  { title: "Resources" },
  { title: "About" },
  { title: "Contribute" },
  { title: "Profile" },
];
*/
const DROPDOWNS = {
  tools: {
    title: 'Explore',
    children: [
      { title: 'Mapping', href: '/collections/map/index.php' },
      { title: 'Identify Plants', href: '/checklists/dynamicmap.php?interface=key' },
      { title: 'Inventories', href: '/projects/index.php' },
      { title: 'OSU Herbarium', href: '/collections/search/index.php?db[]=5,8,238,239,240' },
      { title: 'Grow Natives', href: '/garden/index.php' },
      { title: 'Rare Plants', href: '/pages/rare-plant-guide.php' },
      { title: 'Taxonomic Tree', href: '/taxa/taxonomy/taxonomydisplay.php' },
    ],
  },
  resources: {
    title: 'Resources',
    children: [
      { title: 'Tutorials and Tips', href: '/pages/tutorials.php' },
      { title: 'News and Events', href: '/pages/news-events.php' },
      { title: 'Links', href: '/pages/links.php' },
    ],
  },
  publications: {
    title: 'Publications',
    children: [
      { title: 'Flora of Oregon', href: '/pages/flora-of-oregon.php' },
      { title: 'Taxonomic Checklist', href: '/pages/taxonomic-checklist.php' },
      { title: 'Rare Plant Factsheets', href: '/pages/rare-plant-factsheets.php' },
      { title: 'Newsletters', href: '/newsletters/index.php' },
    ],
  },
  about: {
    title: 'About',
    children: [
      { title: 'Mission and History', href: '/pages/mission.php' },
      { title: 'Contact Info', href: '/pages/contact.php' },
      { title: 'Partners', href: '/pages/project-participants.php' },
    ],
  },
  contribute: {
    title: 'Contribute',
    children: [
      { title: 'Donate', href: '/pages/donate.php' },
      { title: 'Volunteer', href: '/pages/volunteer.php' },
      { title: 'OregonFlora Store', href: '/pages/store.php' },
    ],
  },
  profile: {
    title: 'Profile',
    children: [
      { title: 'Contact', href: `/pages/contact.php` },
      { title: 'Donate', href: `/pages/donate.php` },
      { title: 'Login', href: `/profile/index.php?refurl=${location.pathname}` },
    ],
  },
};

function HeaderButton(props) {
  return (
    <a href={props.href} rel="external">
      <button className={'col header-button no-symbiota-placement' + props.classes}>{props.title}</button>
    </a>
  );
}

function HeaderButtonBar(props) {
  return (
    <div className="row header-button-bar" style={props.style}>
      {props.children}
    </div>
  );
}

function getScrollPos() {
  return document.body.scrollTop || document.documentElement.scrollTop;
}

function HeaderDropdownItem(props) {
  return (
    <a className={'dropdown-item' + props.classes} rel="external" href={props.href} tabIndex={0}>
      {props.title}
    </a>
  );
}

function HeaderDropdown(props) {
  const [show, setShow] = useState(false);
  let id = props.title.replace(/[^a-zA-Z_]/g, '').toLowerCase();
  id = `header-dropdown-${id}`;
  return (
    <li
      className={`nav-item dropdown${show ? ' show' : ''}`}
      onClick={() => {
        setShow(!show);
      }}
      onBlur={(e) => {
        if (!e.currentTarget.contains(e.relatedTarget)) setShow(false);
      }}
    >
      <a
        id={id}
        className={'nav-link dropdown-toggle' + props.classes}
        href="#"
        role="button"
        aria-haspopup="true"
        aria-expanded={show}
        tabIndex={0}
      >
        {props.title}
      </a>
      <div className={`dropdown-menu${show ? ' show' : ''}`} aria-labelledby={id}>
        {props.children}
      </div>
    </li>
  );
}

class HeaderApp extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      //isCollapsed: getScrollPos() > 0,
      scrollLock: false,
      isLoading: false,
      searchText: '',
      dropdowns: DROPDOWNS,
      headerHeight: 100,
      isMobile: false,
      showMobileMenu: false,
      showMobileSearch: false,
    };

    this.onSearchTextChanged = this.onSearchTextChanged.bind(this);
    this.onSearch = this.onSearch.bind(this);
    this.handleScroll = this.handleScroll.bind(this);
    this.updateViewport = this.updateViewport.bind(this);
  }

  onSearchTextChanged(e) {
    this.setState({ searchText: e.target.value });
  }

  // "searchObj" is the JSON object returned from ../webservices/autofillsearch.php
  // WARNING - this code is copied exactly on home/main.jsx
  onSearch(searchObj) {
    this.setState({ isLoading: true });
    let targetUrl = ``;
    let defaultUrl = `${this.props.clientRoot}/taxa/index.php?taxon=${searchObj.taxonId}`; //default for this section

    if (searchObj.taxonId) {
      if (searchObj.taxonId != searchObj.tidaccepted) {
        //synonyms

        /* Check if taxonId is unique
					if so, go to tidaccepted page with reference to taxonid as synonym
					if not, go to taxon page (which will note the ambiguous synonyms)
				*/
        let api = `${this.props.clientRoot}/taxa/rpc/api.php?synonym=${searchObj.taxonId}`;
        httpGet(api).then((res) => {
          res = JSON.parse(res);
          //console.log(res);
          if (res.count == 1) {
            targetUrl = `${this.props.clientRoot}/taxa/index.php?taxon=${searchObj.tidaccepted}&synonym=${searchObj.taxonId}`;
          } else {
            targetUrl = defaultUrl;
          }
          window.location = targetUrl;
        });
      } else {
        targetUrl = defaultUrl;
        window.location = targetUrl;
      }
    } else {
      targetUrl = `${this.props.clientRoot}/taxa/search.php?search=${encodeURIComponent(searchObj.text)}`;
      window.location = targetUrl;
    }

    //window.open( targetUrl );
  }
  updateViewport() {
    let isMobile = false;
    if (window.innerWidth < 992) {
      isMobile = true;
    }
    this.setState({ isMobile: isMobile });
  }
  componentDidMount() {
    let dropdowns = DROPDOWNS;
    if (this.props.userName !== '') {
      dropdowns['profile'] = {
        title: 'Profile',
        children: [
          { title: 'My Profile', href: `/profile/viewprofile.php` },
          { title: 'Logout', href: `/profile/index.php?submit=logout` },
        ],
      };
    }
    Object.entries(dropdowns).map(([key]) => {
      dropdowns[key]['currentAncestor'] = false;
      Object.entries(dropdowns[key].children).map(([ckey]) => {
        dropdowns[key].children[ckey]['currentPage'] = false;
        dropdowns[key].children[ckey].href = this.props.clientRoot + dropdowns[key].children[ckey].href;
        let currURL = new URL(
          window.location.protocol + '//' + window.location.host + dropdowns[key].children[ckey].href,
        );
        if (currURL.pathname.indexOf(this.props.currentPage) == 0) {
          dropdowns[key].children[ckey]['currentPage'] = true;
          dropdowns[key]['currentAncestor'] = true;
        }
      });
    });

    this.setState({ dropdowns: dropdowns });

    window.addEventListener('scroll', this.handleScroll);
    this.updateViewport();
    window.addEventListener('resize', this.updateViewport);
  }
  componentWillUnmount() {
    window.removeEventListener('scroll', this.handleScroll, false);
    window.removeEventListener('resize', this.updateViewport, false);
  }
  handleScroll() {
    //console.log("scrollpos: " + getScrollPos());
    let scrollPos = getScrollPos();
    let headerHeight = scrollPos <= 40 ? 100 - scrollPos : 60;
    //console.log("hh: " + headerHeight);
    this.setState({ headerHeight: headerHeight });
  }
  getLoginButtons() {
    return (
      <HeaderButtonBar>
        {/* style={{ display: this.state.isCollapsed ? 'none' : 'flex' }}*/}
        {this.props.userName !== '' && (
          <a href="" rel="external" className={'disabled'}>
            <button className={'col header-button no-symbiota-placement'}>
              {'Hello, ' + this.props.userName + '!'}
            </button>
          </a>
        )}
        {this.state.dropdowns['profile'].children.map((dropDownChildData) => {
          let currentChild = dropDownChildData.currentPage ? ' current-page' : '';
          return (
            <HeaderButton
              key={dropDownChildData.title}
              title={dropDownChildData.title}
              href={`${dropDownChildData.href}`}
              classes={currentChild}
              rel="external"
            />
          );
        })}
      </HeaderButtonBar>
    );
  }

  render() {
    let lgLogo = `${this.props.clientRoot}/images/header/oregonflora-logo2.png`;
    let smLogo = `${this.props.clientRoot}/images/header/oregonflora-logo-sm2.png`;
    let mainOpacity = (this.state.headerHeight - 60) / 40;
    let fixedLogoClass = 'tall';
    if (this.state.headerHeight == 60) {
      fixedLogoClass = 'short';
    }

    return (
      <div
        className="header-wrapper"
        style={{ backgroundImage: `url(${this.props.clientRoot}/images/header/OF-Header_May8.png)` }}
      >
        <div className="fixed-logo-wrapper">
          <a
            id="fixed-logo"
            className={fixedLogoClass + ' navbar-brand'}
            rel="external"
            href={`${this.props.clientRoot}/`}
          >
            <img style={{ opacity: mainOpacity }} id="site-header-logo" src={lgLogo} alt="OregonFlora" />
            <img style={{ opacity: 1 - mainOpacity }} id="site-header-logo-sm" src={smLogo} alt="OregonFlora" />
          </a>
        </div>
        <nav
          id="site-header"
          className={`container navbar navbar-expand-lg navbar-dark site-header `}
          style={{ height: this.state.headerHeight }}
        >
          {/* ${this.state.isCollapsed ? "site-header-scroll" : ''} */}
          <div id="site-header-dropdowns-wrapper">
            <button
              id="site-header-navbar-toggler"
              className={'navbar-toggler ml-auto no-symbiota-placement' + (this.state.isMobile ? ' collapsed' : '')}
              type="button"
              aria-controls="navbarSupportedContent"
              aria-expanded={this.state.showMobileMenu}
              aria-label="Toggle navigation"
              onClick={() => {
                this.setState({ showMobileMenu: !this.state.showMobileMenu });
              }}
            >
              <span className="menu-toggle">
                <FontAwesomeIcon icon="bars" size="2x" />
              </span>
            </button>

            {
              <ul
                id="site-header-dropdowns"
                className={`navbar-nav${this.state.isMobile ? ' mobile-collapsed' : ''}${this.state.showMobileMenu ? ' mobile-show' : ''}`}
              >
                <div className="mobile-inner-container">
                  {Object.keys(this.state.dropdowns).map((key) => {
                    let currentParent = this.state.dropdowns[key].currentAncestor
                      ? ' current-page current-ancestor'
                      : '';
                    return (
                      <HeaderDropdown key={key} title={this.state.dropdowns[key].title} classes={currentParent}>
                        {this.state.dropdowns[key].children.map((dropDownChildData) => {
                          let currentChild = dropDownChildData.currentPage ? ' current-page' : '';

                          return (
                            <HeaderDropdownItem
                              key={dropDownChildData.title}
                              title={dropDownChildData.title}
                              href={`${dropDownChildData.href}`}
                              classes={currentChild}
                              rel="external"
                            />
                          );
                        })}
                      </HeaderDropdown>
                    );
                  })}
                </div>
              </ul>
            }
          </div>
          {
            <a className="navbar-brand" rel="external" href={`${this.props.clientRoot}/`}>
              <picture>
                <source media="(max-width: 992px)" srcSet={smLogo} />
                <source media="(min-width: 992px)" srcSet={lgLogo} />
                <img id="site-header-logo" src={lgLogo} alt="OregonFlora" />
              </picture>
            </a>
          }

          <div className={'search-wrapper ml-auto'}>
            {this.getLoginButtons()}
            <button
              id="site-search-toggler"
              className="no-symbiota-placement"
              type="button"
              aria-expanded="false"
              aria-controls="search-widget-wrapper"
              onClick={() => {
                this.setState({ showMobileSearch: !this.state.showMobileSearch });
              }}
            >
              <span>Plant search</span>
              <img src={`${this.props.clientRoot}/images/icons/search-icon-2x.png`} />
            </button>
            <div
              className={`row widget-wrapper mobile-collapsed${this.state.showMobileSearch ? ' mobile-show' : ''}`}
              id="search-widget-wrapper"
            >
              <SearchWidget
                placeholder="Search all plants"
                clientRoot={this.props.clientRoot}
                isLoading={this.state.isLoading}
                textValue={this.state.searchText}
                onTextValueChanged={this.onSearchTextChanged}
                onSearch={this.onSearch}
                suggestionUrl={`${this.props.clientRoot}/webservices/autofillsearch.php`}
              />
            </div>
          </div>
        </nav>
      </div>
    );
  }
}

const domContainer = document.getElementById('react-header');
const dataProps = JSON.parse(domContainer.getAttribute('data-props'));
ReactDOM.render(
  <HeaderApp
    clientRoot={dataProps['clientRoot']}
    userName={dataProps['userName']}
    currentPage={dataProps['currentPage']}
  />,
  domContainer,
);

{
  /*
      <!-- Holds dropdowns on mobile -->

      
      <!-- Search -->
      <form
        className="form-inline ml-auto"
        name="quick-search"
        id="quick-search"
        autoComplete="off"
        action="<?php echo $clientRoot . '/taxa/index.php'?>">
        <div className="input-group">
          <div className="dropdown">
            <input id="search-term" name="taxon" type="text" className="form-control dropdown-toggle"
                   data-toggle="dropdown" placeholder="Search all plants">
              <div id="autocomplete-results" className="dropdown-menu" aria-labelledby="search-term">
                <a className="dropdown-item" onClick="document.getElementById('search-term').value = this.innerHTML;"
                   href="#" />
                <a className="dropdown-item" onClick="document.getElementById('search-term').value = this.innerHTML;"
                   href="#" />
                <a className="dropdown-item" onClick="document.getElementById('search-term').value = this.innerHTML;"
                   href="#" />
                <a className="dropdown-item" onClick="document.getElementById('search-term').value = this.innerHTML;"
                   href="#" />
                <a className="dropdown-item" onClick="document.getElementById('search-term').value = this.innerHTML;"
                   href="#" />
              </div>
          </div>
          <input
            id="search-btn"
            src="<?php echo $clientRoot; ?>/images/header/search-white.png"
            className="mt-auto mb-auto"
            type="image" />
        </div>
      </form>
      <!-- Search end -->

    </nav>
  <!-- Header end -->
  */
}
