import React from "react";
import httpGet from "./httpGet";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTimesCircle } from '@fortawesome/free-solid-svg-icons'
library.add(faTimesCircle)
/**
 * Sidebar 'plant search' button
 */
function SearchButton(props) {
	let imgSrc = `${props.clientRoot}/images/icons/home-mag-glass-2x.png`;//header = default
	if (props.location == 'home-main') {
		imgSrc = `${props.clientRoot}/images/icons/home-mag-glass-2x.png`;
	}
	
  return (
    <button
      className="btn-search" style={ props.style }
      onClick={ props.isLoading ? () => {} : props.onClick}>
      {/*<img
        style={{display: props.isLoading ? "none" : "block"}}
        src={imgSrc}
        alt="search plants"/>
      <div
        className="mx-auto text-success spinner-border spinner-border-sm"
        style={{display: props.isLoading ? "block" : "none"}}
        role="status"
        aria-hidden="true"/>*/}
    </button>
  );
}

/**
 * Sidebar 'plant search' text field & button
 */
export class SearchWidget extends React.Component {
  static enterKey = 13;

  constructor(props) {
    super(props);
    this.state = {
      suggestions: []
    };

    this.onKeyUp = this.onKeyUp.bind(this);
    this.onSuggestionsRequested = this.onSuggestionsRequested.bind(this);
  }

  onTextValueChanged(e) {
    this.setState({ textValue: e.target.value });
  }

  onKeyUp(event) {
    if (this.props.textValue === '') {
      this.setState({ suggestions: [] });
    } else if ((event.which || event.keyCode) === SearchWidget.enterKey && !this.props.isLoading) {
      this.props.onSearch({ text: this.props.textValue, value: -1 });
    } else {
      this.onSuggestionsRequested();
    }
  }

  onSuggestionsRequested() {
    if (this.props.suggestionUrl !== '') {
    	let suggestionUrl = `${this.props.suggestionUrl}?q=${this.props.textValue}`; 
    	if (this.props.clid) {
    		suggestionUrl += "&clid=" + this.props.clid;
    	}
    	if (this.props.dynclid) {
    		suggestionUrl += "&dynclid=" + this.props.dynclid;
    	}
    	if (this.props.searchName) {
    		suggestionUrl += "&name=" + this.props.searchName;
    	}
    	if (this.props.searchResults && this.props.searchResults.taxonSort.length > 0) {
    		let tids = this.props.searchResults.taxonSort.map((key) => {
    				//console.log(key);
						//return { key: filterKey, val: this.state.filters[filterKey] }
						return key.tid;
				})
				if (tids.length > 0) {
    			suggestionUrl += "&omit=" + tids.join(',');
    			///garden/rpc/autofillsearch.php?q=ju&clid=54&dynclid=-1&name=sciname&omit=2400,2404,2705,2835,3212,4274,4546,6450
				}
    	}
  		httpGet(suggestionUrl).then((res) => {
        return JSON.parse(res);
      }).catch((err) => {
        console.error(err);
      }).then((suggestions) => {
        this.setState({ suggestions: suggestions });
      });
    }
  }

  render() {
    return (
      <div className="search-widget" style={ this.props.style }>
        <input
          name="search"
          type="text"
          className="form-control"
          data-toggle="dropdown"
          autoComplete="off"
          onKeyUp={ this.onKeyUp }
          placeholder={ this.props.placeholder }
          onChange={ this.props.onTextValueChanged }
          value={ this.props.textValue }
        />
        <div className="dropdown-menu" style={{ display: (Object.keys(this.state.suggestions).length === 0 ? " none" : "") }}>
          {
            this.state.suggestions.map((s) => {
              return (
                <a
                  key={ s.text }
                  onClick={ (e) => { e.preventDefault(); e.stopPropagation(); this.props.onSearch(s); } }
                  className="dropdown-item"
                  href="#"
                  target="_blank"
                >
                  { s.text }
                </a>
              )
            })
          }
        </div>
        { this.props.textValue.length > 0 &&
        <div className="clear-text">
        		<FontAwesomeIcon icon="times-circle" size="2x"
							onClick={ this.props.onClearSearch }
						/>
        </div>
        }
        <SearchButton
          onClick={ () => this.props.onSearch({ text: this.props.textValue }) }
          isLoading={this.props.isLoading}
          style={ this.props.buttonStyle }
          location={ this.props.location }
          clientRoot={ this.props.clientRoot }
        />
      </div>
    );
  }
}

SearchWidget.defaultProps = {
  onSearch: () => {},
  buttonStyle: {},
  location: {},
  clientRoot: '',
  suggestionUrl: '',
  clid: -1,
  dynclid: -1,
  searchName: 'sciname'
};

export default SearchWidget;
