import ReactDOM from 'react-dom';
import React from 'react';
import httpGet from '../common/httpGet.js';
import { getUrlQueryParams } from '../common/queryParams.js';
import { getGardenTaxaPage, getRareTaxaPage } from '../common/taxaUtils';
import ImageCarousel from '../common/imageCarousel.jsx';
import ImageModal from '../common/modal.jsx';
import Loading from '../common/loading.jsx';
import DescriptionTabs from './components/DescriptionTabs.jsx';
import MapItem from './components/MapItem.jsx';
import { showItem } from './components/utils.js';
import { CLID_RARE_ALL, RANK_FAMILY, RANK_GENUS } from './constants';
import { Link } from 'react-scroll';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faArrowCircleUp,
  faArrowCircleDown,
  faEdit,
  faChevronDown,
  faChevronUp,
} from '@fortawesome/free-solid-svg-icons';
library.add(faArrowCircleUp, faArrowCircleDown, faEdit, faChevronDown, faChevronUp);

function BorderedItem(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="border-item list-unstyled p-0 m-0">
        {props.value.map((v) => (
          <li key={v}>{v}</li>
        ))}
      </ul>
    );
  }

  return (
    <div className={'row dashed-border py-2'}>
      <div className="col font-weight-bold char-label">{props.keyName}</div>
      <div className="col char-value">{value}</div>
    </div>
  );
}
class SynonymItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showSynonyms: false,
      //hiddenSynonyms: false,
      maxSynonyms: 3,
    };
  }
  toggleSynonyms = () => {
    this.setState({ showSynonyms: !this.state.showSynonyms });
  };

  render() {
    let visibleItems = this.props.value.slice(0, this.state.maxSynonyms);
    let hiddenItems = this.props.value.slice(this.state.maxSynonyms);

    return (
      <div className={'synonym-items row dashed-border py-1'}>
        <div className="col font-weight-bold char-label">Synonyms and Misapplied Names</div>
        <div className="synonym-list col">
          <span className="short-list">
            {visibleItems.length > 0 &&
              Object.entries(visibleItems)
                .map(([key, obj]) => {
                  return (
                    <span key={key} className={'synonym-item'}>
                      <span className={'synonym-sciname'}>{obj.sciname}</span>
                      <span className={'synonym-author'}> {obj.author}</span>
                    </span>
                  );
                })
                .reduce((prev, curr) => [prev, ', ', curr])}
            {hiddenItems.length > 0 && !this.state.showSynonyms ? '...' : ''}
          </span>

          <span className="full-list" hidden={!this.state.showSynonyms}>
            {hiddenItems.length > 0 &&
              Object.entries(hiddenItems)
                .map(([key, obj]) => {
                  return (
                    <span key={key} className={'synonym-item'}>
                      <span className={'synonym-sciname'}>{obj.sciname}</span>
                      <span className={'synonym-author'}> {obj.author}</span>
                    </span>
                  );
                })
                .reduce((prev, curr) => [prev, ', ', curr])}
          </span>

          {this.props.value.length > this.state.maxSynonyms && (
            <span>
              <div className="up-down-toggle">
                <FontAwesomeIcon
                  icon={this.state.showSynonyms ? 'chevron-up' : 'chevron-down'}
                  onClick={this.toggleSynonyms}
                />
              </div>
            </span>
          )}
        </div>
      </div>
    );
  }
}
function MoreInfoItem(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="list-unstyled p-0 m-0">
        {props.value.map((v) => {
          if (v.url.indexOf('pdf') > 0) {
            return (
              <li key={v.url}>
                <a href={v.url}>
                  <button className="d-block my-2 btn-primary">
                    <img src={`${props.clientRoot}/images/pdf24.png`} />
                    {v.title}
                  </button>
                </a>
              </li>
            );
          } else {
            return (
              <li key={v.url}>
                <a href={v.url}>
                  <button className="d-block my-2 btn-primary">{v.title}</button>
                </a>
              </li>
            );
          }
        })}
      </ul>
    );
  }

  return (
    <div className={'more-info row dashed-border py-2'}>
      <div className="col font-weight-bold">{props.keyName}</div>
      <div className="col">{value}</div>
    </div>
  );
}
function SingleBorderedItem(props) {
  let value = props.value;
  //console.log(props);
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="p-0 m-0 single-border-item">
        {props.value.map((v) => {
          return (
            <li className="col dashed-border py-2" key={v['key']}>
              {v}
            </li>
          );
        })}
      </ul>
    );
  }

  return <div className={'row'}>{value}</div>;
}
function RelatedBorderedItem(props) {
  let value = '';
  //console.log(props);
  value = (
    <div className="col-sm-12 related py-2 row">
      <div className="col-sm-8 related-sciname">{props.value[0]}</div>
      <div className="col-sm-4 related-nav pr-0">
        <span className="related-label">Related</span>
        <span className="related-links">
          {props.rankId > RANK_FAMILY && (
            <a href={props.value[1]}>
              <FontAwesomeIcon icon="arrow-circle-up" />
            </a>
          )}
          {props.rankId > RANK_FAMILY && props.value[2].length > 0 && (
            /* two statements here because I don't want to wrap them in one div */
            <span className="separator">/</span>
          )}
          {props.value[2].length > 0 && (
            <Link to="spp-wrapper" spy={true} smooth={true} duration={400} offset={-180}>
              <FontAwesomeIcon icon="arrow-circle-down" />
            </Link>
          )}
        </span>
      </div>
    </div>
  );
  return <div className={'row'}>{value}</div>;
}

