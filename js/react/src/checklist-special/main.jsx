'use strict';

import React from 'react';
import ReactDOM from 'react-dom';

import AboutDropdown from './components/aboutDropdown.jsx';
import CannedSearchContainer from './components/cannedSearches.jsx';
import GardenCredits from './components/gardenCredits.jsx';
import InfographicDropdownGarden from './components/infographicDropdownGarden.jsx';
import InfographicDropdownRare from './components/infographicDropdownRare.jsx';
import SortOptions from './components/sortOptions.jsx';
import SideBar from '../common/filterSidebar.jsx';
import { CardSearchContainer } from '../common/searchResults.jsx';
import ViewOpts from '../common/viewOpts.jsx';
import { getUrlQueryParams } from '../common/queryParams.js';
import { sortByTaxon } from '../common/taxaUtils';
import Loading from '../common/loading.jsx';
import FilterModal from '../common/filterModal.jsx';
import { addGlossaryTooltips } from '../common/glossary.js';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faChevronDown, faChevronUp } from '@fortawesome/free-solid-svg-icons';
library.add(faChevronDown, faChevronUp);

const MOBILE_BREAKPOINT = 576;

function capitalize(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function getIncludedHeritageLists(characteristics) {
  const heritageChar = characteristics
    .map((group) => Object.values(group.characters).find((char) => char.cid === 244))
    .find((group) => group !== undefined);
  if (!heritageChar || !heritageChar.states || !heritageChar.states.length) {
    return [];
  }

  return heritageChar.states.map((state) => capitalize(state.charstatename));
}

function getDefaultSearchResultsMessage(numTaxa, characteristics, pageType) {
  if (pageType === 'garden') {
    return 'No filters applied, so showing all native plants';
  } else if (pageType === 'rare') {
    const listsOfTaxa = getIncludedHeritageLists(characteristics);
    if (listsOfTaxa && listsOfTaxa.length) {
      return `No filters applied, so showing ${numTaxa} ${listsOfTaxa.join(' and ')} taxa`;
    }
  }
  return 'No filters applied';
}

function getApiUrl(pageType, clientRoot) {
  if (pageType === 'garden') {
    return `${clientRoot}/garden/rpc/api.php`;
  } else if (pageType === 'rare') {
    return `${clientRoot}/rare/rpc/api.php`;
  }
  throw new Error('Tried to render SpecialChecklistApp with bad pageType');
}

function getDefaultSortBy(pageType) {
  return pageType === 'garden' ? 'vernacularName' : 'sciName';
}

function getInfographicDropdownComponent(pageType) {
  return pageType === 'garden' ? InfographicDropdownGarden : InfographicDropdownRare;
}

function getDefaultClid(pageType) {
  if (pageType === 'garden') {
    return 54;
  } else if (pageType === 'rare') {
    return 14948;
  }
  throw new Error('Tried to render SpecialChecklistApp with bad pageType');
}

function getDefaultPid(pageType) {
  if (pageType === 'garden') {
    return 3;
  } else if (pageType === 'rare') {
    return -1;
  }
  throw new Error('Tried to render SpecialChecklistApp with bad pageType');
}

class SpecialChecklistApp extends React.Component {
  constructor(props) {
    super(props);
    const queryParams = getUrlQueryParams(window.location.search);
    this.state = {
      isLoading: true,
      isSearching: false,
      isMobile: false,
      showFilterModal: false,
      searchInit: false,
      clid: getDefaultClid(props.pageType),
      pid: getDefaultPid(props.pageType),
      projName: '',
      currentTids: [],
      filters: {
        searchText: 'search' in queryParams ? queryParams['search'] : ViewOpts.DEFAULT_SEARCH_TEXT,
        attrs: {},
        ranges: {},
        checklist: { clid: -1, name: '' },
      },
      searchResults: { familySort: [], taxonSort: [] },
      characteristics: [],
      cannedSearches: [],
      sortBy: 'sortBy' in queryParams ? queryParams['sortBy'] : getDefaultSortBy(this.props.pageType),
      viewType: 'viewType' in queryParams ? queryParams['viewType'] : 'grid',
      apiUrl: '',
      slideshowCount: 0,
      glossary: [],
      aboutGuideExpanded: false,
      apiError: false,
    };
  }

  componentDidMount() {
    const fetchData = async () => {
      try {
        const apiUrl = getApiUrl(this.props.pageType, this.props.clientRoot);
        const url =
          this.props.pageType === 'garden' ? `${apiUrl}?clid=${this.state.clid}&pid=${this.state.pid}` : apiUrl;
        const res = await fetch(url);
        const data = await res.json();
        let taxa = '';
        let tids = [];
        if (data && data.taxa) {
          taxa = { familySort: data.taxa, taxonSort: sortByTaxon(data.taxa, this.state.sortBy) };
          tids = data.tids; //unordered
        }

        this.setState({
          projName: data.projName,
          searchResults: taxa, //always the full garden checklist
          isMobile: window.innerWidth < MOBILE_BREAKPOINT,
          apiUrl,
          currentTids: tids,
          characteristics: data.characteristics,
        });

        const pageTitle = document.getElementsByTagName('title')[0];
        pageTitle.innerHTML = `${pageTitle.innerHTML} - ${data.title.replace('Rare Plant Guide ', '')}`;

        return data;
      } finally {
        this.setState({ isLoading: false });
      }
    };

    const fetchCannedSearchData = async () => {
      if (this.props.pageType === 'garden') {
        try {
          const url = `${this.props.clientRoot}/garden/rpc/api.php?canned=true`;
          const res = await fetch(url);
          const data = await res.json();
          this.setState({ cannedSearches: data });

          this.updateViewport();
          window.addEventListener('resize', this.updateViewport);
        } catch (err) {
          console.error(err);
        }
      }
    };

    const fetchGlossary = async () => {
      try {
        const res = await fetch('../glossary/rpc/getterms.php');
        const glossary = await res.json();
        this.setState({ glossary });
        return glossary;
      } catch (err) {
        // just log this error and don't do anything for now, since the glossary isn't strictly
        // necessary for the functioning of the page
        console.error(err);
      }
    };

    const fetchDataAndProcessGlossary = async () => {
      try {
        // wait for all the data to be fetched
        // use return values because the state may not update synchronously
        const [data, glossary] = await Promise.all([fetchData(), fetchGlossary(), fetchCannedSearchData()]);
        if (!data.characteristics) return;
        const characteristics = data.characteristics.map((firstLevel) => ({
          ...firstLevel,
          characters: firstLevel.characters.map((char) => ({
            ...char,
            charname: addGlossaryTooltips(char.charname, glossary),
            states:
              char.display || char.cid === 209 // don't add tooltips to nursery names
                ? char.states
                : char.states.map((state) => ({
                    ...state,
                    charstatename: addGlossaryTooltips(state.charstatename, glossary),
                  })),
          })),
        }));
        this.setState({ characteristics });

        const queryParams = getUrlQueryParams(window.location.search);
        if (queryParams.clid) {
          this.onCannedFilter(queryParams.clid);
        }
      } catch (err) {
        console.error(err);
        this.setState({ apiError: true });
      }
    };

    fetchDataAndProcessGlossary();
  }

  /**
   * SECTION: query
   */
  catchQuery = () => {
    let doConfirm = false;
    if (this.state.isMobile && this.getFilterCount() > 0) {
      doConfirm = true;
    }
    if (doConfirm) {
      this.setFilterModal(true);
    } else {
      this.doQuery();
    }
  };
  doQuery = async () => {
    this.setState({
      //isLoading: true,
      isSearching: true,
    });
    if (this.getFilterCount() > 0) {
      const identParams = new URLSearchParams();
      if (this.state.filters.checklist && this.state.filters.checklist['clid'] > -1) {
        identParams.append('clid', this.state.filters.checklist['clid']);
      } else if (this.state.clid > -1) {
        identParams.append('clid', this.state.clid);
      }
      if (this.state.pid > -1) {
        identParams.append('pid', this.state.pid);
      }
      if (this.state.filters.searchText) {
        identParams.append('search', this.state.filters.searchText);
      }
      Object.keys(this.state.filters.attrs).forEach((idx) => {
        identParams.append('attr[]', idx);
      });
      Object.values(this.state.filters.ranges).forEach((range) => {
        range.keys.forEach((key) => identParams.append('range[]', key));
      });

      const url = this.state.apiUrl + '?' + identParams.toString();

      try {
        const res = await fetch(url);
        this.onSearchResults((await res.json()).tids);
        this.setState({ apiError: false });
      } catch (err) {
        console.error(err);
        this.setState({ apiError: true });
      } finally {
        this.setState({ isSearching: false, searchInit: true });
        this.mobileScrollToResults();
      }
    } else {
      //reset
      this.resetTaxaResults();
      this.setState({ isSearching: false });
      this.mobileScrollToResults();
    }
  };
  doConfirm = () => {
    this.setFilterModal(false);
    this.doQuery();
  };

  /**
   * SECTION: mobile scroll and modal
   */
  mobileScrollToResults = () => {
    if (this.state.isMobile && this.getFilterCount() > 0) {
      let section = document.getElementById('results-section');
      let yOffset = 60;
      document.getElementById('results-section').scrollIntoView();
      const newY = section.getBoundingClientRect().top + window.pageYOffset - yOffset;
      window.scrollTo({ top: newY, behavior: 'smooth' });
    }
  };
  mobileScrollToFilters = () => {
    let section = document.getElementById('filter-section');
    let yOffset = 60;
    document.getElementById('filter-section').scrollIntoView();
    const newY = section.getBoundingClientRect().top + window.pageYOffset - yOffset;
    window.scrollTo({ top: newY, behavior: 'smooth' });
  };
  setFilterModal = (val) => {
    this.setState({ showFilterModal: !!val });
  };

  /**
   * SECTION: sort and view
   */
  onSortByChanged = (type) => {
    const familySort = this.state.searchResults.familySort;
    this.setState(
      {
        sortBy: type == 'sciName' ? 'sciName' : 'vernacularName',
      },
      function () {
        const taxonSort = sortByTaxon(this.state.searchResults.taxonSort, this.state.sortBy);
        this.setState({
          searchResults: { familySort: familySort, taxonSort: taxonSort },
        });
      },
    );
  };
  onViewTypeChanged = (type) => {
    this.setState({ viewType: type });
  };

  /**
   * SECTION: search text
   */
  onSearchTextChanged = (e) => {
    this.setState({ filters: Object.assign({}, this.state.filters, { searchText: e.target.value }) });
  };
  // On search start
  onSearch = (searchObj) => {
    this.setState(
      {
        //searchText: searchObj.text,
        filters: Object.assign({}, this.state.filters, { searchText: searchObj.text }),
      },
      function () {
        this.catchQuery();
      },
    );
  };
  // On search end
  onSearchResults = (tids) => {
    this.setState({ currentTids: tids });
  };
  clearTextSearch = () => {
    this.setState(
      {
        filters: Object.assign({}, this.state.filters, { searchText: ViewOpts.DEFAULT_SEARCH_TEXT }),
      },
      function () {
        this.catchQuery();
      },
    );
  };

  /**
   * SECTION: charstate filters
   */
  onAttrChanged = (featureKey, featureName, featureVal) => {
    /* 710-1, simple, on */
    let filters = this.state.filters;

    if (featureVal == 'off') {
      delete filters.attrs[featureKey];
    } else {
      filters.attrs[featureKey] = featureName;
    }

    this.setState(
      {
        filters: Object.assign({}, this.state.filters, { attrs: filters.attrs }),
      },
      function () {
        this.catchQuery();
      },
    );
  };
  onRangeChanged = (cid, featureObj) => {
    // remove previous range filters matching this cid
    const { [cid]: current, ...ranges } = this.state.filters.ranges;

    if (featureObj) {
      ranges[cid] = featureObj;
    }

    this.setState(
      {
        filters: { ...this.state.filters, ranges },
      },
      () => this.catchQuery(),
    );
  };
  onGroupFilterClicked = (children) => {
    let nurseries = this.state.characteristics[5].characters[1]; //as hardcoded in garden/rpc/api.php
    nurseries.states.map((attr) => {
      let val = 'off';
      if (children.indexOf(attr.cs) != -1) {
        val = 'on';
      }
      this.onAttrChanged(attr.cid + '-' + attr.cs, attr.charstatename, val);
    });
  };

  /**
   * SECTION: canned searches
   */
  onCannedFilter = (checklistItem) => {
    /*accepts either object or clid*/
    let checklist = null;
    if (typeof checklistItem === 'object') {
      checklist = checklistItem;
    } else {
      checklist = this.getCannedByClid(checklistItem);
    }

    if (checklist !== null) {
      this.setState({ filters: { ...this.state.filters, checklist } }, () => this.catchQuery());
    }
  };
  getCannedByClid = (clid) => {
    for (let canned of this.state.cannedSearches) {
      if (canned.clid == clid) {
        return canned;
      }
    }
    return null;
  };
  resetCanned = () => {
    this.setState({ filters: { ...this.state.filters, checklist: { clid: -1, name: '' } } }, () => this.catchQuery());
  };
  updateViewport = () => {
    let newSlideshowCount = 4;
    if (window.innerWidth < 1200) {
      newSlideshowCount = 3;
    }
    if (window.innerWidth < 992) {
      newSlideshowCount = 2;
    }
    this.setState({ slideshowCount: newSlideshowCount });
  };

  /**
   * SECTION: charstate filter utils
   */
  getStatesByCid = (cid) => {
    let results = {};
    this.state.characteristics.map((group) => {
      Object.values(group.characters).map((character) => {
        if (character.cid == cid) {
          results = character.states;
        }
      });
    });
    return results;
  };
  resetTaxaResults = () => {
    let tids = this.state.searchResults.taxonSort.map((taxon) => taxon['tid']);
    this.onSearchResults(tids);

    // even if there was an API error, suppress it if there are any initial search results
    // so that the page is at least partially functional
    if (this.state.searchResults.taxonSort.length) {
      this.setState({ apiError: false });
    }
  };
  onFilterRemoved = (key, text) => {
    if (key === 'searchText') {
      // TODO: This is clunky
      this.clearTextSearch();
      return;
    }

    if (this.state.filters.attrs[key]) {
      this.onAttrChanged(key, text, 'off');
    }
    if (this.state.filters.ranges[key]) {
      this.onRangeChanged(key, null);
    }
    if (this.state.filters.checklist['clid'] == key) {
      this.resetCanned();
    }
  };
  clearFilters = () => {
    let filters = {
      searchText: ViewOpts.DEFAULT_SEARCH_TEXT,
      attrs: {},
      ranges: {},
      checklist: { clid: -1, name: '' },
    };
    this.setState({ filters: filters }, function () {
      this.catchQuery();
    });
  };
  getFilterCount = () => {
    let filterCount = 0;
    filterCount += Object.keys(this.state.filters.attrs).length;
    filterCount += Object.keys(this.state.filters.ranges).length;
    filterCount += this.state.filters.checklist['clid'] > -1;
    filterCount += this.state.filters.searchText != ViewOpts.DEFAULT_SEARCH_TEXT;
    return filterCount;
  };

  render() {
    const InfographicDropdown = getInfographicDropdownComponent(this.props.pageType);
    return (
      <div id="rare-wrapper">
        <Loading clientRoot={this.props.clientRoot} isLoading={this.state.isLoading} />
        <InfographicDropdown clientRoot={this.props.clientRoot} />
        {this.props.pageType === 'rare' && (
          <div id="about-guide-flip">
            <div className="container mx-auto px-4">
              <button
                aria-expanded={this.state.aboutGuideExpanded}
                aria-controls="about-guide"
                onClick={() => this.setState({ aboutGuideExpanded: !this.state.aboutGuideExpanded })}
              >
                <h3>About This Guide</h3>
                <FontAwesomeIcon
                  icon="chevron-down"
                  color="white"
                  className={'will-v-flip' + (this.state.aboutGuideExpanded ? ' v-flip' : '')}
                  alt="toggle collapse"
                />
              </button>
            </div>
          </div>
        )}
        <div className="container mx-auto py-4 pl-3 pr-4">
          {this.props.pageType === 'rare' && (
            <AboutDropdown
              hidden={!this.state.aboutGuideExpanded}
              numSpecies={this.state.searchResults.familySort.length}
              lists={getIncludedHeritageLists(this.state.characteristics)}
            />
          )}
          <div className="row">
            <hr hidden={!this.state.aboutGuideExpanded} />
            <div className="col-md-4">
              <SideBar
                clid={this.state.clid}
                style={{ background: '#DFEFD3' }}
                isLoading={this.state.isLoading}
                clientRoot={this.props.clientRoot}
                characteristics={this.state.characteristics}
                searchText={this.state.filters.searchText}
                searchSuggestionUrl={`${this.props.clientRoot}/${this.props.pageType}/rpc/autofillsearch.php`}
                onSearch={this.onSearch}
                onSearchTextChanged={this.onSearchTextChanged}
                searchName={this.state.searchName}
                viewType={this.state.viewType}
                sortBy={this.state.sortBy}
                onSortByClicked={this.onSortByChanged}
                onAttrClicked={this.onAttrChanged}
                onRangeChanged={this.onRangeChanged}
                onFilterClicked={this.onFilterRemoved}
                onGroupFilterClicked={this.onGroupFilterClicked}
                onClearSearch={this.clearTextSearch}
                filters={this.state.filters}
                getFilterCount={this.getFilterCount}
                isMobile={this.state.isMobile}
                useNewSlider
              >
                {this.props.pageType === 'garden' && <GardenCredits clientRoot={this.props.clientRoot} />}
              </SideBar>
            </div>
            <div className="col-md-8">
              {this.props.pageType === 'garden' && (
                <div className="row">
                  <div className="col">
                    <CannedSearchContainer
                      searches={this.state.cannedSearches}
                      onFilter={this.onCannedFilter}
                      checklistId={this.state.filters.checklist['clid']}
                      clientRoot={this.props.clientRoot}
                      slideshowCount={this.state.slideshowCount}
                    />
                  </div>
                </div>
              )}
              {this.state.apiError ? (
                <div className="alert alert-danger" role="alert">
                  An error occurred. Please try again later.
                </div>
              ) : (
                <div className="" id="results-section">
                  <div className="row">
                    <div className="col">
                      {this.state.isMobile == true && this.state.searchInit == true && (
                        <div className="mobile-to-filters" onClick={() => this.mobileScrollToFilters()}>
                          <span>Apply More Filters</span>
                          <FontAwesomeIcon icon="chevron-up" />
                        </div>
                      )}
                    </div>
                  </div>
                  <div id="view-opts" className="row">
                    <div className="col-7 button-section">
                      <h3 className="font-weight-bold">Your search results:</h3>
                      <div className="d-flex flex-row flex-wrap">
                        <ViewOpts
                          onReset={this.clearFilters}
                          onFilterClicked={this.onFilterRemoved}
                          clientRoot={this.props.clientRoot}
                          filters={Object.keys(this.state.filters).map((filterKey) => {
                            return { key: filterKey, val: this.state.filters[filterKey] };
                          })}
                          getStatesByCid={this.getStatesByCid}
                          defaultMessage={getDefaultSearchResultsMessage(
                            this.state.searchResults.familySort.length,
                            this.state.characteristics,
                            this.props.pageType,
                          )}
                        />
                      </div>
                    </div>
                    <SortOptions
                      clientRoot={this.props.clientRoot}
                      viewType={this.state.viewType}
                      onViewTypeChanged={this.onViewTypeChanged}
                      sortBy={this.state.sortBy}
                      onSortByChanged={this.onSortByChanged}
                      defaultSortBy={getDefaultSortBy(this.props.pageType)}
                    />
                  </div>

                  {this.state.searchResults.taxonSort.length > 0 ? (
                    <CardSearchContainer
                      searchResults={this.state.searchResults}
                      viewType={this.state.viewType}
                      sortBy={this.state.sortBy}
                      clientRoot={this.props.clientRoot}
                      isSearching={this.state.isSearching}
                      currentTids={this.state.currentTids}
                      taxaPage={this.props.pageType}
                    />
                  ) : (
                    <p className="no-results">
                      Your search term(s) didn’t produce any results.{' '}
                      <span className="suggest">Try deleting a filter or Clearing All to try different terms?</span>
                    </p>
                  )}

                  <div className="go-top">
                    <a href="#results-section" className="toptext">
                      TOP
                      <br />
                      <FontAwesomeIcon icon="chevron-up" size="2x" />
                    </a>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        {this.state.isMobile == true && (
          <FilterModal show={this.state.showFilterModal}>
            <div className="modal-filter-content">
              <div className="filter-count">
                {this.getFilterCount()} filter{this.getFilterCount() > 1 ? 's' : ''} chosen
              </div>
              <div className="btn btn-primary current-button" role="button" onClick={() => this.doConfirm()}>
                Filter and see results
              </div>
            </div>
          </FilterModal>
        )}
      </div>
    );
  }
}

let pageType;
if (window.location.pathname.includes('/garden/')) {
  pageType = 'garden';
} else if (window.location.pathname.includes('/rare/')) {
  pageType = 'rare';
}

const headerContainer = document.getElementById('react-header');
const dataProps = JSON.parse(headerContainer.getAttribute('data-props'));
const domContainer = document.getElementById(`react-${pageType}`);
ReactDOM.render(<SpecialChecklistApp pageType={pageType} clientRoot={dataProps['clientRoot']} />, domContainer);
