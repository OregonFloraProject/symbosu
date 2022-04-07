"use strict";
import React from "react";
import ReactDOM from "react-dom";

import SideBar from "./sidebar.jsx";
import ViewOpts from "./viewOpts.jsx";
import httpGet from "../common/httpGet.js";
import {ExploreSearchContainer, SearchResultContainer} from "../common/searchResults.jsx";
import {addUrlQueryParam, getUrlQueryParams} from "../common/queryParams.js";
import {getCommonNameStr, getTaxaPage, getIdentifyPage} from "../common/taxaUtils";
import PageHeader from "../common/pageHeader.jsx";
import Loading from "../common/loading.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import {faChevronDown, faChevronUp, faListUl, faSearchPlus } from '@fortawesome/free-solid-svg-icons'
library.add( faChevronDown, faChevronUp, faListUl, faSearchPlus );

class ExploreApp extends React.Component {
  constructor(props) {
    super(props);
    const queryParams = getUrlQueryParams(window.location.search);
    
    
    // TODO: searchText is both a core state value and a state.filters value; How can we make the filtering system more efficient?
    this.state = {
      isLoading: true,
      isSearching: false,
      currentTids: [],
      clid: -1,
      pid: -1,
      dynclid: -1,
      projName: null,
      title: '',
      authors: '',
      abstract: '',
      displayAbstract: 'default',
      googleMapUrl: '',
      exportUrl: '',
      exportUrlCsv: '',
      exportUrlWord: '',
      //taxa: [],
      filters: {
        searchText: ("search" in queryParams ? queryParams["search"] : ViewOpts.DEFAULT_SEARCH_TEXT),
        //checklistId: ("clid" in queryParams ? parseInt(queryParams["clid"]) : ViewOpts.DEFAULT_CLID),
      },
      searchText: ("search" in queryParams ? queryParams["search"] : ViewOpts.DEFAULT_SEARCH_TEXT),
      searchResults: {"familySort":{},"taxonSort":[]},
      searchName: ("searchName" in queryParams ? queryParams["searchName"] : "sciname"),
      searchSynonyms: ("searchSynonyms" in queryParams ? queryParams["searchSynonyms"] : 'on'),
      sortBy: ("sortBy" in queryParams ? queryParams["sortBy"] : "family"),
      viewType: ("viewType" in queryParams ? queryParams["viewType"] : "list"),
      showTaxaDetail: (this.props.showVouchers && this.props.showVouchers == 1 ? 'on' : 'off'),
      totals: {
      	families: 0,
      	genera: 0,
      	species: 0,
      	taxa: 0
      },
      fixedTotals: {//unchanged by filtering
      	families: 0,
      	genera: 0,
      	species: 0,
      	taxa: 0
      }
    };
    this.getPid = this.getPid.bind(this);
    this.getClid = this.getClid.bind(this);
    this.getDynclid = this.getDynclid.bind(this);

    this.onSearchTextChanged = this.onSearchTextChanged.bind(this);
    this.onSearch = this.onSearch.bind(this);
    this.onSearchResults = this.onSearchResults.bind(this);
    this.onSearchNameChanged = this.onSearchNameChanged.bind(this);
    this.onSearchSynonymsChanged = this.onSearchSynonymsChanged.bind(this);
    this.onSortByChanged = this.onSortByChanged.bind(this);
    this.onViewTypeChanged = this.onViewTypeChanged.bind(this);
    this.onTaxaDetailChanged = this.onTaxaDetailChanged.bind(this);
    this.onFilterRemoved = this.onFilterRemoved.bind(this);
    this.sortResults = this.sortResults.bind(this);
    this.clearTextSearch = this.clearTextSearch.bind(this);
    
  }

  getClid() {
    return parseInt(this.props.clid);
  }
  getPid() {
    return parseInt(this.props.pid);
  }
  getDynclid() {
    return parseInt(this.props.dynclid);
  }
  toggleDisplay = () => {
		let newVal = 'default';
		if (this.state.displayAbstract == 'default') {
			newVal = 'expanded';
		} 
		this.setState({
			displayAbstract: newVal
		});

  }

