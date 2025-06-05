import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import ImageCarousel from '../common/imageCarousel.jsx';
import Loading from '../common/loading.jsx';
import ImageModal from '../common/modal.jsx';
import httpGet from '../common/httpGet.js';
import { getUrlQueryParams } from '../common/queryParams.js';
import { getTaxaPage } from '../common/taxaUtils';
import DescriptionTabs from './components/DescriptionTabs.jsx';
import MapItem from './components/MapItem.jsx';
import SideBarSection from './components/SideBarSection.jsx';
import SideBarSectionLookalikesTable from './components/SideBarSectionLookalikesTable.jsx';
import SideBarSectionSpeciesList from './components/SideBarSectionSpeciesList.jsx';
import { csRangeToString } from './utils';

const EMPTY_DATA = {
  sciName: null,
  vernacularNames: [],
  images: [],
  herbariumImages: [],
  rankId: null,
  context: {
    related: [],
    family: '',
    status: {},
    ecoregion: [],
    counties: [],
    habitat: [],
    elevation: '',
    floweringTime: '',
  },
  surveyManage: {
    bestSurveyStatus: '',
    bestSurveyTime: '',
    threats: [],
    management: [],
  },
  descriptions: [],
  lookalikes: [],
  associatedSpecies: [],
  accessRestricted: false,
  legacyFactSheetUrl: null,
};

function elevationToString(obj) {
  const unitlessString = csRangeToString(obj);
  return unitlessString && `${unitlessString} meters`;
}

