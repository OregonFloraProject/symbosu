import ReactDOM from "react-dom";
import React from "react";
import httpGet from "../common/httpGet.js";
import { getUrlQueryParams } from "../common/queryParams.js";


const domContainer = document.getElementById("react-taxa-search-app");
const queryParams = getUrlQueryParams(window.location.search);
if (queryParams.search) {
  httpGet(`./rpc/api.php?search=${queryParams.search}`).then((res) => {
    res = JSON.parse(res);
    if (res.length === 1) {
      window.location = `./index.php?taxon=${res[0].tid}`
    } else if (res.length > 1) {
      // TODO: Search results
      console.log(res);
    }
  }).catch((err) => {
    console.error(err);
  })
} else {
  window.location = "/";
}