function SideBarSection(props) {
  let itemKeys = Object.keys(props.items);
  itemKeys = itemKeys.filter((k) => {
    const v = props.items[k];
    return showItem(v);
  });
  return (
    <div className={'sidebar-section mb-5 ' + props.classes + ' ' + (itemKeys.length > 0 ? '' : 'd-none')}>
      <h3 className="text-light-green font-weight-bold mb-3">{props.title}</h3>
      {itemKeys.map((key) => {
        const val = props.items[key];
        if (key == 'webLinks') {
          return <SingleBorderedItem key={val} keyName={val} value={val} />;
        } else if (key == 'Related') {
          return <RelatedBorderedItem key={key} keyName={key} value={val} rankId={props.rankId} />;
        } else if (key == 'More info') {
          return <MoreInfoItem key={key} keyName={key} value={val} clientRoot={props.clientRoot} />;
        } else if (key == 'Synonyms') {
          return <SynonymItem key={val} keyName={val} value={val} />;
        } else if (val) {
          return <BorderedItem key={key} keyName={key} value={val} />;
        }
      })}
      <span className="row dashed-border" />
    </div>
  );
}

function SppItem(props) {
  const item = props.item;
  let image = null;
  if (item.imagesBasis.HumanObservation.length > 0) {
    image = item.imagesBasis.HumanObservation[0];
  } else if (item.imagesBasis.PreservedSpecimen.length > 0) {
    image = item.imagesBasis.PreservedSpecimen[0];
  }
  let mapImage = null;
  mapImage = `${props.clientRoot}/images/maps/${item.tid}_sm.jpg`;
  let sppQueryParams = queryParams;
  sppQueryParams['taxon'] = item.tid;
  let sppUrl = window.location.pathname + '?taxon=' + encodeURIComponent(sppQueryParams['taxon']);
  return (
    <div key={item.tid} className="card search-result grid-result">
      <a href={sppUrl}>
        <h4>{item.sciname}</h4>
        {image && (
          <div className="img-thumbnail">
            <img
              className="card-img-top grid-image"
              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
              src={image.thumbnailurl}
              alt={image.thumbnailurl}
            />
          </div>
        )}
        <div className="map-preview">
          <img className="card-img-top grid-image" src={mapImage} />
        </div>
      </a>
    </div>
  );
}

class TaxaChooser extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const res = this.props.res;
    const titleElement = document.getElementsByTagName('title')[0];
    const pageTitle = this.props.defaultTitle + ' ' + (res.sciName ? res.sciName : res.family);
    titleElement.innerHTML = pageTitle;
    return (
      <div className="container mx-auto py-5 taxa-detail" style={{ minHeight: '45em' }}>
        <Loading clientRoot={this.props.clientRoot} isLoading={res.isLoading} />
        <div className="print-header">
          {pageTitle}
          <br />
          {window.location.href}
        </div>
        <div className="row print-start">
          <div className="col">
            <h1>
              {res.sciName} {res.author}
            </h1>
          </div>
          {/* <div className="col-auto">
						<FontAwesomeIcon icon="edit" />
					</div>
					*/}
          <div className="col-auto">
            <button className="d-block my-2 btn-primary print-trigger" onClick={() => window.print()}>
              Print page
            </button>
            {/*<button className="d-block my-2 btn-secondary" disabled={ true }>Add to basket</button>*/}
          </div>
        </div>
        <div className="row mt-2 row-cols-sm-2 main-wrapper">
          <div className="col-md-8 pr-4 main-section">
            <p className="mt-4">
              {/*
								Description includes HTML tags & URL-encoded characters in the db.
								It's dangerous to pull/render arbitrary HTML w/ react, so just render the
								plain text & remove any HTML in it.
							*/}
              {/*this.state.descriptions.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "") */}
            </p>
            {res.descriptions.length > 0 && <DescriptionTabs descriptions={res.descriptions} glossary={res.glossary} />}

            {res.spp.length > 0 && (
              <div className="mt-4 dashed-border" id="subspecies">
                <h3 className="text-light-green font-weight-bold mt-2">Species, subspecies and varieties</h3>
                <div className="spp-wrapper search-result-grid">
                  {res.spp.map((spp) => {
                    return <SppItem item={spp} key={spp.tid} clientRoot={this.props.clientRoot} />;
                  })}
                </div>
              </div>
            )}
          </div>
          <div className="col-md-4 pl-4 sidebar sidebar-section">
            <SideBarSection
              title="Context"
              items={res.highlights}
              classes="highlights"
              rankId={res.rankId}
              clientRoot={this.props.clientRoot}
            />
            <SideBarSection
              title="Web links"
              items={res.taxalinks}
              classes="weblinks"
              rankId={res.rankId}
              clientRoot={this.props.clientRoot}
            />
          </div>
        </div>
      </div>
    );
  }
}

