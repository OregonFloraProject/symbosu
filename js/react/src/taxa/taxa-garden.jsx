import ReactDOM from 'react-dom';
import React from 'react';
import { addGlossaryTooltips } from '../common/glossary.js';
import httpGet from '../common/httpGet.js';
import { getUrlQueryParams } from '../common/queryParams.js';
import ImageCarousel from '../common/imageCarousel.jsx';
import ImageModal from '../common/modal.jsx';
import ExplorePreviewModal from '../explore/previewModal.jsx';
import { getTaxaPage } from '../common/taxaUtils';
import Loading from '../common/loading.jsx';
import SideBarSection from './components/SideBarSection.jsx';
import SideBarSectionVendor from './components/SideBarSectionVendor.jsx';
import { csRangeToString, sortKeyedCharObject } from './utils';

class TaxaApp extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isLoading: true,
      sciName: '',
      basename: '',
      vernacularNames: [],
      images: [],
      description: '',
      highlights: {},
      plantFacts: {},
      growthMaintenance: {},
      commercialAvailability: {},
      isOpen: false, //imagemodal
      isPreviewOpen: false, //explorePreviewModal
      currClid: -1, //explorePreviewModal
      currPid: 3, //explorePreviewModal
      tid: null,
      currImage: 0,
      checklists: [],
      nativeGroups: [],
      slideshowCount: 5,
      glossary: {},
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
  toggleImageModal = (_currImage) => {
    this.setState({
      currImage: _currImage,
    });
    this.setState({
      isOpen: !this.state.isOpen,
    });
  };
  togglePreviewModal = (_currClid) => {
    this.setState({
      currClid: _currClid,
    });
    this.setState({
      isPreviewOpen: !this.state.isPreviewOpen,
    });
  };
  componentDidMount() {
    if (this.getTid() === -1) {
      window.location = '/';
    } else {
      let url = `./rpc/api.php?taxon=${this.props.tid}&type=garden`;
      httpGet(url)
        .then((res) => {
          // /taxa/rpc/api.php?taxon=2454
          res = JSON.parse(res);
          //console.log(res.characteristics.features);
          let plantType = '';
          let foliageType = res.characteristics.foliage_type;
          plantType += foliageType.length > 0 ? `${foliageType[0]} ` : '';

          if (res.characteristics.lifespan.length > 0) {
            plantType += `${res.characteristics.lifespan[0]}`.trim() + ' ';
          }
          if (res.characteristics.plant_type.length > 0) {
            plantType += res.characteristics.plant_type.join(' or ') + ' ';
          }

          const width = sortKeyedCharObject(res.characteristics.width);
          const height = sortKeyedCharObject(res.characteristics.height);
          let sizeMaturity = '';
          if (height.length > 0) {
            sizeMaturity += height.length > 1 ? `${height[0]}-${height[height.length - 1]}` : `${height[0]}`;
            sizeMaturity += "' high";
          }
          if (width.length > 0) {
            if (sizeMaturity !== '') {
              sizeMaturity += ', ';
            }
            sizeMaturity += width.length > 1 ? `${width[0]}-${width[width.length - 1]}` : `${width[0]}`;
            sizeMaturity += "' wide";
          }

          const ease_of_growth = csRangeToString(res.characteristics.growth_maintenance.ease_of_growth);

          const spreads_vigorously = res.characteristics.growth_maintenance.spreads_vigorously;

          let moisture = [];
          if (res.characteristics.moisture.length > 0) {
            moisture.push(csRangeToString(res.characteristics.moisture));
          }
          if (res.characteristics.summer_moisture.length > 0) {
            moisture.push(`${csRangeToString(res.characteristics.summer_moisture)} summer water`);
          }

          this.setState({
            sciName: res.sciname,
            basename: res.vernacular.basename,
            vernacularNames: res.vernacular.names,
            images: res.imagesBasis.HumanObservation,
            description: res.gardenDescription,
            checklists: res.specialChecklists,
            highlights: {
              'Plant type': plantType,
              'Size at maturity': sizeMaturity,
              'Light tolerance': res.characteristics.sunlight,
              'Ease of growth': ease_of_growth,
            },
            plantFacts: {
              'Flower color': res.characteristics.flower_color,
              'Bloom time': csRangeToString(res.characteristics.bloom_months, '-'),
              Moisture: moisture,
              'Wildlife support': res.characteristics.wildlife_support,
            },
            growthMaintenance: {
              'Spreads vigorously': spreads_vigorously === null ? '' : spreads_vigorously,
              'Cultivation preferences': res.characteristics.growth_maintenance.cultivation_preferences,
              'Plant behavior': res.characteristics.growth_maintenance.behavior,
              Propagation: res.characteristics.growth_maintenance.propagation,
              'Landscape uses': res.characteristics.growth_maintenance.landscape_uses,
            },
          });
          const nativeGroups = [];
          httpGet(`${this.props.clientRoot}/garden/rpc/api.php?canned=true`).then((res) => {
            let cannedSearches = JSON.parse(res); //14796, 14797, 14798, 14799, 14800
            Object.values(cannedSearches).map((checklist) => {
              let match = this.state.checklists.indexOf(checklist.clid);
              if (match > -1) {
                nativeGroups.push(checklist);
              }
            });
            this.setState({ nativeGroups: nativeGroups });
          });

          const commercialAvailability = {};
          var vendorURL = `${this.props.clientRoot}/checklists/rpc/api-vendor.php?action=taxa_garden&tid=${this.props.tid}`;
          httpGet(vendorURL).then((res) => {
            res = JSON.parse(res);
            Object.values(res).map((taxon) => {
              let vendors = [];
              Object.values(taxon.vendors).map((vendor) => {
                vendors.push({ clid: vendor.clid, name: vendor.name });
              });
              commercialAvailability[taxon.sciname] = vendors;
            });
            this.setState({ commercialAvailability: commercialAvailability });
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

      const fetchGlossary = async () => {
        try {
          const res = await httpGet('../glossary/rpc/getterms.php');
          this.setState({ glossary: JSON.parse(res) });
        } catch (err) {
          // just log this error and don't do anything for now, since the glossary isn't strictly
          // necessary for the functioning of the page
          console.error(err);
        }
      };
      fetchGlossary();

      window.addEventListener('resize', this.updateViewport);
    }
  } //componentDidMount

  render() {
    const titleElement = document.getElementsByTagName('title')[0];
    const pageTitle = `${this.props.defaultTitle} ${this.state.sciName}`;
    titleElement.innerHTML = pageTitle;
    return (
      <div className="container mx-auto pl-4 pr-4 pt-5" style={{ minHeight: '45em' }}>
        <Loading clientRoot={this.props.clientRoot} isLoading={this.state.isLoading} />
        <div className="print-header">
          {pageTitle}
          <br />
          {window.location.href}
        </div>
        <div className="row print-start">
          <div className="col">
            <h1 className="">{this.state.vernacularNames[0]}</h1>
            <h2 className="font-italic">{this.state.sciName}</h2>
          </div>
          <div className="col-auto">
            <button className="d-block my-2 btn-primary print-trigger" onClick={() => window.print()}>
              Print page
            </button>
            {/*<button className="d-block my-2 btn-secondary" disabled={ true }>Add to basket</button>*/}
          </div>
        </div>
        <div className="row mt-2 main-wrapper">
          <div className="col-md-8 main-section">
            {this.state.images.length > 0 && (
              <figure>
                <div className="img-main-wrapper">
                  <img id="img-main" src={this.state.images[0].url} alt={this.state.sciName} />
                </div>
                <figcaption>{this.state.images[0].photographer}</figcaption>
              </figure>
            )}

            <p
              className="mt-4"
              dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(this.state.description, this.state.glossary) }}
            />
            <div className="mt-4 dashed-border taxa-slideshows">
              <h3 className="text-light-green font-weight-bold mt-2">{this.state.vernacularNames[0]} images</h3>
              <div className="slider-wrapper">
                <ImageCarousel
                  images={this.state.images}
                  imageCount={this.state.length}
                  slideshowCount={this.state.slideshowCount}
                >
                  {this.state.images.map((image, index) => {
                    return (
                      <div key={image.url}>
                        <div className="card" style={{ padding: '0.6em' }}>
                          <div style={{ position: 'relative', width: '100%', height: '7em', borderRadius: '0.25em' }}>
                            <img
                              className="d-block"
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              src={image.thumbnailurl}
                              alt={image.thumbnailurl}
                              onClick={() => this.toggleImageModal(index)}
                            />
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </ImageCarousel>
              </div>
            </div>
          </div>
          <ImageModal
            show={this.state.isOpen}
            currImage={this.state.currImage}
            images={this.state.images}
            onClose={this.toggleImageModal}
            clientRoot={this.props.clientRoot}
          >
            <h3>
              <span>{this.state.vernacularNames[0]}</span> images
            </h3>
          </ImageModal>
          <div className="col-md-4 sidebar-section">
            <SideBarSection title="Highlights" items={this.state.highlights} />
            {this.state.nativeGroups.length > 0 && (
              <div className={'mb-4 sidebar-canned'}>
                <h3 className="text-light-green font-weight-bold mb-1">Native plant groups</h3>
                <p>
                  Containing <strong>{this.state.vernacularNames[0]}:</strong>
                </p>
                <div className="canned-results dashed-border">
                  {this.state.nativeGroups.map((checklist) => {
                    return (
                      <div key={checklist.clid} className={'py-2 canned-search-result'}>
                        <h4 className="canned-title" onClick={() => this.togglePreviewModal(checklist.clid)}>
                          {checklist.name}
                        </h4>
                        <div className="card" style={{ padding: '0.5em' }}>
                          <div className="card-body" style={{ padding: '0' }}>
                            <div style={{ position: 'relative', width: '100%', height: '7em', borderRadius: '0.25em' }}>
                              <img
                                className="d-block"
                                style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                src={checklist.iconUrl}
                                alt={checklist.description}
                                onClick={() => this.togglePreviewModal(checklist.clid)}
                                //onMouseOver={ this.onMouseOver }
                              />
                              {/*
																<div
																	className="text-center text-sentence w-100 h-100 px-2 py-1 align-items-center"
																	style={{
																		//display: this.state.hover ? "flex" : "none",
																		position: "absolute",
																		top: 0,
																		left: 0,
																		zIndex: 1000,
																		fontSize: "0.75em",
																		color: "white",
																		background: "rgba(100, 100, 100, 0.8)",
																		overflow: "hidden"
																	}}
																	onMouseOut={ this.onMouseOut }
																>
																</div>
																*/}
                            </div>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
                <span className="row mt-2 dashed-border" />
                <ExplorePreviewModal
                  key={this.state.currClid}
                  show={this.state.isPreviewOpen}
                  onTogglePreviewClick={this.togglePreviewModal}
                  clid={this.state.currClid}
                  pid={this.state.currPid}
                  clientRoot={this.props.clientRoot}
                  referrer={'taxa-garden'}
                ></ExplorePreviewModal>
              </div>
            )}

            <SideBarSection title="Plant Facts" items={this.state.plantFacts} />
            <SideBarSection title="Growth and Maintenance" items={this.state.growthMaintenance} />
            <SideBarSectionVendor
              title="Commercial Availability"
              items={this.state.commercialAvailability}
              clientRoot={this.props.clientRoot}
            />
            <div className="taxa-link">
              <a href={getTaxaPage(this.props.clientRoot, this.getTid())}>
                <button className="my-2 btn-primary">Core profile page</button>
              </a>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

TaxaApp.defaultProps = {
  tid: -1,
};

const headerContainer = document.getElementById('react-header');
const dataProps = JSON.parse(headerContainer.getAttribute('data-props'));
const domContainer = document.getElementById('react-taxa-garden-app');
const queryParams = getUrlQueryParams(window.location.search);
if (queryParams.search) {
  window.location = `./search.php?search=${encodeURIComponent(queryParams.search)}`;
} else if (queryParams.taxon) {
  ReactDOM.render(
    <TaxaApp tid={queryParams.taxon} defaultTitle={dataProps['defaultTitle']} clientRoot={dataProps['clientRoot']} />,
    domContainer,
  );
} else {
  window.location = '/';
}