  componentDidMount() {
    // Load search results
    //this.onSearch({ text: this.state.searchText });
    let exportUrl = `${this.props.clientRoot}/ident/rpc/api.php`;//use identify api for export
    let url = `${this.props.clientRoot}/checklists/rpc/api.php`;//?clid=${this.props.clid}&pid=${this.props.pid}`;
    let identParams = new URLSearchParams();
    if (this.getClid() > -1) {
	    identParams.append("clid",this.getClid());
	  }
	  if (this.getPid() > -1) {
	    identParams.append("pid",this.getPid());
	  }
    if (this.getDynclid() > -1) {
	    identParams.append("dynclid",this.getDynclid());
	  }
  	url = url + '?' + identParams.toString();
    console.log(url);
    //console.log(this.state.showTaxaDetail);
    httpGet(url)
			.then((res) => {
				// /checklists/rpc/api.php?clid=3
				res = JSON.parse(res);
				
				let googleMapUrl = '';			
				let host = window.location.host;
						
				if (res.lat !== '' && res.lng !== '') {
					
					googleMapUrl += 'https://maps.google.com/maps/api/staticmap';
					let mapParams = new URLSearchParams();
					let markerUrl = 'http://' + host + this.props.clientRoot + '/images/icons/map_markers/single.png'; 
					mapParams.append("key",this.props.googleMapKey);
					mapParams.append("maptype",'terrain');
					mapParams.append("size",'220x220');
					mapParams.append("zoom",6);
					mapParams.append("markers",'icon:' + markerUrl + '|anchor:center|' + res.lat + ',' + res.lng);
		
					googleMapUrl += '?' + mapParams.toString();
				}
				
				let viewType = 'list';
				if (this.getPid() == 3) {
					viewType = 'grid';
				}
				let taxa  = [];
				let tids = [];
				if (res && res.taxa) {
					taxa = this.sortResults(res.taxa);
					tids = res.tids;//unordered
				}
				
				this.setState({
					clid: this.getClid(),
					pid: this.getPid(),
					dynclid: this.getDynclid(),
					projName: res.projName,
					title: res.title,
					authors: res.authors,
					abstract: res.abstract,
					viewType: viewType,
					//taxa: res.taxa,
					searchResults: taxa,
					currentTids: tids,
					totals: res.totals,
					fixedTotals: res.totals,
					googleMapUrl: googleMapUrl,
					//exportUrlCsv: `${this.props.clientRoot}/checklists/rpc/export.php?clid=` + this.getClid() + `&pid=` + this.getPid(),
					//exportUrlWord: `${this.props.clientRoot}/checklists/defaultchecklistexport.php?cl=` + this.getClid() + `&pid=` + this.getPid()
					exportUrl: exportUrl,
					exportUrlCsv: exportUrl + `?export=csv&clid=` + this.getClid() + `&pid=` + this.getPid() + `&dynclid=` + this.getDynclid(),
					exportUrlWord: exportUrl + `?export=word&clid=` + this.getClid() + `&pid=` + this.getPid() + `&dynclid=` + this.getDynclid()
				});
				const pageTitle = document.getElementsByTagName("title")[0];
				pageTitle.innerHTML = `${pageTitle.innerHTML} ${res.title}`;
			})
			.catch((err) => {
				//window.location = "/";
				console.error(err);
			})
      .finally(() => {
        this.setState({ isLoading: false });
      });
  }
  updateExportUrls() {
  	this.updateExportUrlCsv();
    this.updateExportUrlWord();
  }
  updateExportUrlCsv() {

  	//let url = `${this.props.clientRoot}/checklists/rpc/export.php`;
  	let url = this.state.exportUrl;
  	let exportParams = new URLSearchParams();
  	
		exportParams.append("export",'csv');
		exportParams.append("clid",this.getClid());
		exportParams.append("pid",this.getPid());
		exportParams.append("dynclid",this.getDynclid());
		
		/*TBD
		if (this.state.searchName) {
			exportParams.append("name",this.state.searchName);
		}
		if (this.state.searchSynonyms) {
			exportParams.append("synonyms",this.state.searchSynonyms);
		}
		if (this.state.filters.searchText) {
			exportParams.append("search",this.state.filters.searchText);
		}*/
  	url += '?' + exportParams.toString();
  	
	  this.setState({
      exportUrlCsv: url,
    });
  }
  updateExportUrlWord() {
  	//let url = `${this.props.clientRoot}/checklists/defaultchecklistexport.php`;
  	let url = this.state.exportUrl;
  	let exportParams = new URLSearchParams();
  	
		exportParams.append("export",'word');
		exportParams.append("clid",this.getClid());
		exportParams.append("pid",this.getPid());
		exportParams.append("dynclid",this.getDynclid());
		exportParams.append("showcommon",1);
		if (this.state.filters.searchText) {
			exportParams.append("taxonfilter",this.state.filters.searchText);
		}
		/*TBD
  	//params here match /checklists/defaultchecklistexport.php - this is old - ap
		if (this.state.searchName === 'commonname') {
			exportParams.append("searchcommon",1);
		}
		if (this.state.searchSynonyms) {
			exportParams.append("searchsynonyms",this.state.searchSynonyms);
		}
		if (this.state.sortBy === 'taxon') {
			exportParams.append("showalphataxa",1);
		}
		if (this.state.viewType === 'grid') {
			exportParams.append("showimages",1);
		}
		if (this.state.showTaxaDetail === 'on') {
			exportParams.append("showauthors",1);
			exportParams.append("showvouchers",1);
		}*/
		
  	url += '?' + exportParams.toString();
	  this.setState({
      exportUrlWord: url,
    });
  }
	clearTextSearch() {
		this.onFilterRemoved("searchText");
	}
	
