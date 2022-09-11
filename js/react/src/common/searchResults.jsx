import React from "react";
import {getCommonNameStr, getTaxaPage, getGardenTaxaPage} from "../common/taxaUtils";
import Searching from "../common/searching.jsx";
import TextareaField from "../common/textarea.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faSquare, faMinusCircle, faEdit } from '@fortawesome/free-solid-svg-icons'
library.add( faSquare, faMinusCircle, faEdit );


function SearchResult(props) {
  const useGrid = props.viewType === "grid";
  let nameFirst = '';
  let nameSecond = '';
	if (props.sortBy == 'vernacularName') {
		nameFirst = props.commonName;
		nameSecond = props.sciName;
	}else {
		nameFirst = props.sciName;
		nameSecond = props.commonName;
	}
  if (props.display) {
    return (
      <a href={props.href} className="text-decoration-none" style={{ maxWidth: "185px" }} target="_blank">
        <div className={ "card search-result " + (useGrid ? "grid-result" : "list-result") }>
            <div className={useGrid ? "" : "card-body"}>
              <img
                className={useGrid ? "card-img-top grid-image" : "d-inline-block mr-1 list-image"}
                alt={props.title}
                src={props.src}
              />
              <div className={(useGrid ? "card-body" : "d-inline py-1") + " px-0"} style={{overflow: "hidden"}}>
                <div className={"card-text" + (useGrid ? "" : " d-inline")}>
                  <span className="">{nameFirst}</span>
                  {useGrid ? <br/> : " - "}
                  <span className="font-italic">{nameSecond}</span>
                </div>
              </div>
            </div>
        </div>
      </a>
    );
  }

  return <span style={{ display: "none" }}/>;
}

class SearchResultContainer extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div
        id="search-results"
        className={ "mt-4 w-100" + (this.props.viewType === "grid" ? " search-result-grid" : "") }
      >
        { this.props.children }
      </div>
    );
  }
}

function GardenSearchResult(props) {
  const useGrid = props.viewType === "grid";
  let nameFirst = '';
  let nameSecond = '';
	if (props.sortBy == 'vernacularName') {
		nameFirst = props.commonName;
		nameSecond = props.sciName;
	}else {
		nameFirst = props.sciName;
		nameSecond = props.commonName;
	}
  if (props.display) {
    return (
      <a href={props.href} className="text-decoration-none" style={{ maxWidth: "185px" }} target="_blank">
        <div className={ "card search-result " + (useGrid ? "grid-result" : "list-result") }>
            <div className={useGrid ? "" : "card-body"}>
              <img
                className={useGrid ? "card-img-top grid-image" : "d-inline-block mr-2 list-image"}
                alt={props.title}
                src={props.src}
              />
              <div className={(useGrid ? "card-body" : "d-inline py-1") + " px-0"} style={{overflow: "hidden"}}>
                <div className={"card-text" + (useGrid ? "" : " d-inline")}>
                  <span className="">{nameFirst}</span>
                  {useGrid ? <br/> : " - "}
                  <span className="font-italic">{nameSecond}</span>
                </div>
              </div>
            </div>
        </div>
      </a>
    );
  }

  return <span style={{ display: "none" }}/>;
}

class GardenSearchContainer extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div
        id="search-results"
        className={ "mt-4 w-100" + (this.props.viewType === "grid" ? " search-result-grid" : "") }
      >
				<Searching 
					clientRoot={ this.props.clientRoot }
					isSearching={ this.props.isSearching }
				/>
			{	this.props.searchResults.taxonSort.map((result) =>  {
						let display = (this.props.currentTids.indexOf(result.tid) > -1);
						return (
							<GardenSearchResult
								display={ display }
								key={ result.tid }
								viewType={ this.props.viewType }
								showTaxaDetail={ this.props.showTaxaDetail }
								href={ getGardenTaxaPage(this.props.clientRoot, result.tid) }
								src={ result.image }
								commonName={ getCommonNameStr(result) }
								sciName={ result.sciname ? result.sciname : '' }
								author={ result.author ? result.author : '' }
								vouchers={  result.vouchers ? result.vouchers : '' }
								clientRoot={ this.props.clientRoot }
								sortBy={ this.props.sortBy }
							/>
						)
					})
				}
      </div>
    );
  }
}

