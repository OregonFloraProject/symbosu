import React from 'react';

import { SearchWidget } from './search.jsx';
import FeatureSelector from './sidebar/featureSelector.jsx';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faFileCsv,
  faFileWord,
  faPrint,
  faChevronDown,
  faChevronUp,
  faChevronCircleDown,
  faChevronCircleUp,
  faCircle,
  faExternalLinkAlt,
} from '@fortawesome/free-solid-svg-icons';
library.add(
  faFileCsv,
  faFileWord,
  faPrint,
  faChevronDown,
  faChevronUp,
  faChevronCircleDown,
  faChevronCircleUp,
  faCircle,
  faExternalLinkAlt,
);

/**
 * Sidebar header with title, subtitle, and help
 */
class SideBarHeading extends React.Component {
  render() {
    return (
      <div style={{ color: 'black' }}>
        <div className="mb-1 pt-2" style={{ color: 'inherit' }}>
          <h3 className="font-weight-bold d-inline">Search for plants</h3>
        </div>
        <p className="container">Start applying characteristics, and the matching plants will appear at right.</p>
      </div>
    );
  }
}

class SidebarAccordion extends React.Component {
  constructor(props) {
    super(props);
    this.state = { isExpanded: false };
    this.onButtonClicked = this.onButtonClicked.bind(this);
  }

  onButtonClicked() {
    if (this.props.disabled !== 'true') {
      this.setState({ isExpanded: !this.state.isExpanded });
    }
  }

  render() {
    let dropdownId = this.props.title;
    dropdownId = dropdownId
      .toLowerCase()
      .replace(/[^a-z]/g, '')
      .concat('-accordion');
    let projectHref = `${this.props.clientRoot}/projects/index.php?pid=4`;
    return (
      <div className={'top-level' + (this.props.disabled === true ? ' dropdown-disabled' : '')} id={dropdownId}>
        <div className="row">
          <h4 className="">
            {this.props.title}
            {this.props.title == 'Commercial Availability' && (
              <a
                href={projectHref}
                className="external-link"
                title="View all participating nurseries and the native species they carry"
              >
                <FontAwesomeIcon icon="external-link-alt" />
              </a>
            )}
          </h4>
          <span className="subheading" dangerouslySetInnerHTML={{ __html: this.props.subheading }} />
        </div>
        <div className="">
          <div className="">{this.props.children}</div>
        </div>
      </div>
    );
  }
}

SidebarAccordion.defaultProps = {
  title: '',
  style: { padding: '1em', backgroundColor: 'white', borderRadius: '0.5em', fontSize: 'initial' },
};

/**
 * Full sidebar
 */
class SideBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      displayFilters: null,
      isMobile: null,
    };

    /* this.onSortByClicked = this.props.onSortByClicked.bind(this);
		this.onFilterClicked = this.props.onFilterClicked.bind(this);*/
    this.getFilterCount = this.props.getFilterCount.bind(this);
  }
  componentDidMount() {
    let displayFilters = true;
    let isMobile = false;

    if (this.state.displayFilters == null && this.props.isMobile == true) {
      displayFilters = false;
      isMobile = true;
    }
    this.setState({
      displayFilters: displayFilters,
      isMobile: isMobile,
    });
  }
  /*
	componentWillReceiveProps(nextProps) {
  	let displayFilters = this.state.displayFilters;
		let isMobile = this.state.isMobile;
		
		if (isMobile != this.props.isMobile) {
			isMobile = this.props.isMobile;
			if (isMobile == true) {
				displayFilters = false;
			}
		}
		this.setState({
			displayFilters: displayFilters,
			isMobile: isMobile
		});
		
	}*/

  toggleFilters = () => {
    let newVal = true;
    if (this.state.displayFilters == true) {
      newVal = false;
    }
    this.setState({
      displayFilters: newVal,
    });
  };
  render() {
    let filterCount = this.getFilterCount();
    //console.log(this.props.characteristics);
    return (
      <div id="sidebar" className="m-1 rounded-border" style={this.props.style}>
        {/* Search */}

        <div className="filter-header" id="filter-section">
          <h3 className="filter-title">Search for Plants</h3>
          {filterCount > 0 && (
            <span className="filter-count">
              (<span className="filter-value">{filterCount.toString()}</span> selected)
            </span>
          )}
          {this.props.isMobile == true && this.state.displayFilters == true && (
            <span className="filter-toggle">
              <span className="fa-layers fa-fw">
                <FontAwesomeIcon className="back" icon="circle" onClick={() => this.toggleFilters()} />
                <FontAwesomeIcon className="front" icon="chevron-circle-up" onClick={() => this.toggleFilters()} />
              </span>
            </span>
          )}

          {this.props.isMobile == true && this.state.displayFilters == false && (
            <span className="filter-toggle">
              Open
              <span className="fa-layers fa-fw">
                <FontAwesomeIcon className="back" icon="circle" onClick={() => this.toggleFilters()} />
                <FontAwesomeIcon className="front" icon="chevron-circle-down" onClick={() => this.toggleFilters()} />
              </span>
            </span>
          )}
        </div>
        <p className="instructions">Start applying characteristics, and the matching plants will appear at right.</p>

        {this.state.displayFilters == true && (
          <div className="filter-tools">
            <SearchWidget
              placeholder="Search plants by name"
              clientRoot={this.props.clientRoot}
              isLoading={this.props.isLoading}
              textValue={this.props.searchText}
              onTextValueChanged={this.props.onSearchTextChanged}
              onSearch={this.props.onSearch}
              suggestionUrl={this.props.searchSuggestionUrl}
              clid={this.props.clid}
              dynclid={this.props.dynclid}
              onFilterClicked={this.onFilterClicked}
              onClearSearch={this.props.onClearSearch}
            />

            {this.props.characteristics &&
              Object.values(this.props.characteristics).map((firstLevel) => {
                let accordionKey = firstLevel.hid.toString() + firstLevel.headingname; //hids are duplicated, so use name also
                return (
                  <SidebarAccordion
                    key={accordionKey}
                    title={firstLevel.headingname}
                    subheading={firstLevel.subheading}
                    clientRoot={this.props.clientRoot}
                  >
                    {Object.values(firstLevel.characters).map((secondLevel) => {
                      return (
                        <FeatureSelector
                          key={secondLevel.cid}
                          cid={secondLevel.cid}
                          title={secondLevel.charname}
                          heading={firstLevel.headingname}
                          display={secondLevel.display}
                          units={secondLevel.units}
                          states={secondLevel.states}
                          attrs={this.props.filters.attrs}
                          sliders={this.props.useNewSlider ? undefined : this.props.filters.sliders}
                          ranges={this.props.useNewSlider ? this.props.filters.ranges : undefined}
                          clientRoot={this.props.clientRoot}
                          /*onChange={ (featureKey) => {
															this.props.onWholePlantChanged(plantFeature, featureKey)
														}}*/
                          onAttrClicked={this.props.onAttrClicked}
                          onGroupFilterClicked={this.props.onGroupFilterClicked}
                          onSliderChanged={this.props.useNewSlider ? undefined : this.props.onSliderChanged}
                          onRangeChanged={this.props.useNewSlider ? this.props.onRangeChanged : undefined}
                          collapsible={this.props.useNewSlider ? secondLevel.display !== 'slider' : undefined}
                          useNewSlider={this.props.useNewSlider}
                        />
                      );
                    })}
                  </SidebarAccordion>
                );
              })}

            {this.props.children}
          </div>
        )}
      </div>
    );
  }
}

SideBar.defaultProps = {
  searchText: '',
  characteristics: { hid: '', headingname: '', characters: {} },
  useNewSlider: false,
};

export default SideBar;
