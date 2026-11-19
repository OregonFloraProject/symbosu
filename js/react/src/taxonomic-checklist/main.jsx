import React, { useEffect, useRef, useState } from 'react';
import ReactDOM from 'react-dom';
const MIN_QUERY_LENGTH = 3;
const MAX_QUERY_LENGTH = 100;

function TaxonomicChecklist({ clientRoot }) {
  const [query, setQuery] = useState('');
  const [matches, setMatches] = useState({ family: [], genus: [] });
  // State of each dropdown. `expanded` is for the arrow dropdown
  // showAll is a flag for "more" option
  const [groupState, setGroupState] = useState({
    family: { expanded: true, showAll: false },
    genus: { expanded: true, showAll: false },
  });
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [error, setError] = useState('');
  const requestNumber = useRef(0);
  const debounceTimer = useRef(null);
  const blurTimer = useRef(null);

  const clearDebounce = () => {
    if (debounceTimer.current !== null) {
      window.clearTimeout(debounceTimer.current);
      debounceTimer.current = null;
    }
  };

  const hideSuggestions = () => {
    setShowSuggestions(false);
  };

  const resetGroups = () => {
    setGroupState({
      family: { expanded: true, showAll: false },
      genus: { expanded: true, showAll: false },
    });
  };

  const loadSuggestions = (searchQuery) => {
    const currentRequest = ++requestNumber.current;
    resetGroups();
    if (!searchQuery || 
      searchQuery.length > MAX_QUERY_LENGTH || 
      searchQuery.length < MIN_QUERY_LENGTH
    ) {
      setMatches({ family: [], genus: [] });
      setShowSuggestions(false);
      setError('');
      return;
    }
    const params = new URLSearchParams({ query: searchQuery });
    fetch(`${clientRoot}/pages/rpc/taxonomic-checklist.php?${params.toString()}`)
      .then((response) => {
        if (!response.ok) throw new Error('Search request failed.');
        return response.json();
      })
      .then((results) => {
        if (currentRequest !== requestNumber.current) return;
        const groupedResults = {
          family: Array.isArray(results.family) ? results.family : [],
          genus: Array.isArray(results.genus) ? results.genus : [],
        };
        setMatches(groupedResults);
        setShowSuggestions(groupedResults.family.length > 0 || groupedResults.genus.length > 0);
        setError('');
      })
      .catch(() => {
        if (currentRequest !== requestNumber.current) return;
        setMatches({ family: [], genus: [] });
        setShowSuggestions(false);
        setError('Search is temporarily unavailable.');
      });
  };

  const handleQueryChange = (event) => {
    const nextQuery = event.target.value;
    clearDebounce();
    requestNumber.current++;
    setQuery(nextQuery);
    setError('');
    hideSuggestions();
    if (!nextQuery.trim()) return;
    debounceTimer.current = window.setTimeout(() => {
      debounceTimer.current = null;
      loadSuggestions(nextQuery.trim());
    }, 500);
  };

  const handleSearch = () => {
    clearDebounce();
    loadSuggestions(query.trim());
  };

  const handleFocus = () => {
    if (blurTimer.current !== null) {
      window.clearTimeout(blurTimer.current);
      blurTimer.current = null;
    }
    setShowSuggestions(true);
  };

  const handleBlur = () => {
    blurTimer.current = window.setTimeout(() => {
      requestNumber.current++;
      hideSuggestions();
      blurTimer.current = null;
    }, 150);
  };

  const handleWidgetFocus = () => {
    if (blurTimer.current !== null) {
      window.clearTimeout(blurTimer.current);
      blurTimer.current = null;
    }
  };

  const toggleGroup = (group) => {
    setGroupState((current) => ({
      ...current,
      [group]: { ...current[group], expanded: !current[group].expanded },
    }));
  };

  const showAllInGroup = (group) => {
    setGroupState((current) => ({
      ...current,
      [group]: { ...current[group], showAll: true },
    }));
  };

  function GroupResult({group, label}) {
    // Get matches from either 'family' or 'group'
    const groupMatches = matches[group];
    const state = groupState[group];
    // Get first 5 results
    const visibleMatches = state.showAll ? groupMatches : groupMatches.slice(0, 5);

    const sectionId = `taxonomic-checklist-${group}-results`;
    return (
      <section className="fg-result-group" aria-label={`${label} results`} key={group}>
        <h4>
          <button
            className="fg-group-toggle"
            type="button"
            aria-expanded={state.expanded}
            aria-controls={sectionId}
            onClick={() => toggleGroup(group)}
          >
            <span className={`fg-group-arrow${state.expanded ? ' is-expanded' : ''}`} aria-hidden="true" />
            {label}
          </button>
        </h4>
        <div id={sectionId} hidden={!state.expanded}>
          {groupMatches.length === 0 ? (
            <p className="fg-no-matches">No {label.toLowerCase()} matches.</p>
          ) : (
            <>
              {visibleMatches.map((match) => (
                <a
                  className="dropdown-item"
                  target="_blank"
                  rel="noreferrer"
                  key={`${group}-${match.label}`}
                  href={`${clientRoot}/pages/pdfs/family/${encodeURIComponent(match.family)}.pdf`}
                >
                  {match.label}
                </a>
              ))}
              {!state.showAll && groupMatches.length > 5 && (
                <button className="fg-more" type="button" onClick={() => showAllInGroup(group)}>
                  More
                </button>
              )}
            </>
          )}
        </div>
      </section>
    );
  };

  useEffect(
    () => () => {
      if (debounceTimer.current !== null) window.clearTimeout(debounceTimer.current);
      if (blurTimer.current !== null) window.clearTimeout(blurTimer.current);
      requestNumber.current++;
    },
    [],
  );

  return (
      <div className="info-page page2022 taxonomic-checklist">
        <section id="titlebackground" className="title-publications">
          <div className="inner-content">
            <h1>Taxonomic Checklist</h1>
          </div>
        </section>
        <section>
          <div className="inner-content">
            <h2>
              Taxonomy is the science of organism relatedness. New discoveries change our understanding of those
              relationships and consequently how we name plants. Our Checklist captures this knowledge and is
              periodically updated to reflect the latest botanical research.
            </h2>
            <div className="row columns">
              <div id="column-main" className="col-lg-8">
                <div className="inset access">
                  <h3>Access the information of the Oregon Vascular Plant Checklist in these ways:</h3>
                  <ul>
                    <li>
                      <a href={`${clientRoot}/pages/data/Checklist_ForWeb.csv`} download>
                        Download
                      </a>{' '}
                      the current checklist (Version 2.4)
                    </li>
                    <li>
                      <a href={`${clientRoot}/pages/data/Natives_OregonFlora.csv`} download>
                        Download
                      </a>{' '}
                      the checklist including only plants native to Oregon.
                    </li>
                    <li>
                      <a href={`${clientRoot}/pages/data/CLrevisions.csv`} download>
                        Download
                      </a>{' '}
                      to view taxonomic changes, additions, and deletions between published versions of the Checklist.
                    </li>
                    <li>
                      Access the{' '}
                      <a href={`${clientRoot}/taxa/taxonomy/taxonomydisplay.php`}>
                        Interactive Taxon Search
                      </a>
                      .
                    </li>
                    <li>
                      <a href={`${clientRoot}/pages/data/terms-used-in-oregonflora-org-publications-pages.pdf`} download>
                        Download
                      </a>{' '}
                      to view glossary of terms used.
                    </li>
                  </ul>
                </div>
                <h2>The birth of a flora starts with a checklist of names</h2>
                <p>
                  The Oregon Vascular Plant Checklist provides a framework of scientific names for all vascular plant
                  taxa growing in the wild in our state for which a confirmed voucher exists, including native,
                  naturalized, non-naturalized, and sporadic taxa. The Checklist’s taxonomy is reflected throughout the
                  OregonFlora website, and it serves as a primary source for the <em>Flora of Oregon</em>.
                </p>
                <p>
                  The Checklist presents the plant diversity of Oregon. It is a living document that is continuously
                  revised to reflect current research and information. Preparation of the original Checklist began in
                  1994; it involved many contributing taxonomists (most of whom volunteered their expertise), as well
                  as volunteers, student workers, and OregonFlora staff.
                </p>
                <h2>
                  What’s the difference between the Checklist and <em>Flora of Oregon</em>?
                </h2>
                <figure>
                  <img
                    src={`${clientRoot}/pages/images/flora_vs_checklist_image.jpg`}
                    alt="comparing the OregonFlora checklist against the Flora of Oregon printed volumes"
                  />
                  <figcaption>
                    <div>All Oregon taxa growing in the wild: native, naturalized, not naturalized (exotic), sporadic </div>
                    <div>All Oregon taxa from the Checklist that are native or naturalized</div>
                  </figcaption>
                </figure>
                <p>
                  The <em>Flora of Oregon</em> recognizes 4,790 taxa (distinct vascular plants) that grow in our state in
                  the wild and without cultivation. We estimate 4,380 taxa will have detailed descriptions once{' '}
                  <em>Flora of Oregon</em> is completed.
                </p>
                <p>
                  The Oregon Vascular Plant Checklist presents 10,736 scientific names that are reflected throughout
                  our website tools. These include accepted native and naturalized taxa and their synonyms, as well as
                  non-naturalized (exotic), and sporadic taxa having a confirmed voucher that documents its occurrence
                  in our state.
                </p>
                <h2>What are confirmed vouchers?</h2>
                <img
                  className="vouchers"
                  src={`${clientRoot}/pages/images/osc163340_delphinium_andersonii.jpg`}
                  alt="Example of voucher for a specific taxon"
                />
                <p>
                  Herbarium specimens (vouchers) of Oregon collections give physical evidence that a taxon exists in
                  our state. If identified or reviewed by a botanist for OregonFlora, we call them “confirmed vouchers”.
                  These give tangible proof that the taxon has been found in Oregon. The OSU Herbarium is our primary
                  repository for Oregon confirmed vouchers. Like most herbaria, the collections are accessible to the
                  public through arranged visits or online.
                </p>
              </div>
              <div id="column-right" className="col-lg-4">
                <div className="sidebox inset search">
                  <h3>Search plant families and genera</h3>
                  <div className="search-wrapper" onBlur={handleBlur} onFocusCapture={handleWidgetFocus}>
                    <div className="search-widget">
                      <input
                        type="text"
                        className="form-control"
                        placeholder="Search families and genera"
                        autoComplete="off"
                        value={query}
                        onChange={handleQueryChange}
                        onFocus={handleFocus}
                      />
                      <button
                        className="btn-search"
                        type="button"
                        aria-label="Search"
                          onClick={handleSearch}
                      />
                      {showSuggestions && (
                        <div
                          className="dropdown-menu"
                        >
                          <GroupResult group='family' label='Family'></GroupResult>
                          <GroupResult group='genus' label='Genus'></GroupResult>
                        </div>
                      )}
                    </div>
                    <p className="fg-confirmation" aria-live="polite">
                      {error}
                    </p>
                  </div>
                  <p>
                    The family reports list each Oregon taxon, evidence supporting its occurrence in Oregon, and the
                    following information:
                  </p>
                  <ul>
                    <li>The scientific name chosen to represent each taxon in the Oregon Checklist</li>
                    <li>A common name </li>
                    <li>Its origin (native or exotic) </li>
                    <li>The herbarium which houses a primary confirmed voucher specimen</li>
                    <li>
                      The year the taxon was most recently collected, using OSU Herbarium collections as the primary
                      source
                    </li>
                    <li>Citations for each Oregon taxon in several local floras </li>
                    <li>
                      Synonyms for each Oregon taxon and their citations, mostly originating from accepted names in the
                      same local floras mentioned above
                    </li>
                    <li>Excluded names and their synonyms </li>
                    <li>
                      Pertinent{' '}
                      <a href={`${clientRoot}/pages/data/primary-regional-floras.pdf`} download>
                        references
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    );
}

const headerContainer = document.getElementById('react-header');
const dataProps = JSON.parse(headerContainer.getAttribute('data-props'));
const stylesheet = document.createElement('link');
stylesheet.rel = 'stylesheet';
stylesheet.href = `${dataProps['clientRoot']}/css/compiled/taxonomic-checklist.css`;
document.head.appendChild(stylesheet);
const domContainer = document.getElementById('react-taxonomic-checklist-app');
ReactDOM.render(<TaxonomicChecklist clientRoot={dataProps['clientRoot']} />, domContainer);
