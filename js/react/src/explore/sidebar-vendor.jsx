import React from "react";
//import HelpButton from "../common/helpButton.jsx";
import {SearchWidget} from "../common/search.jsx";
//import ViewOpts from "./viewOpts.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import {faFileCsv, faFileWord, faPrint } from '@fortawesome/free-solid-svg-icons'
library.add( faFileCsv, faFileWord, faPrint );

/**
 * Full sidebar
 */
class SideBarVendor extends React.Component {
  constructor(props) {
    super(props);
    this.state = { 
			section: props.section,
			uploadedFile: null
		};
    this.storeChange = this.props.storeChange.bind(this);
  }
  handleAddSPP(e) {
		let obj = {'action': e.target.getAttribute('action'),'name': e.target.name, 'value': e.target.getAttribute("tid"), 'section': e.target.getAttribute("section")};
		this.storeChange(obj);
  }


  render() {
  
  	let showFixedTotals = false;
  	if (this.props.totals['taxa'] < this.props.fixedTotals['taxa']) {
  		showFixedTotals = true;
  	}
  	//console.log(this.props.spp);
    return (
      <div
        id="sidebar"
        className="m-1 rounded-border"
        style={ this.props.style }>

				<div className="currently-displayed">
					<h3>Currently displayed:</h3>
					<div className="stat">
						<div className="stat-label">Families:</div>
						<div className="stat-value">{ this.props.totals['families'] }{ showFixedTotals && <span className="fixed-totals"> (of { this.props.fixedTotals['families']})</span> }</div>
					</div>
					<div className="stat">
						<div className="stat-label">Genera:</div>
						<div className="stat-value">{ this.props.totals['genera'] }{ showFixedTotals && <span className="fixed-totals"> (of { this.props.fixedTotals['genera']})</span> }</div>
					</div>
					<div className="stat">
						<div className="stat-label">Species:</div>
						<div className="stat-value">{ this.props.totals['species'] }{ showFixedTotals && <span className="fixed-totals"> (of { this.props.fixedTotals['species']})</span> } (species rank)</div>
					</div>
					<div className="stat">
						<div className="stat-label">Total Taxa:</div>
						<div className="stat-value">{ this.props.totals['taxa'] }{ showFixedTotals && <span className="fixed-totals"> (of { this.props.fixedTotals['taxa']})</span> } (including subsp. and var.)</div>
					</div>
				</div>
		  		<div className="filter-tools">
				  <h3 className="container">Manage plants</h3>
				  
				  
				  
				  {
				  	<div className="open-upload">
					  	<form>
					  		
					  		<button 
					  			type="button"
					  			className="btn btn-primary"
					  			onClick={this.props.onToggleUploadClick}
					  		>Open Inventory Manager</button>
					  		
					  	</form>
					  </div>
				
				  }

					{
					<SearchWidget
						placeholder="Add a plant"
						clientRoot={ this.props.clientRoot }
						isLoading={ this.props.isLoading }
						textValue={ this.props.searchText }
						onTextValueChanged={ this.props.onSearchTextChanged }
						onSearch={ this.props.onSearch }
						searchResults={ this.props.searchResults }
						suggestionUrl={ this.props.searchSuggestionUrl }
						clid={ this.props.clid }
						searchName={ this.props.searchName }
						onClearSearch={ this.props.onClearSearch }
					/>
					}
					<div className="add-buttons">
					{ 
						Object.keys(this.props.spp).length > 0 && 
						<div className="container row">
						 {Object.keys(this.props.spp).map((label) => {
								//console.log(label);
								//let obj = {section:"spp", name:label, value:this.props.spp[label]};
								//console.log(this.props.spp[label] );
								return (
								
									<input 
										key={ this.props.spp[label] }
										type="button"
										className="btn btn-primary"
										name={ label }
										value={ 'Add ' + label + '?' }	
										action={ 'add' }
										tid={ this.props.spp[label] } 
										section={this.state.section}						
										onClick={ this.handleAddSPP.bind(this)} 
									></input>
								)

							})}
						</div>
					}
						</div>
				<div className="msg" id="spp-msg"></div>
			</div>
		</div>
    );
  }
}

SideBarVendor.defaultProps = {
  searchText: '',
  searchSuggestionUrl: '',
  spp: [],
  //onPlantFeaturesChanged: () => {},
  //onGrowthMaintenanceChanged: () => {},
  //onBeyondGardenChanged: () => {}
};

export default SideBarVendor;