  onFilterRemoved(key) {
    // TODO: This is clunky
    //console.log(key);
    switch (key) {
      case "searchText":
        this.setState({
          searchText: ViewOpts.DEFAULT_SEARCH_TEXT },
          () => this.onSearch({ text: ViewOpts.DEFAULT_SEARCH_TEXT, value: -1 })
        );
        this.updateExportUrls();
        break;
      default:
        break;
    }
  }

  onSearchTextChanged(e) {
    this.setState({ searchText: e.target.value });
  }

  // On search start
  onSearch(searchObj) {

    this.setState({
      isSearching: true,
      searchText: searchObj.text,
      filters: Object.assign({}, this.state.filters, { searchText: searchObj.text })
    },function() {
			if (searchObj.text.length) {    	
				let url = `${this.props.clientRoot}/checklists/rpc/api.php`;
				let identParams = new URLSearchParams();
				if (searchObj.text.length) {	identParams.append("search",searchObj.text);}
				if (this.state.searchName.length) {	identParams.append("name",this.state.searchName);}
				if (this.getDynclid() > -1) {	identParams.append("dynclid",this.state.dynclid);}
				if (this.getClid() > -1) {	identParams.append("clid",this.state.clid);	}
				if (this.getPid() > -1) {	identParams.append("pid",this.state.pid);}
				if (this.state.searchSynonyms.length) {	identParams.append("synonyms",this.state.searchSynonyms);}

				url = url + '?' + identParams.toString();

				httpGet(url)
					.then((res) => {
						let jres = JSON.parse(res);
						this.onSearchResults(jres.tids);
						this.updateTotals(jres.totals);
						this.updateExportUrls();
					})
					.catch((err) => {
						console.error(err);
					})
					.finally(() => {
						this.setState({ isSearching: false });
					});
				}else{//reset		
					this.setDefaultTids();
					this.setState({ isSearching: false });
					this.updateTotals(this.state.fixedTotals);
				}
    });    
  }
  
	updateTotals(totals) {
	  this.setState({
      totals: totals,
    });
	}

  // On search end
 /* onSearchResults(results) {
    let newResults;
    newResults = this.sortResults(results);
    this.setState({ searchResults: newResults },function() {
			this.updateExportUrls();
		});
  }*/
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
  
  sortResults(results) {//should receive taxa from API
  	let newResults = {};
  	//console.log(results);

		let familySort = {};
		let tmp = {};
		Object.entries(results).map(([key, result]) => {
			if (!tmp[result.family]) {
				tmp[result.family] = [];
			}
			tmp[result.family].push(result);
		})
		//sort family alpha
		Object.keys(tmp).sort().forEach(function(key) {
			familySort[key] = tmp[key];
		});

		let taxonSort = results;
    
    newResults = {"familySort": familySort, "taxonSort": taxonSort};
    
  	return newResults;
  }

