"use strict";

import React from "react";
import ReactDOM from "react-dom";

import InfographicDropdown from "./infographicDropdown.jsx";
import SideBar from "./sidebar.jsx";
import {GardenSearchResult, GardenSearchContainer} from "../common/searchResults.jsx";
import CannedSearchContainer from "./cannedSearches.jsx";
import ViewOpts from "../common/viewOpts.jsx";
import httpGet from "../common/httpGet.js";
import {IconButton} from "../common/iconButton.jsx";
import {addUrlQueryParam, getUrlQueryParams} from "../common/queryParams.js";
import {getCommonNameStr, getGardenTaxaPage} from "../common/taxaUtils";
import Loading from "../common/loading.jsx";
import FilterModal from "../common/filterModal.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faChevronUp } from '@fortawesome/free-solid-svg-icons'
library.add( faChevronUp)

const MOBILE_BREAKPOINT = 576;

class GardenPageApp extends React.Component {
  constructor(props) {
    super(props);
    const queryParams = getUrlQueryParams(window.location.search);

    this.state = {
      isLoading: true,
      isSearching: false,
      isMobile: false,
      showFilterModal: false,
      searchInit: false,
      clid: -1,
      pid: -1,
      projName: '',
      currentTids: [],
      filters: {
        searchText: ("search" in queryParams ? queryParams["search"] : ViewOpts.DEFAULT_SEARCH_TEXT),
        attrs: {},
        sliders: {},
        checklist: {"clid":-1,"name":''}
      },
      searchResults: {"familySort":{},"taxonSort":[]},
      characteristics: {},
      cannedSearches: [],
      sortBy: ("sortBy" in queryParams ? queryParams["sortBy"] : "vernacularName"),
      viewType: ("viewType" in queryParams ? queryParams["viewType"] : "grid"),
      apiUrl: '',
      slideshowCount: 0 
    };

    
    this.getPid = this.getPid.bind(this);
    this.getClid = this.getClid.bind(this);
    /*this.getCannedSearches = this.getCannedSearches.bind(this);
    this.getCharacteristics = this.getCharacteristics.bind(this);*/

    this.onSearchTextChanged = this.onSearchTextChanged.bind(this);
    this.onSearch = this.onSearch.bind(this);
    this.onSearchResults = this.onSearchResults.bind(this);

    this.onSortByChanged = this.onSortByChanged.bind(this);
    this.sortByTaxon = this.sortByTaxon.bind(this);
    this.onViewTypeChanged = this.onViewTypeChanged.bind(this);
    this.onFilterRemoved = this.onFilterRemoved.bind(this);
    this.onCannedFilter = this.onCannedFilter.bind(this);
    this.clearFilters = this.clearFilters.bind(this);
    this.updateViewport = this.updateViewport.bind(this);
      
    this.onAttrChanged = this.onAttrChanged.bind(this);
    this.onGroupFilterClicked = this.onGroupFilterClicked.bind(this);
    this.resetSlider = this.resetSlider.bind(this);
    this.resetCanned = this.resetCanned.bind(this);
    this.getCannedByClid = this.getCannedByClid.bind(this);
    this.onSliderChanged = this.onSliderChanged.bind(this);
    this.sortResults = this.sortResults.bind(this);
    this.clearTextSearch = this.clearTextSearch.bind(this);
    this.getStatesByCid = this.getStatesByCid.bind(this);
    this.mobileScrollToResults = this.mobileScrollToResults.bind(this);
    this.mobileScrollToFilters = this.mobileScrollToFilters.bind(this);
    this.getFilterCount = this.getFilterCount.bind(this);
    this.setFilterModal = this.setFilterModal.bind(this);
    this.doConfirm = this.doConfirm.bind(this);
    this.setDefaultTids = this.setDefaultTids.bind(this);
    
  }
  getClid() {
    return parseInt(this.props.clid);
  }
  getPid() {
    return parseInt(this.props.pid);
  }
  componentDidMount() {
     
    let apiUrl = `${this.props.clientRoot}/garden/rpc/api.php`;
    let url = apiUrl;

    let gardenParams = new URLSearchParams();
    if (this.getClid() > -1) {
	    gardenParams.append("clid",this.getClid());
	  }
	  if (this.getPid() > -1) {
	    gardenParams.append("pid",this.getPid());
	  }

  	url = url + '?' + gardenParams.toString();
  	///garden/rpc/api.php?clid=54&pid=3
		//console.log(url);

		const cannedSearches = new Promise((resolve, reject) => {
			let cannedURL = `${this.props.clientRoot}/garden/rpc/api.php?canned=true`;
			httpGet(cannedURL)
				.then((res) => {
					resolve(JSON.parse(res));
				});
    });
		
		/*const characteristics = new Promise((resolve, reject) => {
			let charURL = `${this.props.clientRoot}/garden/rpc/api.php?chars=true`;
			httpGet(charURL)
				.then((res) => {
					resolve(JSON.parse(res));
				})
   	});*/
   	const garden = new Promise((resolve, reject) => {
			httpGet(url)
				.then((res) => {
					resolve(JSON.parse(res));
				})
   	});

		Promise.all([
			cannedSearches,
      garden
    ]).then((cres) => {
			let res = cres[1];
			let taxa = '';
			let tids = [];
			if (res && res.taxa) {
				taxa = this.sortResults(res.taxa);
				tids = res.tids;//unordered
			}
			let isMobile = false;
			if (window.innerWidth < MOBILE_BREAKPOINT) {
				isMobile = true;
			}
			this.setState({
				clid: this.getClid(),
				pid: this.getPid(),
				projName: res.projName,
				searchResults: taxa,//always the full garden checklist
				isMobile: isMobile,
				apiUrl: apiUrl,
				currentTids: tids,
				cannedSearches: cres[0],
				characteristics: res.characteristics
			});
			const pageTitle = document.getElementsByTagName("title")[0];
			pageTitle.innerHTML = `${pageTitle.innerHTML} ${res.title}`;
		})
		.catch((err) => {
			//window.location = "/";
			console.error(err);
		})
		.finally(() => {
			this.updateViewport();
			this.setState({ isLoading: false });
		});
		window.addEventListener('resize', this.updateViewport);
	
  }

