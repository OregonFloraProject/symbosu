import ReactDOM from "react-dom";
import React from "react";
import httpGet from "../common/httpGet.js";
import { getUrlQueryParams } from "../common/queryParams.js";


class TaxaSearchResults extends React.Component {
  render() {
    return (
      <pre>
        { JSON.stringify(this.props.results, null, 2) }
      </pre>
    );
  }
}

TaxaSearchResults.defaultProps = {
  results: []
};

const domContainer = document.getElementById("react-taxa-search-app");
const queryParams = getUrlQueryParams(window.location.search);
if (queryParams.search) {
  httpGet(`./rpc/api.php?search=${queryParams.search}`).then((res) => {
    res = JSON.parse(res);
    if (res.length === 1) {
      window.location = `./index.php?taxon=${res[0].tid}`

    } else if (res.length > 1) {
      ReactDOM.render(<TaxaSearchResults results={ res } />, domContainer);

    } else {
      window.location = "/";

    }
  }).catch((err) => {
    console.error(err);
  })
} else {
  window.location = "/";
}