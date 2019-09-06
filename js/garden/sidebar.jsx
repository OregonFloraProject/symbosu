"use strict";

const helpButtonStyle = {
  float: "right",
  padding: 0,
  marginLeft: "auto",
  borderRadius: "50%",
  background: "#5FB021",
};

const searchButtonStyle = {
  width: "2em",
  height: "2em",
  padding: "0.3em",
  marginLeft: "0.5em",
  borderRadius: "50%",
  background: "rgba(255, 255, 255, 0.5)"
};

function SideBarHeading(props) {
  return (
    <div style={{color: "black"}}>
      <div className="mb-1" style={{color: "inherit"}}>
        <h3 className="font-weight-bold d-inline">Search for plants</h3>
        <button style={ helpButtonStyle }>
          <img
            style={{ width: "1.25em" }}
            alt="help"
            src="/images/garden/help.png"/>
        </button>
      </div>
      <p>
        Start applying characteristics, and the matching plants will appear at
        right.
      </p>
    </div>
  );
}

function SearchButton(props) {
  return (
    <button className="my-auto" style={ searchButtonStyle } onClick={ props.onClick }>
      <img
        style={{ display: props.showLoading ? "none" : "block" }}
        src="/images/garden/search-green.png"
        alt="search plants"/>
      <div
        className="mx-auto text-success spinner-border spinner-border-sm"
        style={{ display: props.showLoading ? "block" : "none" }}
        role="status"
        aria-hidden="true"/>
    </button>
  );
}


function SideBarSearch(props) {
  return (
    <div className="input-group w-100 mb-4 p-2">
      <input
        name="search"
        type="text"
        placeholder="Search plants by name"
        className="form-control search-param"
        onChange={ props.onChange } />
      <SearchButton onClick={ props.onClick } showLoading={ props.isLoading } />
    </div>
  );
}

class SideBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      searchText: '',
      isLoading: false
    }
  }

  onSearchTextChanged() {
    this.setState({ searchText: event.target.value });
  }

  onSearch() {
    this.setState({ isLoading: true });

    // TODO: Search!
    console.log("The current search value is '" + this.state.searchText + "'");
    setTimeout(() => { this.setState({ isLoading: false }) }, 3000);
  }

  render() {
    return (
      <div id="sidebar" className="col-sm-3 m-2 p-5 rounded-border" style={{ background: "#DFEFD3", minHeight: "20em" }}>
        <SideBarHeading />
        <SideBarSearch
          onChange={ this.onSearchTextChanged.bind(this) }
          onClick={ this.onSearch.bind(this) }
          isLoading={ this.state.isLoading }
        />
      </div>
    );
  }
}