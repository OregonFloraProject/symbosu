"use strict";
import React, { useState } from "react";
import ReactDOM from "react-dom";

import SideBarVendor from "./sidebar-vendor.jsx";
import ViewOpts from "./viewOpts.jsx";
import httpGet from "../common/httpGet.js";
import {ExploreSearchContainer, SearchResultContainer} from "../common/searchResults.jsx";
import {addUrlQueryParam, getUrlQueryParams} from "../common/queryParams.js";
import {getCommonNameStr, getTaxaPage, getIdentifyPage} from "../common/taxaUtils";
import PageHeader from "../common/pageHeader.jsx";
import Loading from "../common/loading.jsx";
import TextField from "../common/formFields.jsx";
import TextareaField from "../common/textarea.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import {faChevronDown, faChevronUp, faListUl, faSearchPlus, faEdit } from '@fortawesome/free-solid-svg-icons'
library.add( faChevronDown, faChevronUp, faListUl, faSearchPlus, faEdit );

const GARDEN_CLID = 54;

class ExploreApp extends React.Component {
  constructor(props) {
    super(props);
    const queryParams = getUrlQueryParams(window.location.search);
    
    // TODO: searchText is both a core state value and a state.filters value; How can we make the filtering system more efficient?
    this.state = {
      isLoading: true,
      isSearching: false,
      isEditing: {info: false},
      isUpdating: {info: false},
      updatedData: {info: {},spp: []},
      updateMsg: {info: {}, spp: []},
      clid: null,
      pid: null,
      projName: null,
      title: '',
      authors: '',
      abstract: '',
      locality: '',
      publication: '',
      notes: '',
      latcentroid: '',
      longcentroid: '',
      pointradiusmeters: '',
      displayDescription: 'default',
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

    this.getChecklist = this.getChecklist.bind(this);
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
    this.toggleEditing = this.toggleEditing.bind(this);
    this.updateField = this.updateField.bind(this);
    this.updateSPP = this.updateSPP.bind(this);
    this.updateSection = this.updateSection.bind(this);
    
  }

  getClid() {
    return parseInt(this.props.clid);
  }
  getPid() {
    return parseInt(this.props.pid);
  }
  toggleDisplay = () => {
		let newVal = 'default';
		if (this.state.displayDescription == 'default') {
			newVal = 'expanded';
		} 
		this.setState({
			displayDescription: newVal
		});

  }
  toggleEditing(section) {
    let isEditing = this.state.isEditing;
  	let newVal;
  	if (this.state.isEditing[section] == true) {
  		if ( Object.keys(this.state.updatedData[section]).length > 0 ) {
				if (confirm("You have unsaved changes.  Proceed?") == true) {
					newVal = false;
				}else{
					newVal = true;
				}
			}else{
				newVal = false;
			}
  	}else{
  		newVal = true;
  	}
		isEditing[section] = newVal;
    this.setState({
      isEditing: Object.assign({}, this.state.isEditing, isEditing)
    });
  
  }
	updateField(obj) {
		let section = obj.section;
		let name = obj.name;
		let value = obj.value;
		if (section == 'spp' && value < 1) {
			return;
		}
		let stateData = this.state.updatedData;
		stateData[section][name] = value;
		this.setState({
      updatedData: Object.assign(this.state.updatedData, stateData)
    },function() {
			//console.log(this.state.updatedData);
    });
	}
	
	updateSPP(obj) {
		//console.log('updatespp');
		//console.log(obj);
		let section = obj.section;
		let name = obj.name;
		let value = obj.value;
		let action = obj.action;
		let fields = {};
		let actionStr = '';
		switch (action) {
			case 'add':
				actionStr = 'added';
				break;
			case 'delete':
				actionStr = 'deleted';
				if (!confirm("Delete " + name + " from checklist - proceed?") == true) {
					return;
				}
				break;
			case 'edit':
				actionStr = 'edited';
				fields['notes'] = obj.notes;
				break;
		}
		//console.log(this.state.updatedData);
		let stateData = this.state.updatedData;
		stateData[section][name] = value;
    if ( Object.keys(stateData[section]).length > 0 ) {
      let url = `${this.props.clientRoot}/checklists/rpc/api.php`;
			let mapParams = new URLSearchParams();
			mapParams.append('update',section);
			mapParams.append('action',action);
			mapParams.append('pid',this.props.pid);
			mapParams.append('clid',this.props.clid);
    	Object.entries(this.state.updatedData[section]).map(([key, value]) => {
    		if (actionStr == 'edited') {
					mapParams.append(section,value);//not an array
				}else{
					mapParams.append(section + '[]',value);
				}
			});
			Object.entries(fields).map(([key,value]) => {
				mapParams.append(key,value);
			});
			url += '?' + mapParams.toString();
			//console.log(url);
			//return;
			///checklists/rpc/api.php?update=spp&action=delete&pid=4&clid=14920&spp[]=3249
			///checklists/rpc/api.php?update=spp&action=add&pid=4&clid=14920&spp[]=3549
			///checklists/rpc/api.php?update=spp&action=edit&pid=4&clid=14920&spp=3277&notes=My+notes
			
			httpGet(url)
				.then((res) => {
					let jres = JSON.parse(res);
					var msg = '';
					if (jres.success > 0) {
						msg = jres.success + " plant" + (jres.success > 1? 's':'') + " " + actionStr;
					}
				
					//clear updatedData
					let stateData = this.state.updatedData;
					stateData[section] = [];
					this.setState({ 
						updatedData: Object.assign(this.state.updatedData, stateData),
					});
				
					//show msg
					var id = section + '-msg';
					var msgDiv = document.getElementById(id);
					msgDiv.classList.add("display");
					msgDiv.innerHTML = msg;
					setTimeout(function() {
						msgDiv.classList.remove("display");
						msgDiv.innerHTML = '';
					},2000);
					
					if (actionStr == 'edit') {
					
					
					}
			})
			.catch((err) => {
				//window.location = "/";
				console.error(err);
			})
      .finally(() => {
        this.getChecklist();
      }); 
    }
	}
	updateSection(section) {
    let url = `${this.props.clientRoot}/checklists/rpc/api.php`;
		let mapParams = new URLSearchParams();
		mapParams.append('update',section);
		mapParams.append('pid',this.props.pid);
		mapParams.append('clid',this.props.clid);
		if ( Object.keys(this.state.updatedData[section]).length > 0 ) {
			/*
			let stateUpdating = this.state.isUpdating;
			stateUpdating[section] = true;
			this.setState({
				stateUpdating: Object.assign(this.state.isUpdating, stateUpdating)
			},function() {		*/
				Object.entries(this.state.updatedData[section]).map(([key, value]) => {
					mapParams.append(key,value);
				});
				url += '?' + mapParams.toString();
				//console.log(url);///checklists/rpc/api.php?update=info&pid=4&clid=14920&locality=alt+locality2&notes=alt+notes2
				httpGet(url)
					.then((res) => {
						let jres = JSON.parse(res);
						var msg = '';
						if (jres.success > 0) {
							msg = jres.success + " field" + (jres.success > 1? 's':'') + " updated";
						}
						
						//clear updatedData
						let stateData = this.state.updatedData;
						stateData[section] = [];
						this.setState({ 
							updatedData: Object.assign(this.state.updatedData, stateData),
						});
						
						//show msg
						var id = section + '-msg';
						var msgDiv = document.getElementById(id);
						msgDiv.classList.add("display");
						msgDiv.innerHTML = msg;
						setTimeout(function() {
							msgDiv.classList.remove("display");
							msgDiv.innerHTML = '';
						},2000);

					}); 
			/*});*/
		}
	}
  componentDidMount() {
  	this.getChecklist();
  }
  getChecklist() {
   // Load search results
    let url = `${this.props.clientRoot}/checklists/rpc/api.php?clid=${this.props.clid}&pid=${this.props.pid}`;
    let exportUrl = `${this.props.clientRoot}/ident/rpc/api.php`;//use identify api for export
    //console.log(url);
    httpGet(url)
			.then((res) => {
				// /checklists/rpc/api.php?clid=3
				res = JSON.parse(res);
				
				let googleMapUrl = '';			
				let host = window.location.host;
						
				if (res.latcentroid !== '' && res.longcentroid !== '') {
					
					googleMapUrl += 'https://maps.google.com/maps/api/staticmap';
					let mapParams = new URLSearchParams();
					let markerUrl = 'http://' + host + this.props.clientRoot + '/images/icons/map_markers/single.png'; 
					mapParams.append("key",this.props.googleMapKey);
					mapParams.append("maptype",'terrain');
					mapParams.append("size",'220x220');
					mapParams.append("zoom",6);
					mapParams.append("markers",'icon:' + markerUrl + '|anchor:center|' + res.latcentroid + ',' + res.longcentroid);
		
					googleMapUrl += '?' + mapParams.toString();
				}
				
				let viewType = 'list';
				if (this.getPid() == 3) {
					viewType = 'grid';
				}
							
				this.setState({
					clid: this.getClid(),
					pid: this.getPid(),
					projName: res.projName,
					title: res.title,
					authors: res.authors,
					abstract: res.abstract,
					locality: res.locality,
					publication: res.publication,
					notes: res.notes,
					latcentroid: res.latcentroid,
					longcentroid: res.longcentroid,
					pointradiusmeters: res.pointradiusmeters,
					viewType: viewType,
					//taxa: res.taxa,
					searchResults: this.sortResults(res.taxa),
					totals: res.totals,
					fixedTotals: res.totals,
					googleMapUrl: googleMapUrl,
					exportUrl: exportUrl,
					exportUrlCsv: exportUrl + `?export=csv&clid=` + this.getClid() + `&pid=` + this.getPid(),// + `&dynclid=` + this.getDynclid(),
					exportUrlWord: exportUrl + `?export=word&clid=` + this.getClid() + `&pid=` + this.getPid(),// + `&dynclid=` + this.getDynclid()
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
		//exportParams.append("dynclid",this.getDynclid());

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
		//exportParams.append("dynclid",this.getDynclid());
		exportParams.append("showcommon",1);
		if (this.state.filters.searchText) {
			exportParams.append("taxonfilter",this.state.filters.searchText);
		}
		
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
  	let newObj = {};
  	newObj.section = 'spp';
  	newObj.name = searchObj.text;
  	newObj.value = searchObj.value;
  
		this.updateField(newObj);
		  
  }
	updateTotals(totals) {
	  this.setState({
      totals: totals,
    });
	}

  // On search end
  onSearchResults(results) {
    let newResults;
    newResults = this.sortResults(results);
    this.setState({ searchResults: newResults },function() {
			this.updateExportUrls();
		});
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
  }
  onSearchSynonymsChanged(synonyms) {
    this.setState({ searchSynonyms: synonyms },function() {
    	this.updateExportUrls();
    });
  }
  onViewTypeChanged(type) {
    this.setState({ viewType: type },function() {
			if (type === 'grid') {
				this.setState({showTaxaDetail: "off"},function() {
   			 	this.updateExportUrls();
		    });
			}else{
   			 this.updateExportUrls();
			}
    });
  }
  onTaxaDetailChanged(taxaDetail) {
  	this.setState({showTaxaDetail: taxaDetail},function() {
    	this.updateExportUrls();
    });
  }

  render() {
  
  	var infoSubmitValue = 'Update Info';//(this.state.isUpdating['info']? 'Updating' : 'Update Info');
  	var infoSubmitClass = "btn-primary " + (this.state.isUpdating['info']? 'updating' : 'normal');
		let suggestionUrl = `${this.props.clientRoot}/garden/rpc/autofillsearch.php`;
		
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
 				<div className="row pb-2 info">
          <div className="col-9 copy">
						{	this.state.displayDescription == 'default' &&
							<div className="row title">
								<div className="col-9">
									<h2><span className="editable-content title-content">{ this.state.title }</span></h2>	
								</div>
								<div className="col-3">
									<div className="more more-less display" onClick={() => this.toggleDisplay()}>
											<FontAwesomeIcon icon="chevron-down" />Show Vendor Info
									</div>
								</div>	
							</div>	
						}
						{	this.state.displayDescription == 'expanded' &&
							<div className="row title">
								<div className="col-9">
									{ this.state.isEditing['info'] == false && 
										<h2><span className="editable-content title-content">{ this.state.title }</span></h2>	
									}
									{ this.state.isEditing['info'] == true && 
										<h2><TextField section="info" name="title" value={ this.state.title } onUpdate={this.updateField} placeholder="Vendor Name"/></h2>	
									}
								</div>
								<div className="col-3">
									<div className="less more-less display" onClick={() => this.toggleDisplay()}>
											<FontAwesomeIcon icon="chevron-up" />Hide Vendor Info
									</div>
								</div>	
							</div>	
						}
						{this.state.displayDescription == 'expanded' && 

							<div>
								<div className="row authors">
									<div className="col-2">
										<p><strong>Authors: </strong></p>
									</div>
									<div className="col-10">
										{	this.state.isEditing['info'] == true?
											<TextField section="info" name="authors" value={ this.state.authors } onUpdate={this.updateField} placeholder="Authors" />
											:
											<span className="editable-content authors-content" dangerouslySetInnerHTML={{__html: this.state.authors}} />
										}
									</div>
								</div>
								
								
								<div className="row abstract">
									<div className="col-2">
										<p><strong>Abstract: </strong></p>
									</div>
									<div className="col-10">
										{	this.state.isEditing['info'] == true?
											<TextareaField section="info" name="abstract" value={ this.state.abstract } onUpdate={this.updateField} placeholder="Abstract" />
											:
											<span className="editable-content abstract-content" dangerouslySetInnerHTML={{__html: this.state.abstract}} />
										}
									</div>
								</div>
							
								<div className="row locality">
									<div className="col-2">
										<p><strong>Locality: </strong></p>
									</div>
									<div className="col-10">
										{	this.state.isEditing['info'] == true?
											<TextField section="info" name="locality" value={ this.state.locality } onUpdate={this.updateField}  placeholder="Locality"/>
											:
											<span className="editable-content locality-content" dangerouslySetInnerHTML={{__html: this.state.locality}} />
										}
									</div>
								</div>

								<div className="row publication">
									<div className="col-2">
										<p><strong>Citation: </strong></p>
									</div>
									<div className="col-10">
										{	this.state.isEditing['info'] == true?
											<TextField section="info" name="publication" value={ this.state.publication } onUpdate={this.updateField}  placeholder="Citation"/>
											:
											<span className="editable-content publication-content" dangerouslySetInnerHTML={{__html: this.state.publication}} />
										}
									</div>
								</div>

								<div className="row notes">
									<div className="col-2">
										<p><strong>Notes: </strong></p>
									</div>
									<div className="col-10">
										{	this.state.isEditing['info'] == true?
											<TextField section="info" name="notes" value={ this.state.notes } onUpdate={this.updateField}  placeholder="Notes"/>
											:
											<span className="editable-content notes-content" dangerouslySetInnerHTML={{__html: this.state.notes}} />
										}
									</div>
								</div>
													
								<div className="row latcentroid">
									<div className="col-2">
										<p><strong>Latitude: </strong></p>
									</div>
									<div className="col-10">
										{	this.state.isEditing['info'] == true?
											<TextField section="info" name="latcentroid" value={ this.state.latcentroid } onUpdate={this.updateField}  placeholder="Latitude"/>
											:
											<span className="editable-content latcentroid-content" dangerouslySetInnerHTML={{__html: this.state.latcentroid}} />
										}
									</div>
								</div>
							
								<div className="row longcentroid">
									<div className="col-2">
										<p><strong>Longitude: </strong></p>
									</div>
									<div className="col-10">
										{	this.state.isEditing['info'] == true?
											<TextField section="info" name="longcentroid" value={ this.state.longcentroid } onUpdate={this.updateField}  placeholder="Longitude"/>
											:
											<span className="editable-content longcentroid-content" dangerouslySetInnerHTML={{__html: this.state.longcentroid}} />
										}
									</div>
								</div>															
							
								<div className="row pointradiusmeters">
									<div className="col-2">
										<p><strong>Point Radius(meters): </strong></p>
									</div>
									<div className="col-10">
										{	this.state.isEditing['info'] == true?
											<TextField section="info" name="pointradiusmeters" value={ this.state.pointradiusmeters } onUpdate={this.updateField}  placeholder="Point Radius (meters)"/>
											:
											<span className="editable-content pointradiusmeters-content" dangerouslySetInnerHTML={{__html: this.state.pointradiusmeters}} />
										}
									</div>
								</div>	
							</div>			
						}

							<div className="row">
								<div className="col-3"></div>
								<div className="col-9 msg" id="info-msg"></div>
							</div>
							<div className="row">
								<div className="col-3">
									{this.state.displayDescription == 'expanded' && this.state.isEditing['info'] == true &&
											<div className="less more-less editing" onClick={() => this.toggleEditing('info')}>
												<FontAwesomeIcon icon="edit" />Toggle Editing
											</div>
									}
									{this.state.displayDescription == 'expanded' && this.state.isEditing['info'] == false &&
										<div className="more more-less" onClick={() => this.toggleEditing('info')}>
												<FontAwesomeIcon icon="edit" />Toggle Editing
										</div>
									}		
								</div>
								<div className="col-9 submit-wrapper">
									{	this.state.displayDescription == 'expanded' && this.state.isEditing['info'] == true &&
										<input 
											type="button"
											name="submitInfo"
											className={ infoSubmitClass }
											onClick={ this.updateSection.bind(this,'info')} 
											value={ infoSubmitValue }
										></input>
									}
								</div>
							</div>				
          </div>
          <div className="col-3 text-right mt-3 map">
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
					
						<SideBarVendor
							clid={ GARDEN_CLID }
							style={{ background: "#DFEFD3" }}
							isLoading={ this.state.isLoading }
							clientRoot={this.props.clientRoot}
							totals={ this.state.totals }
							fixedTotals={ this.state.fixedTotals }
							searchText={ this.state.searchText }
							searchResults={ this.state.searchResults }
							searchSuggestionUrl= { suggestionUrl }
							onSearch={ this.onSearch }
							onSearchTextChanged={ this.onSearchTextChanged }
							searchName={ this.state.searchName }
							sortBy={ this.state.sortBy }
							section={ 'spp' }
							onSearchNameClicked={ this.onSearchNameChanged }
							onClearSearch={ this.clearTextSearch }
							exportUrlCsv={ this.state.exportUrlCsv }
							exportUrlWord={ this.state.exportUrlWord }
							spp={ this.state.updatedData.spp }
							updateChange={this.updateField } 
							storeChange={this.updateSPP } 
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
									<div className="alt-wrapper">
										<div>Switch to</div>
										<a href={getIdentifyPage(this.props.clientRoot,this.getClid(),this.getPid())}>
											<div className="btn btn-primary alt-button" role="button">
												<FontAwesomeIcon icon="search-plus" /> Identify
											</div>
										</a>
									</div>
								</div>
									<ExploreSearchContainer
										searchResults={ this.state.searchResults }
										viewType={ this.state.viewType }
										sortBy={ this.state.sortBy }
										showTaxaDetail={ this.state.showTaxaDetail }
										clientRoot={this.props.clientRoot}
										isSearching={this.state.isSearching}
										isEditable={ true }
										storeChange={this.updateSPP } 
								
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
  showVouchers: 0,
};

const headerContainer = document.getElementById("react-header");
const dataProps = JSON.parse(headerContainer.getAttribute("data-props"));
const domContainer = document.getElementById("react-explore-vendor-app");
const queryParams = getUrlQueryParams(window.location.search);

// Use both cl and clid (symbiota-light) to denote the checklist
if (queryParams.clid) {
  queryParams.cl = queryParams.clid;
}

if (queryParams.cl) {
  ReactDOM.render(
    <ExploreApp clid={queryParams.cl } pid={queryParams.pid } showVouchers={ queryParams.showvouchers } clientRoot={ dataProps["clientRoot"] } googleMapKey={ dataProps["googleMapKey"] }/>,
    domContainer
  );
} else {
  window.location = "/projects/";
}