class ExploreSearchResult extends React.Component  {
  constructor(props) {
    super(props);
    this.state = { 
			editNotes: false,
			updatedData: {}
		};
    this.updateSPPFields = this.updateSPPFields.bind(this);
    this.saveEditSPP = this.saveEditSPP.bind(this);
    this.toggleEditingNotes = this.toggleEditingNotes.bind(this);
  }
  toggleEditingNotes() {
  	let newEditNotes = true;
  	if (this.state.editNotes == true) {
  		newEditNotes = false;
  	}
  	this.setState({
      editNotes: newEditNotes
    },function() {
			//console.log(this.state.editNotes);
    });
  }
	updateSPPFields(e) {
		//console.log(e.target.value);
		//Object { name: "notes", value: "My damn notesasdfasdf", section: "spp" }

		let name = e.target.name;
		let value = e.target.value;
		let stateData = {};// = this.state.updatedData;
		stateData[name] = value;
		//console.log(stateData);
		this.setState({
      updatedData: Object.assign(this.state.updatedData, stateData)
    },function() {
			//console.log(this.state.updatedData);
    });
	}
	saveEditSPP(obj) {
		let self = this;
		this.props.storeChange({'action': obj.action,'name': obj.name, 'value': obj.value, 'section': obj.section, 'notes': obj.notes});
		setTimeout(function() {
			self.toggleEditingNotes();
		},1500);
		
	}
	
