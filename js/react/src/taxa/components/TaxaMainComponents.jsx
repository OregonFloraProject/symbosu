import React from 'react';
import ImageCarousel from '../../common/imageCarousel.jsx';
import ImageModal from '../../common/modal.jsx';
import Loading from '../../common/loading.jsx';
import { getUrlQueryParams } from '../../common/queryParams.js';
import DescriptionTabs from './DescriptionTabs.jsx';
import MapItem from './MapItem.jsx';
import SideBarSection from './SideBarSectionForMain.jsx';
import { checkNullThumbnailUrl } from '../utils.js';
import Lightbox from 'react-image-lightbox';

const queryParams = getUrlQueryParams(window.location.search);

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

export class TaxaChooser extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const res = this.props.res;
    const titleElement = document.getElementsByTagName('title')[0];
    const pageTitle = this.props.defaultTitle + ' ' + (res.sciName ? res.sciName : res.family);
    titleElement.innerHTML = pageTitle;
    checkNullThumbnailUrl(res.images.HumanObservation, '../images/icons/no-thumbnail.jpg');
    checkNullThumbnailUrl(res.images.PreservedSpecimen, '../images/icons/no-thumbnail.jpg');

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
              glossary={res.glossary}
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

export class TaxaDetail extends React.Component {
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
    const images = this.state.currImageBasis || [];
    const photoIndex = this.state.currImage || 0;
    const currentImg = images[photoIndex];
    const res = this.props.res;

    let lightboxCaption = null;
    if (currentImg) {
      const recordLink = currentImg.occid 
        ? `${this.props.clientRoot}/collections/individual/index.php?occid=${currentImg.occid}`
        : `${this.props.clientRoot}/imagelib/imgdetails.php?imgid=${currentImg.imgid}`;

      lightboxCaption = (
        <div className="lightbox-caption-container">
          
          <div className="lightbox-caption-name">
            <span className="font-italic">{res.sciName}</span>
          </div>

          <div className="lightbox-caption-loc desktop-only">
            {currentImg.fulldate && <span>{currentImg.fulldate} </span>}
            {currentImg.locality && (
              <span>
                in <a href={recordLink} target="_blank" rel="noopener noreferrer">{currentImg.locality}</a>
              </span>
            )}
          </div>

          <div className="lightbox-caption-middle desktop-only">
            {currentImg.photographer && <div>&copy; {currentImg.photographer}</div>}
            {currentImg.collectionname && <div>Courtesy of {currentImg.collectionname}</div>}
            {currentImg.copyright && <div>License: {currentImg.copyright}</div>}
          </div>

          <details className="lightbox-mobile-details mobile-only">
            <summary className="lightbox-mobile-summary">More Info</summary>
            
            <div className="lightbox-caption-loc">
              {currentImg.fulldate && <span>{currentImg.fulldate} </span>}
              {currentImg.locality && (
                <span>
                  in <a href={recordLink} target="_blank" rel="noopener noreferrer">{currentImg.locality}</a>
                </span>
              )}
            </div>

            <div className="lightbox-caption-middle">
              {currentImg.photographer && <div>&copy; {currentImg.photographer}</div>}
              {currentImg.collectionname && <div>Courtesy of {currentImg.collectionname}</div>}
              {currentImg.copyright && <div>License: {currentImg.copyright}</div>}
            </div>
          </details>

          <div className="lightbox-caption-right">
            <a 
              href={recordLink} 
              target="_blank" 
              rel="noopener noreferrer"
              className="lightbox-caption-btn"
            >
              Full Record &#x2197;
            </a>
          </div>

        </div>
      );
    }
    const pageTitle = this.props.defaultTitle + ' ' + res.sciName;
    const titleElement = document.getElementsByTagName('title')[0];
    titleElement.innerHTML = pageTitle;
    const allImages = res.images.HumanObservation.concat(res.images.PreservedSpecimen);
    const showDescriptions = res.descriptions ? true : false;
    let h2 = res.vernacularNames[0];
    let h2class = '';

