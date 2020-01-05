import React from "react";
import httpGet from "./httpGet";

/**
 * Sidebar 'plant search' button
 */
function SearchButton(props) {
  return (
    <button
      className="my-auto btn-search" style={ props.style }
      onClick={ props.isLoading ? () => {} : props.onClick}>
      <img
        style={{display: props.isLoading ? "none" : "block"}}
        src={`${props.clientRoot}/images/garden/search-green.png`}
        alt="search plants"/>
      <div
        className="mx-auto text-success spinner-border spinner-border-sm"
        style={{display: props.isLoading ? "block" : "none"}}
        role="status"
        aria-hidden="true"/>
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
      this.props.onSearch(this.props.textValue);
    } else {
      this.onSuggestionsRequested();
    }
  }

  onSuggestionsRequested() {
    if (this.props.suggestionUrl !== '') {
      httpGet(`${this.props.suggestionUrl}?q=${this.props.textValue}`).then((res) => {
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
      <div className="search-widget dropdown input-group w-100 mb-4 p-2" style={ this.props.style }>
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
                  onClick={ (e) => { e.preventDefault(); e.stopPropagation(); this.props.onSearch(s.value); } }
                  className="dropdown-item"
                  href="#"
                >
                  { s.text }
                </a>
              )
            })
          }
        </div>
        <SearchButton
          onClick={ () => this.props.onSearch(this.props.textValue) }
          isLoading={this.props.isLoading}
          style={ this.props.buttonStyle }
          clientRoot={ this.props.clientRoot }
        />
      </div>
    );
  }
}

SearchWidget.defaultProps = {
  onSearch: () => {},
  buttonStyle: {},
  clientRoot: '',
  suggestionUrl: ''
};

export default SearchWidget;