  render() {
  	const useGrid = this.props.viewType === "grid";
  	//console.log(this.props);
		return (
				<div className={ "card search-result " + (useGrid ? "grid-result" : "list-result") }>
						<div className={useGrid ? "" : "card-body"}>
							{useGrid &&
								<img
									className={useGrid ? "card-img-top grid-image" : "d-inline-block mr-1 list-image"}
									alt={this.props.title}
									src={this.props.src}
								/>
							}
							{!useGrid && 
								<FontAwesomeIcon icon="square" />
							}
							<div className={(useGrid ? "card-body" : "d-inline py-1") + " px-0"} style={{overflow: "hidden"}}>
								<div className={"card-text" + (useGrid ? "" : " d-inline")}>
									<a href={this.props.href} className="text-decoration-none" style={{ maxWidth: "185px" }} target="_blank">
										<span className="font-italic sci-name">{this.props.sciName}</span>
										{
											this.props.showTaxaDetail === 'on' &&
											<span className="author"> ({this.props.author})</span>
										}
										{ !useGrid && this.props.commonName.length > 0? <span dangerouslySetInnerHTML={{__html: ' &mdash; '}} /> :''}
										<span className="common-name">{this.props.commonName}</span>
									</a>
									{
										this.props.showTaxaDetail === 'on' && this.props.vouchers.length && 
								
											<div className="vouchers">Vouchers:&nbsp;          		
											{
												this.props.vouchers.map((voucher) =>  {
													return (
														<a href={ this.props.clientRoot + "/collections/individual/index.php?occid=" + voucher.occid } target="_blank" key={ voucher.occid } className={ "voucher" } >
															<span className={ "recorded-by" }>{voucher.recordedby} </span>
															<span className={ "event-date" }>{voucher.eventdate}</span>
															<span className={ "institution-code" }> [{ voucher.institutioncode }]</span>
														</a>
													)
												})
												.reduce((prev, curr) => [prev, ', ', curr])
											}
											</div>
									} 
									{
										this.props.isEditable == true && 
										<span>
											<span className="taxa-edit">
												<a 
													type="button"
													className="btn"
													name={ this.props.sciName }
													value={ this.props.sciName }	
													action={ 'edit' }
													tid={ this.props.tid } 				
													title={ 'Edit Notes' }
													onClick={ () => this.toggleEditingNotes()} 
												>
													<FontAwesomeIcon icon="edit" />
												</a>
											</span>
									
											<span className="taxa-delete">
												<a 
													type="button"
													className="btn"
													name={ this.props.sciName }
													value={ this.props.sciName }	
													action={ 'delete' }
													tid={ this.props.tid } 				
													title={ 'Delete from checklist' }
													onClick={ () => this.props.storeChange({'action': 'delete','name': this.props.sciName, 'value': this.props.tid, 'section': 'spp'})} 
												>
													<FontAwesomeIcon icon="minus-circle" />
												</a>
											</span>
										</span>
									}         
									
									{
										this.props.isEditable == true && this.state.editNotes &&
										        		
											<div className="vouchers notes spp">
										
												<div>			
													<textarea
														aria-label="notes"
														section="spp" 
														name="notes" 
														defaultValue={this.props.checklistNotes}
														placeholder="Your notes here" 
														onChange={ this.updateSPPFields}
													/>
													<a 
														type="button"
														className="btn btn-primary"
														onClick={ () => this.saveEditSPP({'action': 'edit','name': this.props.sciName, 'value': this.props.tid, 'section': 'spp', 'notes': this.state.updatedData['notes']})} 
													>Save</a>
													<a 
														type="button"
														className="btn btn-primary"
														onClick={ () => this.toggleEditingNotes()} 
													>Cancel</a>
												</div>
											
											</div>
									}     
									{
										this.props.isEditable == true && this.state.editNotes != true &&
											
											<div className="vouchers notes spp">
											{
												this.props.checklistNotes
											}
											</div>
									}    
									      
								</div>
							</div>
						</div>
				</div>
		)
	}
}
function ExploreSearchContainer(props) {
	//console.log(props);
  const useGrid = props.viewType === "grid";
	if (props.searchResults) {
		if (props.sortBy === 'taxon') {		
			return (
				<div
					id="search-results"
					className={ "mt-4 w-100" + (props.viewType === "grid" ? " search-result-grid" : "") }
				>
				<Searching 
					clientRoot={ props.clientRoot }
					isSearching={ props.isSearching }
				/>
				{	props.searchResults.taxonSort.map((result) =>  {
						//console.log(result);
						return (
							<ExploreSearchResult
								key={ result.tid }
								tid={ result.tid }
								section={ props.section }
								viewType={ props.viewType }
								showTaxaDetail={ props.showTaxaDetail }
								href={ getTaxaPage(props.clientRoot, result.tid) }
								src={ result.thumbnail }
								commonName={ getCommonNameStr(result) }
								sciName={ result.sciname ? result.sciname : '' }
								author={ result.author ? result.author : '' }
								vouchers={  result.vouchers ? result.vouchers : '' }
								clientRoot={ props.clientRoot }
								isEditable={ props.isEditable }
								storeChange={ props.storeChange }
								checklistNotes={ result.checklistNotes ? result.checklistNotes : ''} 
							/>
						)
					})
				}
				</div>
			)
		}else{
			return (
				
				<div
					id="search-results"
					className={ "mt-2 w-100" }
				>
				<Searching 
					clientRoot={ props.clientRoot }
					isSearching={ props.isSearching }
				/>
				{
						Object.entries(props.searchResults.familySort).map(([family, results]) => {
							return (
								<div key={ family } className="family-group">
									<h4>{ family }</h4>	
									<div className={ (props.viewType === "grid" ? " search-result-grid" : "") } >
									{ results.map((result) =>  {
											return (
												<ExploreSearchResult
													key={ result.tid }
													tid={ result.tid }
													viewType={ props.viewType }
													showTaxaDetail={ props.showTaxaDetail }
													href={ getTaxaPage(props.clientRoot, result.tid) }
													src={ result.thumbnail }
													commonName={ getCommonNameStr(result) }
													sciName={ result.sciname ? result.sciname : '' }
													author={ result.author ? result.author : '' }
													vouchers={  result.vouchers ? result.vouchers : '' }
													clientRoot={ props.clientRoot }
													isEditable={ props.isEditable }
													storeChange={ props.storeChange } 
													checklistNotes={ result.checklistNotes ? result.checklistNotes : ''}
												/>
											)
										})
									}
									</div>
								</div>
							)
						})
				}
				</div>
			)
		}
	}
  return <span style={{ display: "none" }}/>;
}
ExploreSearchContainer.defaultProps = {
  searchResults: [],
};