	updateViewport() {
		let newSlideshowCount = 4;
		if (window.innerWidth < 1200) {
			newSlideshowCount = 3;
		}
		if (window.innerWidth < 992) {
			newSlideshowCount = 2;
		}
		this.setState({ slideshowCount: newSlideshowCount });
	}

	clearTextSearch() {
		this.setState({
			filters: Object.assign({}, this.state.filters, {searchText : ViewOpts.DEFAULT_SEARCH_TEXT})
		},function() {
			this.catchQuery();
		});
		
	} 

  onFilterRemoved(key,text) {
    // TODO: This is clunky
    switch (key) {
      case "searchText":
        this.clearTextSearch();
        break;

      default://characteristics/attr numbers
				if (this.state.filters.attrs[key]) {
	      	this.onAttrChanged(key,text,'off');
	      }
				if (this.state.filters.sliders[key]) {
	      	this.resetSlider(key);
	      }
	      if (this.state.filters.checklist['clid'] == key) {
	      	this.resetCanned();
	      }
        break;
    }
  }

  onSearchTextChanged(e) {
    this.setState({filters: Object.assign({}, this.state.filters, { searchText: e.target.value })});
  }
  // On search start
  onSearch(searchObj) {
    this.setState({
      //searchText: searchObj.text,
      filters: Object.assign({}, this.state.filters, { searchText: searchObj.text })
    },function() {
			this.catchQuery();
    });
  }
  catchQuery() {

  	let doConfirm = false;
  	if (this.state.isMobile && this.getFilterCount() > 0) {
  		doConfirm = true;
  	}
  	if (doConfirm) {
	    this.setFilterModal(true);
	  }else{
	  	this.doQuery();
	  }
  }
  doQuery() {
    this.setState({
      //isLoading: true,
      isSearching: true,
    });
    if (this.getFilterCount() > 0) {
			let url = this.state.apiUrl;
			let identParams = new URLSearchParams();
			if (this.state.filters.checklist && this.state.filters.checklist['clid'] > -1) {
				identParams.append("clid",this.state.filters.checklist['clid']);
			}else if (this.getClid() > -1) {
				identParams.append("clid",this.getClid());
			}
			if (this.getPid() > -1) {
				identParams.append("pid",this.getPid());
			}
			if (this.state.filters.searchText) {
				identParams.append("search",this.state.filters.searchText);
			}
			Object.keys(this.state.filters.attrs).map((idx) => {
				identParams.append("attr[]",idx);
			});
			/* compare slider values vs characteristics and add to attr list;
					adding each state as its own attr[] value makes the URL unacceptably long,
					so we create a new range[] param for purposes of building the URL;
					the API will convert this back into attrs for the DB calls
			 */

			Object.entries(this.state.filters.sliders).map((item) => {
				let cid = item[0];
				let slider = item[1];
				let states = this.getStatesByCid(cid);
				let min = states[0].cs;
				let max = (states.length > 1? states[1].cs : states[0].cs);
				Object.keys(states).map((key) => {
					let stateNum = Number(states[key].numval);
					let stateCs = Number(states[key].cs);
					if (stateNum == slider.range[0]) {
						min = stateCs;			
					}
					if (stateNum == slider.range[1]) {
						max = stateCs;
					}	
				})
				identParams.append("range[]",cid + '-n-' + min);
				identParams.append("range[]",cid + '-x-' + max);
			});	
		
			url = url + '?' + identParams.toString();
			//console.log(decodeURIComponent(url));
			httpGet(url)
				.then((res) => {
					let jres = JSON.parse(res);
					this.onSearchResults(jres.tids);
				})
				.catch((err) => {
					console.error(err);
				})
				.finally(() => {
					this.setState({ isSearching: false, searchInit: true });
					this.mobileScrollToResults();
				});
			}else{//reset
				this.setDefaultTids();
				this.setState({ isSearching: false });
				this.mobileScrollToResults();
			}
  }
  mobileScrollToResults() {
    if (this.state.isMobile && this.getFilterCount() > 0) {
      let section = document.getElementById("results-section");      
			let yOffset = 60;
			document.getElementById("results-section").scrollIntoView();
			const newY = section.getBoundingClientRect().top + window.pageYOffset - yOffset;
			window.scrollTo({top: newY, behavior: 'smooth'});
		}
  }
  mobileScrollToFilters() {
		let section = document.getElementById("filter-section");      
		let yOffset = 60;
		document.getElementById("filter-section").scrollIntoView();
		const newY = section.getBoundingClientRect().top + window.pageYOffset - yOffset;
		window.scrollTo({top: newY, behavior: 'smooth'});
  }
  getFilterCount() {
  	let filterCount = 0;
  	filterCount += Object.keys(this.state.filters.attrs).length;
  	filterCount += Object.keys(this.state.filters.sliders).length;
  	filterCount += (this.state.filters.checklist['clid'] > -1);
  	filterCount += (this.state.filters.searchText != ViewOpts.DEFAULT_SEARCH_TEXT);
  	return filterCount;
  }
  setFilterModal(val) {
  	let newVal = (val == true ? true : false);
    this.setState({ showFilterModal: newVal });
  }
  doConfirm() {
  	this.setFilterModal(false);
  	this.doQuery();
  }
  	
