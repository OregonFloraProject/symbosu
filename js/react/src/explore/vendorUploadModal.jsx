import React, { Component } from "react";
import ReactDOM from "react-dom";
import Papa from "papaparse"; 

import {VendorUploadContainer} from "../common/searchResults.jsx";
//import httpGet from "../common/httpGet.js";
import Loading from "../common/loading.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import {faTimesCircle } from '@fortawesome/free-solid-svg-icons'
library.add(  faTimesCircle );

export default class vendorUploadModal extends Component {
  constructor(props) {
    super(props);
    this.state = {
    	previewReady: false,
    	errorMsg: '',
      //isSearching: false,
    };

		this.onToggleUploadClick = this.props.onToggleUploadClick.bind(this);
    this.setUploadUpdating = this.props.setUploadUpdating.bind(this);
    this.handleUpload = this.handleUpload.bind(this);
    this.storeUpload = this.storeUpload.bind(this);
    this.processUpload = this.processUpload.bind(this);
    this.handleCancel = this.handleCancel.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  handleUpload = (file) => {
  	this.setState({ errorMsg: ''});
  	let isCSV = true;
  	let mimeTypes = 'text/csv';//text/csv, .csv, application/vnd.ms-excel
		if (window.Blob) {
			if (mimeTypes.indexOf(file.type) == -1) {
				isCSV = false;
			}
		}
		if (isCSV) {
			const config = {
				skipEmptyLines: true,
				header: true,
				transformHeader:function(h) {//transformHeader is not well-documented, and only works with this anonymous function, not with a named function
					let ret = h;
					let acceptable = {
						'sciname' : ['sciname','scientificname','sci name','scientific name','sci_name','scientific_name','sci-name','scientific-name'],
						'notes' : ['notes','mynotes','my-notes','my_notes']
					};

					Object.keys(acceptable).forEach((columnName) => {
						if (acceptable[columnName].indexOf(h.toLowerCase()) > -1) {
							ret = columnName;
						}
					});
					return ret;
				},
				complete: results => {
					this.storeUpload(results);
					this.setState({ previewReady: true });
				},
			};
			Papa.parse(file, config);
		}else{
  		this.setState({ errorMsg: 'Your file must be a .csv file.'});
		}
  }
  storeUpload = (res) => {
  	//console.log(res.meta);
		if (res.meta.fields.indexOf('sciname') != -1) {//transformed above, so this works
  		this.setState({ uploadedFile: res.data });
		}else{
  		this.setState({ errorMsg: 'Your CSV file must contain a header row with column headings "ScientificName" (required) and "Notes" (optional).'});
		}
  }
  processUpload() {
  	if (this.state.uploadedFile) {
			this.props.previewSPPlist(this.state.uploadedFile);
		}else{
			console.log('file is null');
		}
  }
  handleCancel() {
  	this.props.clearUpload('cancel');
  }
  handleSubmit() {
		this.props.updateSPPlist();	
  }
  /*
  componentDidMount() {
		if (this.props.clid > -1) {
			httpGet(`${this.props.clientRoot}/checklists/rpc/api.php?clid=${this.props.clid}&pid=${this.props.pid}`)
				.then((res) => {
					// /checklists/rpc/api.php?clid=3&pid=1
					res = JSON.parse(res);

					this.setState({
						title: res.title,
      			intro: res.intro,
			      iconUrl: res.iconUrl,
						authors: res.authors,
						abstract: res.abstract,
						totals: res.totals,
					});
				})
				.catch((err) => {
					//window.location = "/";
					console.error(err);
				})
				.finally(() => {
					this.setState({ isLoading: false });
				});
			}
  }
*/


  render() {
    if(!this.props.show) {
      return null;
    }
    let disabledStatus = (this.state.previewReady? '' : 'disabled');
    return (
    
    <div className="modal-backdrop vendor-upload-modal">
      <div className="modal-content container mx-auto">
			<div className="wrapper vendor-upload-wrapper">
				<div className="" style={{ position: "relative", minHeight: "45em", maxWidth: "100%", overflowX: "hidden" }}>
		
					<div className="row" style={{ maxWidth: "100%", overflowX: "hidden"}}>
										
							<div className="mask">&nbsp;</div>
							
							<div className="vendor-upload-header">
								<h1>Inventory Manager</h1>
								<div className="close-modal" onClick={this.props.onToggleUploadClick}>
									<FontAwesomeIcon icon="times-circle" size="2x"/>
								</div>
								{
									this.props.uploadResponse && this.props.uploadResponse.status == 'success' ?
										(
										<div className="file-upload submit">
											<div className="download-link">
												{
													this.props.uploadResponse.csvURL &&
													<div>
														<span>I want to make edits</span><span>as a .csv file.</span>
														<a href={this.props.uploadResponse.csvURL}
															download
															className="btn btn-primary"
														>Download these results.</a>
													</div>
												}
											</div>
											<div className="submit-list">
													<div>
														<span>This looks good.</span>
														<button 
															type="button"
															onClick={this.handleSubmit}
															className="btn btn-primary"
														>Submit this list.</button>
													</div>
											</div>
											<div className="cancel">
												<div>
													<span>Start over.</span>
													<button 
														type="button"
														className="btn btn-primary"
														onClick={this.handleCancel} 
													>Cancel</button>
												</div>
											</div>
										</div>
										)
										:								
										(
										<div className="file-upload preview">				
											<div className="download-link">
												{
													<div>
														<div>I want to download my current list.</div>
														<a href={this.props.exportUrlCsv}
															download
															className="btn btn-primary"
														>Download</a>
													</div>
												}
											</div>
											<div className="file-upload-form">
												<span>I want to upload a new list.</span>
												<form>		
													<input 
														name="vendor-upload"
														type="file" 
														accept='text/csv, application/vnd.ms-excel'
														onChange={(e) => {this.handleUpload(e.target.files[0])}}
													/>
													<button 
														type="button"
														className="btn btn-primary"
														onClick={this.processUpload}
														disabled={disabledStatus}
													>Upload and Preview</button>
												</form>
											</div>
										</div>
										)
									}
							</div>
						
							<div className="col-12 vendor-upload-main">
							{
									this.state.errorMsg && 
									
									<div className="error">Error: {this.state.errorMsg}</div>
									
							}
							<VendorUploadContainer
								uploadResponse={ this.props.uploadResponse }
								isSearching={this.props.isUploadUpdating}
								clientRoot={this.props.clientRoot}					
							/>
									
							</div>							
						</div>			
				</div>
			</div>
		</div>
	</div>
    );
  }
}
vendorUploadModal.defaultProps = {
  //clid: -1,
  //pid: -1,
  clientRoot: '',
  onToggleUploadClick: () => {},
  updateSPPlist: () => {},
  previewSPPlist: () => {},
  clearUpload: () => {},
  uploadResponse: {status: '',results: {}, csvURL: ''},
  show: false
  
};