    /* handle unusual cases of ambiguous synonyms like 6617 */
    let otherH2 = '';
    const numAcceptedSynonyms = res.acceptedSynonyms.length;
    const ambiguousTaxon = numAcceptedSynonyms > 0;
    const taxonWord = numAcceptedSynonyms === 1 ? 'taxon' : 'taxa';
    if (ambiguousTaxon) {
      otherH2 = `In Oregon, this name is a synonym for the following accepted ${taxonWord}: `;
      h2class = 'ambiguous';
      h2 = res.acceptedSynonyms
        .map((accepted) => {
          return (
            <a key={accepted.tid} href={`${this.props.clientRoot}/taxa/index.php?taxon=${accepted.tid}`}>
              {accepted.sciname}
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
            {!ambiguousTaxon && allImages.length > 0 && (
              <figure>
                <div className="img-main-wrapper">
                  <img id="img-main" src={allImages[0].url} alt={res.sciName} />
                </div>
                <figcaption>{allImages[0].photographer}</figcaption>
              </figure>
            )}

            {ambiguousTaxon && (
              <div className="mb-4" id="subspecies">
                <h3 className="text-light-green font-weight-bold mt-2">Accepted {taxonWord}</h3>
                <div className="spp-wrapper search-result-grid">
                  {res.acceptedSynonyms.map((spp) => {
                    return <SppItem item={spp} key={spp.tid} clientRoot={this.props.clientRoot} />;
                  })}
                </div>
              </div>
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

            {!ambiguousTaxon && res.images.HumanObservation.length > 0 && (
              <ImageCarousel
                title={`Photo images`}
                images={res.images.HumanObservation}
                imageCount={res.images.HumanObservation.length}
                altname={"Photo of " + res.sciName}
                slideshowCount={res.slideshowCount}
                onClick={(index) => this.toggleImageModal(index, 'HumanObservation')}
              />
            )}

            {!ambiguousTaxon && res.images.PreservedSpecimen.length > 0 && (
              <ImageCarousel
                title={`Herbarium specimens`}
                images={res.images.PreservedSpecimen}
                imageCount={res.images.PreservedSpecimen.length}
                altname={"Herbarium specimen of " + res.sciName}
                slideshowCount={res.slideshowCount}
                onClick={(index) => this.toggleImageModal(index, 'PreservedSpecimen')}
              />
            )}
          </div>
          <div className="col-md-4 sidebar sidebar-section">
            {!ambiguousTaxon && 
              (<>
              <SideBarSection
                title="Context"
                items={res.highlights}
                classes="highlights"
                rankId={res.rankId}
                clientRoot={this.props.clientRoot}
                glossary={res.glossary}
              />
              <MapItem
                title={res.sciName}
                tid={res.tid}
                clientRoot={this.props.clientRoot}
                needsPermission={res.accessRestricted}
              />
              </>)
            }
            <SideBarSection
              title="Web links"
              items={res.taxalinks}
              classes="weblinks"
              rankId={res.rankId}
              clientRoot={this.props.clientRoot}
            />
          </div>
        </div>
        {this.state.isOpen && currentImg && (
          <Lightbox
            mainSrc={images[photoIndex].url}
            nextSrc={images.length > 1 ? images[(photoIndex + 1) % images.length].url : undefined}
            prevSrc={images.length > 1 ? images[(photoIndex + images.length - 1) % images.length].url : undefined}
            imagePadding={typeof window !== 'undefined' && window.innerWidth <= 768 ? 10 : 85}
            onCloseRequest={() => this.setState({ isOpen: false })}
            onMovePrevRequest={() =>
              this.setState({
                currImage: (photoIndex + images.length - 1) % images.length,
              })
            }
            onMoveNextRequest={() =>
              this.setState({
                currImage: (photoIndex + 1) % images.length,
              })
            }
            imageCaption={lightboxCaption}
            toolbarButtons={[
              <div key="counter" style={{ color: '#bbb', fontSize: '1.2rem', paddingRight: '20px', lineHeight: '50px' }}>
                {photoIndex + 1} of {images.length}
              </div>,
              <button
                key="fullscreen"
                type="button"
                className="ril__toolbarItemChild ril__builtinButton"
                title="Toggle Fullscreen"
                style={{
                  background: 'url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGcgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIGZpbGw9Im5vbmUiPjxwYXRoIGQ9Ik0yIDdWMmg1Ii8+PHBhdGggZD0iTTE4IDdWMmgtNSIvPjxwYXRoIGQ9Ik0yIDEzdjVoNSIvPjxwYXRoIGQ9Ik0xOCAxM3Y1aC01Ii8+PC9nPjwvc3ZnPg==") no-repeat center'
                }}
                onClick={() => {
                  if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                  } else {
                    document.exitFullscreen();
                  }
                }}
              />
            ]}
            reactModalStyle={{ overlay: { zIndex: 100001 } }}
          />
        )}
      </div>
    );
  }
}