	setDefaultTids() {
		let tids = Object.entries(this.state.searchResults.taxonSort).map(([idx,taxon]) => {
  		return taxon['tid'];
  	});
  	this.onSearchResults(tids);
	}
  // On search end
  onSearchResults(tids) {
    this.setState({ currentTids: tids });
  }

  onAttrResults(chars) {

  	let newAttrs = {};
  	let newSliders = {};

  	let newCids = [];
  	Object.entries(chars).map(([key, group]) => {
  		Object.entries(group.characters).map(([ckey,gchar]) => {
  			newCids.push(gchar.cid);
  		});
  	});
  	Object.entries(this.state.filters.attrs).map(([cid,attr]) => {
  		if (newCids.indexOf(Number(cid)) != -1) {
  			newAttrs[cid] = attr;
  		}
  	});
  	Object.entries(this.state.filters.sliders).map(([cid,slider]) => {
  		if (newCids.indexOf(Number(cid)) != -1) {
  			newSliders[cid] = slider;
  		}
  	});
  	this.setState({
      filters: Object.assign({}, this.state.filters, { attrs: newAttrs }),
      filters: Object.assign({}, this.state.filters, { sliders: newSliders }),
      characteristics: chars
    });
  }
  sortByTaxon(taxa) {
		let taxonSort = {};
		switch (this.state.sortBy) {
			case 'sciName':
				taxonSort = taxa.sort((a, b) => { return a["sciname"] > b["sciname"] ? 1 : -1 });
				break;
			case 'vernacularName':
				taxonSort = taxa.sort((a, b) => { return getCommonNameStr(a).toLowerCase() > getCommonNameStr(b).toLowerCase() ? 1 : -1 });
				break;
		}
  	return taxonSort;
  }
  sortResults(results) {//should receive taxa from API
  	let newResults = {};
		let taxonSort = this.sortByTaxon(results);
		let familySort = results;

    newResults = {"familySort": familySort, "taxonSort": taxonSort};
  	return newResults;
  }
  