  onSortByChanged(sortBy) {
    this.setState({ sortBy: sortBy },function() {
    	this.updateExportUrls();
    });
  }
  onSearchNameChanged(name) {
    this.setState({ searchName: name },function() {
    	this.updateExportUrls();
    });
/*
    let newName;
    if (name === "commonname") {
      newName = name;
    } else {
      newName = 'sciname';
    }*/
    //let newQueryStr = addUrlQueryParam("searchName", newName);
    /*window.history.replaceState({ query: newQueryStr }, '', window.location.pathname + newQueryStr);*/
  }
  onSearchSynonymsChanged(synonyms) {
    this.setState({ searchSynonyms: synonyms },function() {
    	this.updateExportUrls();
    });

/*    let newSynonyms;
    if (synonyms === 'off') {
      newSynonyms = synonyms;
    } else {
      newSynonyms = 'on';
    }*/
    //let newQueryStr = addUrlQueryParam("searchSynonyms", newSynonyms);
    /*window.history.replaceState({ query: newQueryStr }, '', window.location.pathname + newQueryStr);*/
  }
  onViewTypeChanged(type) {
/*
    let newType;
    if (type) {
      newType = type;
    } else {
      newType = 'list';
    }
  */  
    this.setState({ viewType: type },function() {
			if (type === 'grid') {
				this.setState({showTaxaDetail: "off"},function() {
   			 	this.updateExportUrls();
		    });
			}else{
   			 this.updateExportUrls();
			}
    });
    //let newQueryStr = addUrlQueryParam("viewType", newType);
    /*window.history.replaceState({ query: newQueryStr }, '', window.location.pathname + newQueryStr);*/
  }
  onTaxaDetailChanged(taxaDetail) {
  	this.setState({showTaxaDetail: taxaDetail},function() {
    	this.updateExportUrls();
    });
 /* 	
  	let newVal;
  	if (taxaDetail === 'on') {
  		newVal = taxaDetail;
  	}else{
  		newVal = 'off';
  	}
  */	
  	//let newQueryStr = addUrlQueryParam("taxaDetail",newVal);
    /*window.history.replaceState({ query: newQueryStr }, '', window.location.pathname + newQueryStr);*/
  }

