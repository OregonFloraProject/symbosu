import ReactDOM from 'react-dom';
import React from 'react';
import { getUrlQueryParams } from '../common/queryParams.js';
import UnifiedTaxaCore from './unified/UnifiedTaxaCore.jsx';
import UnifiedTaxaGarden from './unified/UnifiedTaxaGarden.jsx';
import UnifiedTaxaRare from './unified/UnifiedTaxaRare.jsx';

const pathname = window.location.pathname;
let Variant;
if (pathname.includes('garden.php')) {
  Variant = UnifiedTaxaGarden;
} else if (pathname.includes('rare.php')) {
  Variant = UnifiedTaxaRare;
} else {
  Variant = UnifiedTaxaCore;
}

const mountIds = ['react-taxa-app', 'react-taxa-garden-app', 'react-taxa-rare-app'];
let domContainer = null;
mountIds.some((id) => {
  const el = document.getElementById(id);
  if (el) {
    domContainer = el;
    return true;
  }
  return false;
});

if (domContainer) {
  const headerContainer = document.getElementById('react-header');
  const dataProps = JSON.parse(headerContainer.getAttribute('data-props'));
  const queryParams = getUrlQueryParams(window.location.search);

  // Use both taxon and tid (symbiota-light) to denote the taxon
  if (Variant !== UnifiedTaxaGarden && queryParams.tid) {
    queryParams.taxon = queryParams.tid;
  }

  if (queryParams.search) {
    window.location = `./search.php?search=${encodeURIComponent(queryParams.search)}`;
  } else if (queryParams.taxon && parseInt(queryParams.taxon, 10) !== -1) {
    const variantProps = {
      tid: queryParams.taxon,
      defaultTitle: dataProps['defaultTitle'],
      clientRoot: dataProps['clientRoot'],
    };
    if (Variant !== UnifiedTaxaGarden) {
      variantProps.synonym = queryParams.synonym - 0;
    }
    ReactDOM.render(<Variant {...variantProps} />, domContainer);
  } else {
    window.location = '/';
  }
}