  onSortByChanged(type) {
    let taxonSort;
    let familySort = this.state.searchResults.familySort;
		this.setState({
      sortBy: (type == 'sciName'? 'sciName': 'vernacularName')
    },function() {
    	taxonSort = this.sortByTaxon(this.state.searchResults.taxonSort);
			this.setState({
				searchResults: {"familySort": familySort, "taxonSort": taxonSort}
			});
    });
  }
  getStatesByCid(cid) {
  	let results = {};
  	Object.entries(this.state.characteristics).map(([key, group]) => {
			Object.entries(group.characters).map(([ckey, character]) => {
				if (character.cid == cid) {
					results = character.states;
				}
			});  	
  	});
  	return results;
  }
  onAttrChanged(featureKey, featureName, featureVal) {
  /* 710-1, simple, on */
  	let filters = this.state.filters;

  	if (featureVal == 'off') {
  		delete filters.attrs[featureKey];
  	}else{
  		filters.attrs[featureKey] = featureName;
  	}

    this.setState({
      filters: Object.assign({}, this.state.filters, { attrs: filters.attrs })
    },function() {
    	this.catchQuery();
    });
    
  }
  onGroupFilterClicked(children) {
  	//console.log(children);
  	let nurseries = this.state.characteristics[5].characters[1];//as hardcoded in garden/rpc/api.php
  	nurseries.states.map((attr) => {
  		let val = 'off';
			if (children.indexOf(attr.cs) != -1) {
				val = 'on';
			}
			this.onAttrChanged(attr.cid + '-' + attr.cs,attr.charstatename,val);
  	});
  }

  onViewTypeChanged(type) {
    this.setState({ viewType: type });

    let newType;
    if (type === "list") {
      newType = type;
    } else {
      newType = '';
    }
    let newQueryStr = addUrlQueryParam("viewType", newType);
    /*window.history.replaceState({ query: newQueryStr }, '', window.location.pathname + newQueryStr);*/
  }

  onCannedFilter(checklistItem) {/*accepts either object or clid*/
  	let checklist = null;
  	if (typeof checklistItem === 'object') {
  		checklist = checklistItem;
  	}else{
  		checklist = this.getCannedByClid(checklistItem);
  	}

  	if (checklist !== null) {
			this.setState({
				filters: Object.assign({}, this.state.filters, {checklist : checklist})
			},function() {
				this.catchQuery();
			});
		}
  }
  resetCanned() {
		this.setState({
			filters: Object.assign({}, this.state.filters, {checklist : {'clid':-1,'name':''}})
		},function() {
			this.catchQuery();
		});
  }
  getCannedByClid(clid) {
  	let ret = null;
  	this.state.cannedSearches.map((canned) => {
  		if (canned.clid == clid) {
  			ret = canned;
  		}
  	});
  	return ret;
  }
  
