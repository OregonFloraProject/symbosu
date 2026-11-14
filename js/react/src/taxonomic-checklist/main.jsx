import React, { useEffect, useRef, useState } from 'react';
import ReactDOM from 'react-dom';

function TaxonomicChecklist({ clientRoot }) {
  const [mode, setMode] = useState('family');
  const [query, setQuery] = useState('');
  const [matches, setMatches] = useState([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [confirmation, setConfirmation] = useState(null);
  const [error, setError] = useState('');
  const requestNumber = useRef(0);
  const debounceTimer = useRef(null);
  const blurTimer = useRef(null);
  const errorTimer = useRef(null);

  const clearDebounce = () => {
    if (debounceTimer.current !== null) {
      window.clearTimeout(debounceTimer.current);
      debounceTimer.current = null;
    }
  };

  const clearErrorTimer = () => {
    if (errorTimer.current !== null) {
      window.clearTimeout(errorTimer.current);
      errorTimer.current = null;
    }
  };

  const hideSuggestions = () => {
    setMatches([]);
    setShowSuggestions(false);
  };

  const loadSuggestions = (searchQuery, searchMode = mode) => {
    const currentRequest = ++requestNumber.current;
    clearErrorTimer();
    if (!searchQuery) {
      setMatches([]);
      setShowSuggestions(false);
      setError('');
      return;
    }
    const params = new URLSearchParams({ mode: searchMode, query: searchQuery });
    fetch(`${clientRoot}/pages/rpc/taxonomic-checklist.php?${params.toString()}`)
      .then((response) => {
        if (!response.ok) throw new Error('Search request failed.');
        return response.json();
      })
      .then((results) => {
        if (currentRequest !== requestNumber.current) return;
        setMatches(results);
        setShowSuggestions(results.length > 0);
        setConfirmation(null);
        setError('');
      })
      .catch(() => {
        if (currentRequest !== requestNumber.current) return;
        setMatches([]);
        setShowSuggestions(false);
        setError('Search is temporarily unavailable.');
        errorTimer.current = window.setTimeout(() => {
          setError('');
          errorTimer.current = null;
        }, 4000);
      });
  };

  const handleModeChange = (event) => {
    clearDebounce();
    requestNumber.current++;
    setMode(event.target.value);
    setConfirmation(null);
    hideSuggestions();
  };

  const handleQueryChange = (event) => {
    const nextQuery = event.target.value;
    clearDebounce();
    requestNumber.current++;
    setQuery(nextQuery);
    setConfirmation(null);
    setError('');
    clearErrorTimer();
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
    handleSearch();
  };

  const handleBlur = () => {
    blurTimer.current = window.setTimeout(() => {
      requestNumber.current++;
      hideSuggestions();
      blurTimer.current = null;
    }, 150);
  };

  const handleSelect = (match) => {
    clearDebounce();
    requestNumber.current++;
    setQuery(match.name);
    setMatches([]);
    setShowSuggestions(false);
    setError('');
    setConfirmation({
      family: match.family,
      url: `${clientRoot}/pages/pdfs/family/${encodeURIComponent(match.family)}.pdf`,
    });
  };

  useEffect(
    () => () => {
      if (debounceTimer.current !== null) window.clearTimeout(debounceTimer.current);
      if (errorTimer.current !== null) window.clearTimeout(errorTimer.current);
      if (blurTimer.current !== null) window.clearTimeout(blurTimer.current);
      requestNumber.current++;
    },
    [],
  );

  const isFamily = mode === 'family';
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
                  <div className="search-wrapper">
                    <div className="fg-mode-toggle">
                      <label className={`fg-mode-option${isFamily ? '' : ' is-inactive'}`} htmlFor="fg-mode-family">
                        <input
                          type="radio"
                          name="fg-mode"
                          id="fg-mode-family"
                          value="family"
                          checked={isFamily}
                          onChange={handleModeChange}
                        />{' '}
                        Family
                      </label>
                      <label className={`fg-mode-option${isFamily ? ' is-inactive' : ''}`} htmlFor="fg-mode-genus">
                        <input
                          type="radio"
                          name="fg-mode"
                          id="fg-mode-genus"
                          value="genus"
                          checked={!isFamily}
                          onChange={handleModeChange}
                        />{' '}
                        Genus
                      </label>
                    </div>
                    <div className="search-widget">
                      <input
                        type="text"
                        className="form-control"
                        placeholder={isFamily ? 'Search families' : 'Search genera'}
                        autoComplete="off"
                        value={query}
                        onChange={handleQueryChange}
                        onFocus={handleFocus}
                        onBlur={handleBlur}
                      />
                      <button
                        className="btn-search"
                        type="button"
                        aria-label="Search"
                        onMouseDown={(event) => event.preventDefault()}
                        onClick={handleSearch}
                      />
                      {showSuggestions && (
                        <div
                          className="dropdown-menu"
                          onMouseDown={(event) => event.preventDefault()}
                        >
                          {matches.map((match) => (
                            <a
                              className="dropdown-item"
                              type="button"
                              key={`${match.label}`}
                              onClick={() => handleSelect(match)}
                            >
                              {match.label}
                            </a>
                          ))}
                        </div>
                      )}
                    </div>
                    <p className="fg-confirmation" aria-live="polite">
                      {error}
                      {confirmation && (
                        <a
                          href={confirmation.url}
                          target="_blank"
                          rel="noreferrer"
                        >
                          View {confirmation.family} PDF
                        </a>
                      )}
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
const domContainer = document.getElementById('react-taxonomic-checklist-app');
ReactDOM.render(<TaxonomicChecklist clientRoot={dataProps['clientRoot']} />, domContainer);