function IdentifySearchResult(props) {
  const useGrid = props.viewType === "grid";//not an option here but leaving it in case
	return (
		<a href={props.href} className="text-decoration-none" style={{ maxWidth: "185px" }} target="_blank">
			<div className={ "card search-result " + (useGrid ? "grid-result" : "list-result") }>
					<div className={useGrid ? "" : "card-body"}>
						{useGrid &&
							<img
								className={useGrid ? "card-img-top grid-image" : "d-inline-block mr-2 list-image"}
								alt={props.title}
								src={props.src}
							/>
						}
						{!useGrid && 
							<FontAwesomeIcon icon="square" />
						}
						<div className={(useGrid ? "card-body" : "d-inline py-1") + " px-0"} style={{overflow: "hidden"}}>
								{props.sortBy === 'sciName' &&
										<div className={"card-text" + (useGrid ? "" : " d-inline")}>
											<span className="font-italic sci-name">{props.sciName}</span>
										</div>
								}
								{props.sortBy === 'vernacularName' &&
										<div className={"card-text" + (useGrid ? "" : " d-inline")}>
											<span className="font-italic sci-name">{props.commonName}</span>
										</div>
								}  
						</div>
					</div>
			</div>
		</a>
	)
}
function IdentifySearchContainer(props) {
  const useGrid = props.viewType === "grid";//not an option here but leaving it in case
	if (props.searchResults) {

		return (
			
			<div
				id="search-results"
				className={ "mt-2 w-100" }
			>				
			<Searching 
				clientRoot={ props.clientRoot }
				isSearching={ props.isSearching }
			/>
			{
					Object.entries(props.searchResults.familySort).map(([family, results]) => {
						return (
							<div key={ family } className="family-group">
								<h4>{ family }</h4>	
								<div className={ (props.viewType === "grid" ? " search-result-grid" : "") } >
								{ results.map((result) =>  {
										return (
											<IdentifySearchResult
												key={ result.tid }
												href={ getTaxaPage(props.clientRoot, result.tid) }
												src={ result.thumbnail }
												commonName={ getCommonNameStr(result) }
												sciName={ result.sciname ? result.sciname : '' }
												sortBy={ props.sortBy }
											/>
										)
									})
								}
								</div>
							</div>
						)
					})
			}
			</div>
		)
		
	}
  return <span style={{ display: "none" }}/>;
}

IdentifySearchContainer.defaultProps = {
  searchResults: [],
};
/*
ExploreSearchResult.defaultProps = {
	tid: -1,
	sciName: '',
	section: ''
	
};*/
function VendorUploadContainer(props) {
	//console.log(props.uploadResponse.length);
	//if (props.uploadResponse.status == 'success') {
	
			return (
			
				<div
					id="search-results"
					className={ "vendor-upload-preview" }
				>
				{
				<Searching 
					clientRoot={ props.clientRoot }
					isSearching={ props.isSearching }
				/>
				}
				{props.uploadResponse.status == 'success' &&
			
					<table>
						<thead>
							<tr key="header">
								<th className="search-sciname">Your sciname</th>
								<th className="code">Result</th>
								<th className="of-sciname">OF sciname</th>
								<th className="feedback">Feedback</th>
							</tr>
						</thead>
						<tbody>
					
						{
								Object.entries(props.uploadResponse.results).map(([index,result]) => {
									//console.log(result);
									return (
	
										<tr key={result.tid}>
											<td className="search-sciname">{result.searchSciname}</td>
											<td className={ "code " + result.code.toLowerCase()}>{result.code}</td>
											<td className="of-sciname">{result.OFsciname}</td>
											<td className="feedback">{
														result.feedback && result.feedback
															.map(t => <span>{t}</span>)
													}
											</td>
										</tr>
						
									)
								})
						
						}
						</tbody>
					</table>
					}
				
				</div>
			)
		
	//}
	//return <span style={{ display: "none" }}/>;
}
VendorUploadContainer.defaultProps = {
	uploadResponse: [],
	clientRoot: '',
	isSearching: true
};

export { SearchResultContainer, SearchResult, GardenSearchContainer, GardenSearchResult, ExploreSearchResult, ExploreSearchContainer, IdentifySearchResult, IdentifySearchContainer, VendorUploadContainer };


