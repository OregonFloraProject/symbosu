import React from "react";

import HelpButton from "../common/helpButton.jsx";
import {SearchWidget} from "../common/search.jsx";
import FeatureSelector from "../common/featureSelector.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import {faFileCsv, faFileWord, faPrint, faChevronDown, faChevronUp, faChevronCircleDown, faChevronCircleUp, faCircle } from '@fortawesome/free-solid-svg-icons'
library.add( faFileCsv, faFileWord, faPrint, faChevronDown, faChevronUp, faChevronCircleDown, faChevronCircleUp, faCircle );


/**
 * Sidebar header with title, subtitle, and help
 */
class SideBarHeading extends React.Component {
  render() {
    return (
      <div style={{color: "black"}}>
        <div className="mb-1 pt-2" style={{color: "inherit"}}>
          <h3 className="font-weight-bold d-inline">Search for plants</h3>
          }
        </div>
        <p className="container">
          Start applying characteristics, and the matching plants will appear at
          right.
        </p>
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
    if (this.props.disabled !== "true") {
      this.setState({isExpanded: !this.state.isExpanded});
    }
  }

  render() {
    let dropdownId = this.props.title;
    dropdownId = dropdownId.toLowerCase().replace(/[^a-z]/g, "").concat("-accordion");
    return (
      <div
        className={ "top-level" + (this.props.disabled === true ? " dropdown-disabled" : "") }
        id={ dropdownId }
         >
        <div className="row">
          <h4 className="">
            {this.props.title}
          </h4>
					<span className="subheading">{ this.props.subheading }</span>
        </div>
        <div className="">
          <div className="">
            { this.props.children }
          </div>
        </div>
      </div>
    );
  }
}

SidebarAccordion.defaultProps = {
  title: '',
  style: { padding: "1em", backgroundColor: "white", borderRadius: "0.5em", fontSize: "initial" },
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
			isMobile: isMobile
		});
	};
	
	componentWillReceiveProps(nextProps) {//necessary because React doesn't set isMobile in componentDidMount grr
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
		
	}
  
  toggleFilters = () => {
		let newVal = true;
		if (this.state.displayFilters == true) {
			newVal = false;
		} 
		this.setState({
			displayFilters: newVal
		});

  }  
  render() {  
  	
  	let filterCount = this.getFilterCount();
  	//console.log(this.props.characteristics);
    return (
      <div
        id="sidebar"
        className="m-1 rounded-border"
        style={ this.props.style }>


        {/* Search */}
        
				<div className="filter-header" id="filter-section">
					<h3 className="filter-title">Search for Plants</h3>
					{ filterCount > 0 &&
						<span className="filter-count">
						(<span className="filter-value">{filterCount.toString() }</span>  selected)
						</span>
					}
					{ this.props.isMobile == true && this.state.displayFilters == true &&
								<span className="filter-toggle">
									<span className="fa-layers fa-fw">
										<FontAwesomeIcon className="back" icon="circle" onClick={() => this.toggleFilters()} 
										/> 
										<FontAwesomeIcon className="front" icon="chevron-circle-up" onClick={() => this.toggleFilters()} 
										/>
									</span>
								</span>
					}
					
					{ this.props.isMobile == true && this.state.displayFilters == false &&
								<span className="filter-toggle">
									Open
									<span className="fa-layers fa-fw">
										<FontAwesomeIcon className="back" icon="circle" onClick={() => this.toggleFilters()} 
										/> 
										<FontAwesomeIcon className="front" icon="chevron-circle-down" onClick={() => this.toggleFilters()} 
										/>
									</span>
								</span>
					}
					
      	</div>
				<p className="instructions">
					Start applying characteristics, and the matching plants will appear at
					right.
				</p>


					{ this.state.displayFilters == true &&
					
						<div className="filter-tools" >
							<SearchWidget
								placeholder="Search plants by name"
								clientRoot={this.props.clientRoot}
								isLoading={ this.props.isLoading }
								textValue={ this.props.searchText }
								onTextValueChanged={ this.props.onSearchTextChanged }
								onSearch={ this.props.onSearch }
								suggestionUrl={ this.props.searchSuggestionUrl }
								clid={ this.props.clid }
								dynclid={ this.props.dynclid }
								onFilterClicked={ this.onFilterClicked }
								onClearSearch={ this.props.onClearSearch }
							/>

							{	this.props.characteristics &&
								Object.keys(this.props.characteristics).map((idx) => {
								let firstLevel = this.props.characteristics[idx];
									return (					
										<SidebarAccordion key={ firstLevel.hid } title={ firstLevel.headingname } subheading={ firstLevel.subheading }>
										{
											Object.keys(firstLevel.characters).map((idx2) => {
												let secondLevel = firstLevel.characters[idx2];
												/*if (secondLevel.display == 'slider') {
													console.log(secondLevel.states);
												}*/
												return (
													<FeatureSelector
														key={ secondLevel.cid }
														cid={ secondLevel.cid }
														title={ secondLevel.charname }
														display={ secondLevel.display }
														units={ secondLevel.units }
														states={ secondLevel.states }
														attrs={ this.props.filters.attrs }
														sliders={ this.props.filters.sliders }
														clientRoot={this.props.clientRoot}
														/*onChange={ (featureKey) => {
															this.props.onWholePlantChanged(plantFeature, featureKey)
														}}*/
														onAttrClicked={ this.props.onAttrClicked } 
														onSliderChanged={ this.props.onSliderChanged } 
													/>
												)
											})
										}

										</SidebarAccordion>
									)
								})
							}
							<SidebarAccordion title="Commercial Availability (Coming soon)" disabled={ true } />
							
							<div className="p-3 metro">
								<p><a href="https://www.oregonmetro.gov/" target="_blank"><img 
											src={ this.props.clientRoot + "/images/metro_logo_t.png"} 
										/></a>Support for the Grow Natives section of the site provided by <a href="https://www.oregonmetro.gov/" target="_blank">Metro</a>
								 &mdash; protecting clean air, water and habitat in greater Portland.</p>

								<p>See contributing partners to OregonFlora <a href={ this.props.clientRoot + "/pages/project-participants.php"}>here</a>.</p>
							</div>
						</div>
					}
      </div>
    );
  }
}


SideBar.defaultProps = {
  searchText: '',
  characteristics: {"hid":'',"headingname":'',"characters":{}},
};

export default SideBar;