class TaxaDetail extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      isOpen: false,
      currImage: 0,
      currImageBasis: this.props.res.images.HumanObservation,
    };
  }

  toggleImageModal = (_currImage, _imageBasis) => {
    let basis =
      _imageBasis === 'PreservedSpecimen'
        ? this.props.res.images.PreservedSpecimen
        : this.props.res.images.HumanObservation;

    this.setState({
      currImage: _currImage,
      isOpen: !this.state.isOpen,
      currImageBasis: basis,
    });
  };
  render() {
    const res = this.props.res;
    const pageTitle = this.props.defaultTitle + ' ' + res.sciName;
    const titleElement = document.getElementsByTagName('title')[0];
    titleElement.innerHTML = pageTitle;
    const allImages = res.images.HumanObservation.concat(res.images.PreservedSpecimen);
    const showDescriptions = res.descriptions ? true : false;
    let h2 = res.vernacularNames[0];
    let h2class = '';

    /* handle unusual cases of ambiguous synonyms like 6617 */
    let otherH2 = '';
    if (Object.keys(res.ambiguousSynonyms).length > 0) {
      otherH2 = 'This name is a synonym for the following accepted taxa: ';
      h2class = 'ambiguous';
      h2 = Object.keys(res.ambiguousSynonyms)
        .map((ampTid) => {
          return (
            <a key={ampTid} href={`${this.props.clientRoot}/taxa/index.php?taxon=${ampTid}`}>
              {res.ambiguousSynonyms[ampTid]['sciname']}
            </a>
          );
        })
        .reduce((prev, curr) => [prev, ', ', curr]);
    }

    return (
      <div className="container mx-auto py-5 taxa-detail" style={{ minHeight: '45em' }}>
        <Loading clientRoot={this.props.clientRoot} isLoading={res.isLoading} />
        <div className="print-header">
          {pageTitle}
          <br />
          {window.location.href}
        </div>
        <div className="row print-start">
          <div className="col">
            <h1>
              <span className="font-italic">{res.sciName}</span> {res.author}
            </h1>

            <h2 className={h2class}>
              {otherH2}
              <span className="font-italic">{h2}</span>
              {res.synonym && (
                <span className="synonym">
                  {' '}
                  (synonym: <span className="font-italic">{res.synonym}</span>)
                </span>
              )}
            </h2>
          </div>
          <div className="col-auto">
            <button className="d-block my-2 btn-primary print-trigger" onClick={() => window.print()}>
              Print page
            </button>
            {/*<button className="d-block my-2 btn-secondary" disabled={ true }>Add to basket</button>*/}
          </div>
        </div>
        <div className="row mt-2 row-cols-sm-2 main-wrapper">
          <div className="col-md-8 pr-4 main-section">
            {allImages.length > 0 && (
              <figure>
                <div className="img-main-wrapper">
                  <img id="img-main" src={allImages[0].url} alt={res.sciName} />
                </div>
                <figcaption>{allImages[0].photographer}</figcaption>
              </figure>
            )}
            {/*
				
								Description includes HTML tags & URL-encoded characters in the db.
								It's dangerous to pull/render arbitrary HTML w/ react, so just render the
								plain text & remove any HTML in it.		
								<p className="mt-4">
								</p>
							*/}
            {/*this.state.descriptions.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "") */}
            {showDescriptions && <DescriptionTabs descriptions={res.descriptions} glossary={res.glossary} />}

            {res.spp.length > 0 && (
              <div className="mt-4 dashed-border" id="subspecies">
                <h3 className="text-light-green font-weight-bold mt-2">Subspecies and varieties</h3>
                <div className="spp-wrapper search-result-grid">
                  {res.spp.map((spp) => {
                    return <SppItem item={spp} key={spp.tid} clientRoot={this.props.clientRoot} />;
                  })}
                </div>
              </div>
            )}

            {res.images.HumanObservation.length > 0 && (
              <div className="mt-4 dashed-border taxa-slideshows" id="photos">
                <h3 className="text-light-green font-weight-bold mt-2">Photo images</h3>
                <div className="slider-wrapper">
                  <ImageCarousel
                    images={res.images.HumanObservation}
                    imageCount={res.images.HumanObservation.length}
                    slideshowCount={res.slideshowCount}
                  >
                    {res.images.HumanObservation.map((image, index) => {
                      return (
                        <div key={image.url}>
                          <div className="card" style={{ padding: '0.5em' }}>
                            <div style={{ position: 'relative', width: '100%', height: '7em', borderRadius: '0.25em' }}>
                              <img
                                className="d-block"
                                style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                src={image.thumbnailurl}
                                alt={image.thumbnailurl}
                                onClick={() => this.toggleImageModal(index, 'HumanObservation')}
                              />
                            </div>
                          </div>
                        </div>
                      );
                    })}
                  </ImageCarousel>
                </div>
              </div>
            )}

            {res.images.PreservedSpecimen.length > 0 && (
              <div className="mt-4 dashed-border taxa-slideshows" id="herbarium">
                <h3 className="text-light-green font-weight-bold mt-2">Herbarium specimens</h3>
                <div className="slider-wrapper">
                  <ImageCarousel
                    images={res.images.PreservedSpecimen}
                    imageCount={res.images.PreservedSpecimen.length}
                    slideshowCount={res.slideshowCount}
                  >
                    {res.images.PreservedSpecimen.map((image, index) => {
                      return (
                        <div key={image.url}>
                          <div className="card" style={{ padding: '0.5em' }}>
                            <div style={{ position: 'relative', width: '100%', height: '7em', borderRadius: '0.25em' }}>
                              <img
                                className="d-block"
                                style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                src={image.thumbnailurl}
                                alt={image.thumbnailurl}
                                onClick={() => this.toggleImageModal(index, 'PreservedSpecimen')}
                              />
                            </div>
                          </div>
                        </div>
                      );
                    })}
                  </ImageCarousel>
                </div>
              </div>
            )}
          </div>
          <div className="col-md-4 sidebar sidebar-section">
            <SideBarSection
              title="Context"
              items={res.highlights}
              classes="highlights"
              rankId={res.rankId}
              clientRoot={this.props.clientRoot}
            />
            <MapItem
              title={res.sciName}
              tid={res.tid}
              clientRoot={this.props.clientRoot}
              needsPermission={res.accessRestricted}
            />
            <SideBarSection
              title="Web links"
              items={res.taxalinks}
              classes="weblinks"
              rankId={res.rankId}
              clientRoot={this.props.clientRoot}
            />
          </div>
        </div>
        <ImageModal
          show={this.state.isOpen}
          currImage={this.state.currImage}
          images={this.state.currImageBasis}
          onClose={this.toggleImageModal}
          clientRoot={this.props.clientRoot}
        >
          <h3>
            <span>{res.vernacularNames[0]}</span> images
          </h3>
        </ImageModal>
      </div>
    );
  }
}