  resetSlider(cid) {
  	let filters = this.state.filters;
  	delete filters.sliders[cid];
    this.setState({
      filters: Object.assign({}, this.state.filters, { sliders: filters.sliders })
    },function() {
    	this.catchQuery();
    });
  }
  
  onSliderChanged(sliderState, range) {

		let min = sliderState.states[0].numval;
		let max = sliderState.states[sliderState.states.length - 1].numval;
  	let filters = this.state.filters;
  	
  	if (range[0] == min && range[1] == max) {
  		delete filters.sliders[sliderState.cid];
  	}else{
  		filters.sliders[sliderState.cid] = { range: sliderState.range, label: sliderState.label, units: sliderState.units, step: sliderState.step, originalStates: sliderState.states };
  	}
  
    this.setState({
      filters: Object.assign({}, this.state.filters, { sliders: filters.sliders })
    },function() {
    	this.catchQuery();
    });
  }
	clearFilters() {
		let filters = {
			searchText: ViewOpts.DEFAULT_SEARCH_TEXT,
			attrs: {},
			sliders: {},
			checklist: {}
		};
    this.setState({ filters: filters },function() {
    	this.catchQuery();
    });
	}
  render() {
    const checkListMap = {};
    for (let i in this.state.cannedSearches) {
      let search = this.state.cannedSearches[i];
      checkListMap[search.clid] = search.name;
    }
    
		let suggestionUrl = `${this.props.clientRoot}/garden/rpc/autofillsearch.php`;
    /*const pageTitle = document.getElementsByTagName("title")[0];
    pageTitle.innerHTML = `${pageTitle.innerHTML} Gardening with Natives`;
    */
    //console.log(this.state.searchResults);
    
    return (
      <div className="garden-wrapper">
				<Loading 
					clientRoot={ this.props.clientRoot }
					isLoading={ this.state.isLoading }
				/>
        <InfographicDropdown 
        	clientRoot={this.props.clientRoot}
				/>
   			<div className="container mx-auto py-4 pl-3 pr-4">
          <div className="row">
            <div className="col-md-4">
              <SideBar
								clid={ this.state.clid }
                style={{ background: "#DFEFD3" }}
                isLoading={ this.state.isLoading }
								clientRoot={this.props.clientRoot}
								characteristics={ this.state.characteristics }
								searchText={ this.state.filters.searchText }
								searchSuggestionUrl={ suggestionUrl }
                onSearch={ this.onSearch }
                onSearchTextChanged={ this.onSearchTextChanged }
                searchName={ this.state.searchName }
								viewType={ this.state.viewType }
								sortBy={ this.state.sortBy }
								onSortByClicked={ this.onSortByChanged }
								onAttrClicked={ this.onAttrChanged }
								onGroupFilterClicked={ this.onGroupFilterClicked }
								onSliderChanged={ this.onSliderChanged }
								onFilterClicked={ this.onFilterRemoved }
								onClearSearch={ this.clearTextSearch }
								filters={ this.state.filters }
								getFilterCount={ this.getFilterCount } 
								isMobile={ this.state.isMobile }
              />
            </div>
            <div className="col-md-8">
              <div className="row">
                <div className="col">
                  <CannedSearchContainer
                    searches={ this.state.cannedSearches }
                    onFilter={ this.onCannedFilter }
										clientRoot={this.props.clientRoot}
										checklistId={this.state.filters.checklist['clid']}
										slideshowCount= { this.state.slideshowCount } 
                  />
                </div>
              </div>
              <div className="">
                <div className="" id="results-section">
           				<div className="row">
										<div className="col">
											{ this.state.isMobile == true && this.state.searchInit == true &&
												<div className="mobile-to-filters" onClick={() => this.mobileScrollToFilters()}>
													<span>Apply More Filters</span>
													<FontAwesomeIcon icon="chevron-up" />
												</div>
											}
										</div>
									</div>
									<div id="view-opts" className="row">
										<div className="col-7 button-section">
											<h3 className="font-weight-bold">Your search results:</h3>
											<div className="d-flex flex-row flex-wrap">
	 
											<ViewOpts
												onReset={ this.clearFilters }
												onFilterClicked={ this.onFilterRemoved }
												clientRoot={this.props.clientRoot}
												filters={
													Object.keys(this.state.filters).map((filterKey) => {
														return { key: filterKey, val: this.state.filters[filterKey] }
													})
												}
												getStatesByCid={ this.getStatesByCid } 
												defaultMessage={ 'No filters applied, so showing all native plants' }
											/>

											</div>
										</div>
										<div className="col-5 col pt-2 container settings px-2">
											<div className="col-lg-6 col-sm-12 row mx-0 mb-2 px-2 settings-section">
												<div className="col-5 text-right px-2 pt-1 toggle-labels">
													View as:
												</div>
												<div className="col-7 p-0">
			
														<IconButton
															title="Grid"
															icon={`${this.props.clientRoot}/images/garden/gridViewIcon.png`}
															onClick={() => {
																this.onViewTypeChanged("grid")
															}}
															isSelected={this.state.viewType === "grid"}
														/>
														<IconButton
															title="List"
															icon={`${this.props.clientRoot}/images/garden/listViewIcon.png`}
															onClick={() => {
																this.onViewTypeChanged("list")
															}}
															isSelected={this.state.viewType === "list"}
														/>
	
						
												</div>
						
											</div>
					
											<div className="col-lg-6 col-sm-12 row mx-0 mb-2 px-0 settings-section">
												<div className="col-5 text-right px-2 pt-1 toggle-labels">
													Sort by name:  
												</div>
												<div className="col-7 p-0">      	
		
														<IconButton
															title="Common"
															onClick={() => {
																this.onSortByChanged("vernacularName")
															}}
															isSelected={this.state.sortBy === "vernacularName"}
														/>
														<IconButton
															title="Scientific"
															onClick={() => {
																this.onSortByChanged("sciName")
															}}
															isSelected={this.state.sortBy === "sciName"}
														/>
						
												</div>
											</div>
										</div>
									</div>
								          
                  { this.state.searchResults.taxonSort.length > 0 ?
										<GardenSearchContainer
											searchResults={ this.state.searchResults }
											viewType={ this.state.viewType }
											sortBy={ this.state.sortBy }
											clientRoot={ this.props.clientRoot }
											isSearching={this.state.isSearching}
											currentTids={this.state.currentTids}
										/>
									:
									<p className="no-results">Your search term(s) didn’t produce any results.
										<span className="suggest">Try deleting a filter or Clearing All to try different terms?</span>
									</p> 
									}
										
      
                  <div className="go-top">
                        <a href="#results-section" className="toptext">
                            TOP<br />
                            <FontAwesomeIcon icon="chevron-up" size="2x"/>
                        </a>
                </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
				{ this.state.isMobile == true &&
					<FilterModal 
						show={ this.state.showFilterModal }
					>
						<div className="modal-filter-content">
							<div className="filter-count">{ this.getFilterCount() } filter{ this.getFilterCount() > 1? 's':'' } chosen</div>
							<div 
								className="btn btn-primary current-button" 
								role="button"
								onClick={() => this.doConfirm()}
							>Filter and see results</div>
						</div>
					</FilterModal>
				}
        
      </div>
    );
  }
}

const headerContainer = document.getElementById("react-header");
const dataProps = JSON.parse(headerContainer.getAttribute("data-props"));
const domContainer = document.getElementById("react-garden");
ReactDOM.render(<GardenPageApp 
									clientRoot={ dataProps["clientRoot"] }
									clid={ 54 }
									pid={ 3 }
								/>, domContainer);