  render() {
		let shortAbstract = '';
		if (this.state.abstract.length > 0) {
			shortAbstract = this.state.abstract.replace(/^(.{240}[^\s]*).*/, "$1") + "...";//wordsafe truncate
		}

    return (
    <div className="wrapper">
			<Loading 
				clientRoot={ this.props.clientRoot }
				isLoading={ this.state.isLoading }
			/>
			<div className="page-header">
				<PageHeader bgClass="explore" title={ this.state.projName } />
      </div>
      <div className="container explore" style={{ minHeight: "45em" }}>
 				<div className="row pb-2">
          <div className="col-9 copy">
            <h2>{ this.state.title }</h2>
            {this.state.authors.length > 0 &&
            <p className="authors"><strong>Authors:</strong> <span className="authors-content" dangerouslySetInnerHTML={{__html: this.state.authors}} /></p>
						}
						{this.state.abstract.length > 0 && this.state.displayAbstract == 'default' &&
							<div>
							<p className="abstract"><strong>Abstract:</strong> <span className="abstract-content" dangerouslySetInnerHTML={{__html: shortAbstract}} /></p>
							<div className="more more-less" onClick={() => this.toggleDisplay()}>
									<FontAwesomeIcon icon="chevron-down" />Show Abstract
							</div>
							</div>
						}
						{this.state.abstract.length > 0 && this.state.displayAbstract == 'expanded' &&
							<div>
							<p className="abstract"><strong>Abstract:</strong> <span className="abstract-content" dangerouslySetInnerHTML={{__html: this.state.abstract}} /></p>
							<div className="less more-less" onClick={() => this.toggleDisplay()}>
									<FontAwesomeIcon icon="chevron-up" />Hide Abstract
							</div>
							</div>
						}				
				
          </div>
          <div className="col-3 text-right mt-3 map">
          		{ /*this.state.googleMapUrl.length > 0 &&
          			<a href={ this.props.clientRoot + "/checklists/checklistmap.php?clid=" + this.getClid() } target="_blank">
              		<img className="img-fluid" src={this.state.googleMapUrl} title="Project map" alt="Map representation of checklists" />
              	</a>
              	*/
              }
          		{ this.state.googleMapUrl.length > 0 &&
          			<a href={ this.props.clientRoot + "/map/googlemap.php?maptype=occquery&clid=" + this.getClid() } target="_blank">
              		<img className="img-fluid" src={this.state.googleMapUrl} title="Project map" alt="Map representation of checklists" />
              	</a>
              }
          </div>
        </div>
				<div className="row explore-main inventory-main">
					<hr/>
					<div className="col-12 col-xl-4 col-md-5 sidebar-wrapper">
					{
					
						<SideBar
							//ref={ this.sideBarRef }
							clid={ this.state.clid }
							dynclid={ this.state.dynclid }
							style={{ background: "#DFEFD3" }}
							isLoading={ this.state.isLoading }
							clientRoot={this.props.clientRoot}
							totals={ this.state.totals }
							fixedTotals={ this.state.fixedTotals }
							searchText={ this.state.searchText }
							searchSuggestionUrl="./rpc/autofillsearch.php"
							onSearch={ this.onSearch }
							onSearchTextChanged={ this.onSearchTextChanged }
							searchName={ this.state.searchName }
							searchSynonyms={ this.state.searchSynonyms }
							viewType={ this.state.viewType }
							sortBy={ this.state.sortBy }
							showTaxaDetail={ this.state.showTaxaDetail }
							onSearchSynonymsClicked={ this.onSearchSynonymsChanged }
							onSearchNameClicked={ this.onSearchNameChanged }
							onSortByClicked={ this.onSortByChanged }
							onViewTypeClicked={ this.onViewTypeChanged }
							onTaxaDetailClicked={ this.onTaxaDetailChanged }
							onFilterClicked={ this.onFilterRemoved }
							onClearSearch={ this.clearTextSearch }
							filters={
								Object.keys(this.state.filters).map((filterKey) => {
									return { key: filterKey, val: this.state.filters[filterKey] }
								})
							}
							exportUrlCsv={ this.state.exportUrlCsv }
							exportUrlWord={ this.state.exportUrlWord }
						/>
						
					}
					</div>
					<div className="col-12 col-xl-8 col-md-7 results-wrapper">
						<div className="row">
							<div className="col">
								<div className="explore-header inventory-header">
									<div className="current-wrapper">
										<div className="btn btn-primary current-button" role="button"><FontAwesomeIcon icon="list-ul" /> Explore</div>
										
										<div className="button-wrapper">
										{ this.state.totals.taxa < this.state.fixedTotals.taxa &&
											<div className="filter-status">(Filtered)</div>
										}
										</div>
									</div>
									{ this.getClid() > -1 && this.getPid() > -1 &&
										<div className="alt-wrapper">
											<div>Switch to</div>
											<a href={getIdentifyPage(this.props.clientRoot,this.getClid(),this.getPid())}>
												<div className="btn btn-primary alt-button" role="button">
													<FontAwesomeIcon icon="search-plus" /> Identify
												</div>
											</a>
										</div>
									}
								</div>
									<ExploreSearchContainer
										searchResults={ this.state.searchResults }
										viewType={ this.state.viewType }
										sortBy={ this.state.sortBy }
										showTaxaDetail={ this.state.showTaxaDetail }
										clientRoot={this.props.clientRoot}
										isSearching={this.state.isSearching}
										currentTids={this.state.currentTids}
									/>
											
							</div>
							
						</div>											

					</div>
				</div>
					
				<div className="row ">
					<a className="back-to-top mx-auto"
						onClick={() => window.scrollTo(0,0)}
					>
						<span className="back-to-top-label">Top</span>
						<FontAwesomeIcon icon="chevron-up" size="2x"/>
					</a>	
				</div>
			</div>
		</div>
    );
  }
}
ExploreApp.defaultProps = {
  clid: -1,
  pid: -1,
  dynclid: -1,
  showVouchers: 0,
  currentTids: []
  //abstract: '',
  //authors: '',
  //googleMapUrl: '',
};

const headerContainer = document.getElementById("react-header");
const dataProps = JSON.parse(headerContainer.getAttribute("data-props"));
const domContainer = document.getElementById("react-explore-app");
const queryParams = getUrlQueryParams(window.location.search);

// Use both cl and clid (symbiota-light) to denote the checklist
if (queryParams.clid) {
  queryParams.cl = queryParams.clid;
}

if (queryParams.cl || queryParams.dynclid) {
  ReactDOM.render(
    <ExploreApp 
    	clid={queryParams.cl? queryParams.cl : -1 } 
    	pid={queryParams.pid? queryParams.pid : -1 } 
    	dynclid={queryParams.dynclid ? queryParams.dynclid : -1 } 
    	showVouchers={ queryParams.showvouchers } 
    	clientRoot={ dataProps["clientRoot"] } 
    	googleMapKey={ dataProps["googleMapKey"] }
    />,
    domContainer
  );
} else {
  window.location = "/projects/";
}