class TaxaApp extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isLoading: true,
      tid: null,
      sciName: '',
      author: '',
      basename: '',
      family: '',
      vernacularNames: [],
      images: {
        HumanObservation: [],
        PreservedSpecimen: [],
        LivingSpecimen: [],
      },
      descriptions: [],
      synonym: '',
      synonyms: [],
      ambiguousSynonyms: [],
      origin: '',
      taxalinks: [],
      gardenId: null,
      rarePlantFactSheet: '',
      accessRestricted: false,
      highlights: {},
      spp: [],
      rankId: null,
      currImage: 0,
      related: [],
      glossary: [],
      slideshowCount: 5,
    };
    this.getTid = this.getTid.bind(this);
    this.updateViewport = this.updateViewport.bind(this);
  }

  getTid() {
    return parseInt(this.props.tid);
  }
  updateViewport() {
    let newSlideshowCount = 5;
    if (window.innerWidth < 1200) {
      newSlideshowCount = 4;
    }
    if (window.innerWidth < 992) {
      newSlideshowCount = 3;
    }
    this.setState({ slideshowCount: newSlideshowCount });
  }
  componentDidMount() {
    if (this.getTid() === -1) {
      window.location = '/';
    } else {
      // Get a list of glossary terms
      httpGet('../glossary/rpc/getterms.php')
        .then((res) => {
          res = JSON.parse(res);
          this.setState({
            glossary: res,
          });
        })
        .catch((err) => {
          // TODO: Something's wrong
          console.error(err);
        });

      let api = `./rpc/api.php?taxon=${this.props.tid}`;
      //console.log(api);
      httpGet(api)
        .then((res) => {
          // /taxa/rpc/api.php?taxon=2454
          res = JSON.parse(res);

          let url = new URL(window.location);
          let parentQueryParams = new URLSearchParams(url.search);
          parentQueryParams.set('taxon', res.parentTid);
          let parentUrl = window.location.pathname + '?' + parentQueryParams.toString();

          let childUrl = '';
          if (res.spp.length) {
            childUrl = '#subspecies';
          }

          const relatedArr = [res.sciname, parentUrl, childUrl];

          let moreInfo = [];
          if (res.specialChecklists && res.specialChecklists.includes(CLID_RARE_ALL)) {
            const rareProfileUrl = getRareTaxaPage(this.props.clientRoot, this.props.tid);
            moreInfo.push({ title: 'Rare Plant Profile', url: rareProfileUrl });
          }
          if (res.rarePlantFactSheet.length) {
            moreInfo.push({ title: 'Rare Plant Fact Sheet', url: res.rarePlantFactSheet });
          }
          if (res.gardenId > 0) {
            let gardenUrl = getGardenTaxaPage(this.props.clientRoot, res.gardenId);
            moreInfo.push({ title: 'Garden Fact Sheet', url: gardenUrl });
          }

          let web_links = res.taxalinks.map((link) => {
            return (
              <div key={link.url}>
                <a href={link.url} target="_blank" rel="noreferrer">
                  {link.title}
                </a>
              </div>
            );
          });

          let synonym = '';
          if (this.props.synonym) {
            Object.keys(res.synonyms).map((key) => {
              if (this.props.synonym === res.synonyms[key].tid) {
                synonym = res.synonyms[key].sciname;
              }
            });
          }
          this.setState({
            tid: this.getTid(),
            sciName: res.sciname,
            author: res.author,
            basename: res.vernacular.basename,
            vernacularNames: res.vernacular.names,
            images: res.imagesBasis,
            gardenId: res.gardenId,
            rankId: res.rankId,
            descriptions: res.descriptions,
            highlights: {
              Related: relatedArr,
              Family: res.family,
              'Common Names': res.vernacular.names,
              Synonyms: res.synonyms,
              Origin: res.origin,
              'More info': moreInfo,
            },
            taxalinks: {
              webLinks: web_links,
            },
            accessRestricted: !!res.accessRestricted,
            spp: res.spp,
            related: relatedArr,
            family: res.family,
            synonym: synonym,
            ambiguousSynonyms: res.ambiguousSynonyms,
          });
        })
        .catch((err) => {
          // TODO: Something's wrong
          console.error(err);
        })
        .finally(() => {
          this.setState({ isLoading: false });
          this.updateViewport();
        });
      window.addEventListener('resize', this.updateViewport);
    }
  } //componentDidMount

  render() {
    //choose page
    if (this.state.rankId <= RANK_GENUS) {
      return <TaxaChooser res={this.state} clientRoot={this.props.clientRoot} defaultTitle={this.props.defaultTitle} />; //Genus or Family
    } else {
      return <TaxaDetail res={this.state} clientRoot={this.props.clientRoot} defaultTitle={this.props.defaultTitle} />; //Species
    }
  }
}

TaxaApp.defaultProps = {
  tid: -1,
};

const headerContainer = document.getElementById('react-header');
const dataProps = JSON.parse(headerContainer.getAttribute('data-props'));
const domContainer = document.getElementById('react-taxa-app');
const queryParams = getUrlQueryParams(window.location.search);

// Use both taxon and tid (symbiota-light) to denote the taxon
if (queryParams.tid) {
  queryParams.taxon = queryParams.tid;
}

if (queryParams.search) {
  window.location = `./search.php?search=${encodeURIComponent(queryParams.search)}`;
} else if (queryParams.taxon) {
  ReactDOM.render(
    <TaxaApp
      tid={queryParams.taxon}
      defaultTitle={dataProps['defaultTitle']}
      clientRoot={dataProps['clientRoot']}
      synonym={queryParams.synonym - 0}
    />,
    domContainer,
  );
} else {
  window.location = '/';
}
