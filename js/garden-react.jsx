"use strict";

function SearchResult(props) {
  return (
    <div className="card col-sm mx-1">
      <a href={props.plantLink}>
        <img className="card-img-top search-result-img" src={props.plantImage} alt={props.plantNameSci} />
        <div className="card-body">
          <h5 className="card-title">{props.plantNameCommon !== null ? props.plantNameCommon : props.plantNameSci}</h5>
          <p className="card-text">{props.plantNameSci}</p>
        </div>
      </a>
    </div>
  );
}

class SearchResultContainer extends React.Component {
  constructor(props) {
    super(props);

    // Store data as rows of 5
    const rows = [];
    let currentRow = [];
    for (let i = 0; i < searchResults.length; i++) {
      if (i % 5 === 0 && i !== 0) {
        rows.push(currentRow);
        currentRow = [searchResults[i]];
      } else {
        currentRow.push(searchResults[i]);
      }
    }

    this.state = {
      resultRows: rows
    };
    console.log(this.state.resultRows);
  }

  setSearchResultsArray(jsonArray) {
    const rows = [];
    let currentRow = [];
    for (let i = 0; i < jsonArray.length; i++) {
      if (i % 5 === 0 && i !== 0) {
        rows.push(currentRow);
        currentRow = [jsonArray[i]];
      } else {
        currentRow.push(jsonArray[i]);
      }
    }

    this.setState({ resultRows: rows });
  }

  renderSearchResult(row, col) {
    return (
      <SearchResult
        key={ this.state.resultRows[row][col].tid }
        plantLink={ "../taxa/garden.php?taxon=" + this.state.resultRows[row][col].tid }
        plantImage={ this.state.resultRows[row][col].image }
        plantNameCommon={ this.state.resultRows[row][col].vernacularname }
        plantNameSci={ this.state.resultRows[row][col].sciname }
      />
    );
  }

  renderRow(i) {
    const results = this.state.resultRows[i];
    return (
      <div className="row my-2">
        { results.map((obj, idx) => { return this.renderSearchResult(i, idx) }) }
      </div>
    );
  }

  render() {
    return (
      <div className="container-fluid">
        { this.state.resultRows.map((obj, idx) => { return this.renderRow(idx); }) }
      </div>
    );
  }
}

const domContainer = document.getElementById("results");
ReactDOM.render(<SearchResultContainer />, domContainer);
