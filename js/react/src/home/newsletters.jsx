import ReactDOM from "react-dom";
import React from "react";
import Slider from "react-slick";
import httpGet from "../common/httpGet.js";
import PageHeader from "../common/pageHeader.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faChevronDown, faChevronUp } from '@fortawesome/free-solid-svg-icons'
library.add( faChevronDown, faChevronUp)

class Newsletters extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
			issues: []
    };
  }
	toggleItemDisplay = (index) => {
		let newArr = this.state.issues;
		let newVal = 'default';
		if (this.state.issues[index].display == 'default') {
			newVal = 'expanded';
		} 
		newArr[index].display = newVal;
		this.setState({
			issues: newArr
		});

  }
  componentDidMount() {

		httpGet(`./rpc/api.php`)
			.then((res) => {
				res = JSON.parse(res);//in this case, passed as object to preserve sort and then convert to array
				let issues = [];
				//convert to array
				for (var i in res) {
					issues.push(res[i]);
				}
				issues.map((issue,index) => {
					issues[index].display = "default";
				})
				this.setState({
					issues: issues
				});
				const pageTitle = document.getElementsByTagName("title")[0];
				pageTitle.innerHTML = `${pageTitle.innerHTML} Newsletter Archive`;
			})
			.catch((err) => {
				// TODO: Something's wrong
				console.error(err);
			});
    
  }//componentDidMount
  render() {

		
    return (
    <div className="wrapper">
			<div className="page-header">
				<PageHeader bgClass="title-blueberry" title={ 'Oregon Flora Newsletters' } />
      </div>
      <div className="container newsletters" style={{ minHeight: "45em" }}>

        <div className="row">
          <div className="col">
            <h2>Discover interviews with Oregon botanical notables, maps, taxonomy, and stories about the plants in our lives in our archives. 
            The Oregon Flora Newsletter was published from 1995–2012.</h2>
          </div>
        </div>
        <div className="row">
          <div className="col">
            <p className="instructions">To download an issue, click on the PDF icon. To preview an issue’s contents, click the arrow.</p>
					 </div>
        </div>

        <div className="row">
          <div className="col newsletter-list">
            {
									this.state.issues.map((issue,index) => {
										return (					
											<div key={index} className="issue-item">
												{
														<div className="issue-default">
															<div className="issue-header">
																<div className="issue-link">
																		<a href={ '/ofn/' +  issue[0].pdf + '.pdf' } target="_blank" rel="noreferrer"><img src={ `${this.props.clientRoot}/images/Adobe_PDF_file_icon_32x32.png` } /></a>
                                </div>
																<div className="issue-title">
																	<h3>
																		{'Vol. ' + issue[0].volume + ' Iss. ' + issue[0].issue}
																	</h3>
                                </div>
                                { issue.display == 'default' &&
																	<div className="more more-less" onClick={() => this.toggleItemDisplay(index)}>
																			<span>Issue Contents </span>
																			<FontAwesomeIcon icon="chevron-down" size="2x" />
																	</div>
																}
																{ issue.display == 'expanded' &&
																	<div className="less more-less" onClick={() => this.toggleItemDisplay(index)}>
																			<FontAwesomeIcon icon="chevron-up" size="2x" />
																	</div>
																}
															</div>
														</div>
													
												}
												{issue.display == 'expanded' && 
														<div className="issue-expanded">
																	{issue.map((article,idx) => {
																		return (
																				<div key={idx} className="row">
																						<div className="issue-title col-sm-8 pr-0" dangerouslySetInnerHTML={{__html: article.title}} >
																						
																						</div>
																						<div className="issue-author col-sm pl-0">
																							{ article.authors }
																						</div>
																				</div>
																		);
																	})}
														</div>
												}
											</div>
										);
									})
								}
					 </div>
        </div>
        
      </div>
    </div>
    );
  }
}


const headerContainer = document.getElementById("react-header");
const dataProps = JSON.parse(headerContainer.getAttribute("data-props"));
const domContainer = document.getElementById("react-newsletters-app");
ReactDOM.render(
	<Newsletters clientRoot={ dataProps["clientRoot"] } />,
	domContainer
);