function TaxaRareApp(props) {
  const [isLoading, setIsLoading] = useState(true);
  const [apiError, setApiError] = useState(false);
  const [data, setData] = useState(EMPTY_DATA);
  const [glossary, setGlossary] = useState({});
  const [isImageModalOpen, setIsImageModalOpen] = useState(false);
  const [currImage, setCurrImage] = useState(0);
  const [currImageBasis, setCurrImageBasis] = useState('HumanObservation');
  const [slideshowCount, setSlideshowCount] = useState(5);

  const tid = parseInt(props.tid);

  useEffect(() => {
    if (tid === -1) {
      window.location = '/';
    } else {
      const fetchData = async () => {
        try {
          const res = JSON.parse(await httpGet(`./rpc/api.php?taxon=${tid}&type=rare`));

          const url = new URL(window.location);
          const parentQueryParams = new URLSearchParams(url.search);
          parentQueryParams.set('taxon', res.parentTid);
          const parentUrl = 'index.php?' + parentQueryParams.toString();
          // TODO(eric) ask if this will ever be nonnull and what to link to if so --
          // index.php#subspecies perhaps?
          const childUrl = '';
          // if (res.spp.length) {
          // 	childUrl = "#subspecies";
          // }

          // use profile 8 for RPG summary, and the first other profile for taxon description
          const taxonDescriptions = res.descriptions.filter((desc) => desc.profile !== 8 && desc.profile !== 9);
          const descriptions = [
            {
              source: null,
              desc: [],
              ...(res.descriptions.find((desc) => desc.profile === 8) ?? {}),
              caption: 'Summary',
            },
            {
              ...taxonDescriptions[0],
              caption: 'Taxon description',
            },
          ];

          setData({
            sciName: res.sciname,
            vernacularNames: res.vernacular.names,
            images: res.imagesBasis.HumanObservation,
            herbariumImages: res.imagesBasis.PreservedSpecimen,
            rankId: res.rankId,
            context: {
              Related: [res.sciname, parentUrl, childUrl],
              family: res.family,
              status: res.characteristics.conservation_status,
              ecoregion: res.characteristics.ecoregion,
              counties: [], // TODO(eric): figure out how to get this data
              habitat: res.characteristics.habitat,
              elevation: elevationToString(res.characteristics.elevation),
              floweringTime: csRangeToString(res.characteristics.bloom_months, '-'),
            },
            surveyManage: {
              bestSurveyStatus: res.characteristics.best_survey_status,
              bestSurveyTime: csRangeToString(res.characteristics.best_survey_months, '-'),
              threats: res.characteristics.threats,
              management: res.characteristics.management,
            },
            descriptions,
            lookalikes: res.associations['look-alike'] ?? [],
            associatedSpecies: res.associations.associatedWith ?? [],
            accessRestricted: !!res.accessRestricted,
            legacyFactSheetUrl: res.rarePlantFactSheet ?? null,
          });

          const titleElement = document.getElementsByTagName('title')[0];
          titleElement.innerHTML = `${props.defaultTitle} - ${res.sciname} - Rare Plant Profile`;
        } catch (err) {
          console.error(err);
          setApiError(true);
        } finally {
          setIsLoading(false);
          updateViewport();
        }
      };

      const fetchGlossary = async () => {
        try {
          const res = await httpGet('../glossary/rpc/getterms.php');
          setGlossary(JSON.parse(res));
        } catch (err) {
          // just log this error and don't do anything for now, since the glossary isn't strictly
          // necessary for the functioning of the page
          console.error(err);
        }
      };

      fetchData();
      fetchGlossary();
      window.addEventListener('resize', updateViewport);
    }
  }, []);

  const updateViewport = () => {
    let newSlideshowCount = 5;
    if (window.innerWidth < 1200) {
      newSlideshowCount = 4;
    }
    if (window.innerWidth < 992) {
      newSlideshowCount = 3;
    }
    setSlideshowCount(newSlideshowCount);
  };
  const toggleImageModal = (image, basis) => {
    setCurrImage(image);
    setIsImageModalOpen(!isImageModalOpen);
    setCurrImageBasis(basis);
  };

  const profileImage = data.images.length
    ? data.images[0]
    : data.herbariumImages.length
      ? data.herbariumImages[0]
      : null;

  return (
    <div className="container mx-auto py-5" style={{ minHeight: '45em' }}>
      <Loading clientRoot={props.clientRoot} isLoading={isLoading} />
      <div className="print-header">
        {`${props.defaultTitle} - ${data.sciName} - Rare Plant Profile`}
        <br />
        {window.location.href}
      </div>
      <div className="row print-start">
        <div className="col">
          <h1 className="font-italic">{data.sciName}</h1>
          <h2>{data.vernacularNames[0]}</h2>
        </div>
        <div className="col-auto">
          <button className="d-block my-2 btn-primary print-trigger" onClick={() => window.print()}>
            Print page
          </button>
        </div>
      </div>
      <div className="row mt-2 main-wrapper">
        <div className="col-md-8 pr-4 main-section">
          {apiError && (
            <div className="alert alert-danger" role="alert">
              An error occurred. Please try again later.
            </div>
          )}

          <div className="profile-type pr-4">Rare Plant Profile</div>
          <hr />

          {profileImage !== null && (
            <figure>
              <div className="img-main-wrapper">
                <img id="img-main" src={profileImage.url} alt={data.sciName} />
              </div>
              <figcaption>{profileImage.photographer}</figcaption>
            </figure>
          )}

          <div className="taxa-prose">
            <DescriptionTabs descriptions={data.descriptions} glossary={glossary} />
          </div>

          {data.images.length > 0 && (
            <div className="mt-4 dashed-border taxa-slideshows">
              <h3 className="font-weight-bold mt-2">
                <i>{data.sciName}</i> images
              </h3>
              <div className="slider-wrapper">
                <ImageCarousel images={data.images} imageCount={data.images.length} slideshowCount={slideshowCount}>
                  {data.images.map((image, index) => {
                    return (
                      <div key={image.url}>
                        <div className="card" style={{ padding: '0.6em' }}>
                          <div style={{ position: 'relative', width: '100%', height: '7em', borderRadius: '0.25em' }}>
                            <img
                              className="d-block"
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              src={image.thumbnailurl}
                              alt={image.thumbnailurl}
                              onClick={() => toggleImageModal(index, 'HumanObservation')}
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
          {data.herbariumImages.length > 0 && (
            <div className="mt-4 dashed-border taxa-slideshows">
              <h3 className="font-weight-bold mt-2">Herbarium specimens</h3>
              <div className="slider-wrapper">
                <ImageCarousel
                  images={data.herbariumImages}
                  imageCount={data.herbariumImages.length}
                  slideshowCount={slideshowCount}
                >
                  {data.herbariumImages.map((image, index) => {
                    return (
                      <div key={image.url}>
                        <div className="card" style={{ padding: '0.6em' }}>
                          <div style={{ position: 'relative', width: '100%', height: '7em', borderRadius: '0.25em' }}>
                            <img
                              className="d-block"
                              style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                              src={image.thumbnailurl}
                              alt={image.thumbnailurl}
                              onClick={() => toggleImageModal(index, 'PreservedSpecimen')}
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
        <ImageModal
          show={isImageModalOpen}
          currImage={currImage}
          images={currImageBasis === 'PreservedSpecimen' ? data.herbariumImages : data.images}
          onClose={toggleImageModal}
          clientRoot={props.clientRoot}
        >
          <h3>
            <span>{data.vernacularNames[0]}</span> images
          </h3>
        </ImageModal>
        <div className="col-md-4 sidebar-section">
          <SideBarSection title="Context" items={data.context} rankId={data.rankId} glossary={glossary} />
          <MapItem
            title={data.sciName}
            tid={tid}
            clientRoot={props.clientRoot}
            needsPermission={data.accessRestricted}
          />
          <SideBarSection title="Survey & Manage" items={data.surveyManage} glossary={glossary} />
          <SideBarSectionLookalikesTable
            title="Look-Alikes"
            items={data.lookalikes}
            glossary={glossary}
            clientRoot={props.clientRoot}
          />
          <SideBarSectionSpeciesList
            title="Associated species"
            items={data.associatedSpecies}
            clientRoot={props.clientRoot}
          />
          <div className="taxa-link">
            <a href={getTaxaPage(props.clientRoot, tid)} style={{ marginRight: '1rem' }}>
              <button className="my-2 btn-primary">Core profile page</button>
            </a>
            {data.legacyFactSheetUrl && (
              <a href={`${props.clientRoot}${data.legacyFactSheetUrl}`} target="_blank" rel="noreferrer">
                <button className="my-2 btn-primary">
                  <img
                    src={`${props.clientRoot}/images/pdf24.png`}
                    style={{ paddingRight: '3px', marginLeft: '-0.2em' }}
                  />
                  Legacy fact sheet
                </button>
              </a>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

const headerContainer = document.getElementById('react-header');
const dataProps = JSON.parse(headerContainer.getAttribute('data-props'));
const domContainer = document.getElementById('react-taxa-rare-app');
const queryParams = getUrlQueryParams(window.location.search);

// Use both taxon and tid (symbiota-light) to denote the taxon
if (queryParams.tid) {
  queryParams.taxon = queryParams.tid;
}

if (queryParams.search) {
  window.location = `./search.php?search=${encodeURIComponent(queryParams.search)}`;
} else if (queryParams.taxon) {
  ReactDOM.render(
    <TaxaRareApp
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
