import "regenerator-runtime/runtime";

import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import ImageCarousel from "../common/imageCarousel.jsx";
import Loading from "../common/loading.jsx";
import ImageModal from "../common/modal.jsx";
import { addGlossaryTooltips } from "../common/glossary";
import httpGet from "../common/httpGet.js";
import { getUrlQueryParams } from "../common/queryParams.js";
import { getTaxaPage } from "../common/taxaUtils";
import MapItem from './components/MapItem.jsx';
import SideBarSection from './components/SideBarSection.jsx';
import SideBarSectionLookalikesTable from './components/SideBarSectionLookalikesTable.jsx';
import SideBarSectionSpeciesList from './components/SideBarSectionSpeciesList.jsx';
import { sortKeyedCharObject } from './utils';

const EMPTY_DATA = {
  sciName: null,
  vernacularNames: [],
  images: [],
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
  lookalikes: [],
  associatedSpecies: [],
  literature: [],
};

function rangeToString(obj) {
  // sort entries by key (cs), then map to an array of just the values (charstatename)
  const arr = sortKeyedCharObject(obj);
  if (arr.length < 1) {
    return '';
  } else if (arr.length === 1) {
    return arr[0];
  }
  return `${arr[0]} - ${arr[arr.length - 1]}`;
}

function elevationToString(obj) {
  const unitlessString = rangeToString(obj);
  return unitlessString && `${unitlessString} meters`;
}

function TaxaRareApp(props) {
  const [isLoading, setIsLoading] = useState(true);
  const [data, setData] = useState(EMPTY_DATA);
  const [glossary, setGlossary] = useState([]);
  const [isImageModalOpen, setIsImageModalOpen] = useState(false);
  const [currImage, setCurrImage] = useState(0);
  const [slideshowCount, setSlideshowCount] = useState(5);

  const tid = parseInt(props.tid);

  useEffect(() => {
    if (tid === -1) {
      window.location = "/";
    } else {
      const fetchData = async () => {
        try {
          const res = JSON.parse(await httpGet(`./rpc/api.php?taxon=${tid}&type=rare`));

          const url = new URL(window.location);
					const parentQueryParams = new URLSearchParams(url.search);
					parentQueryParams.set('taxon',res.parentTid);
					const parentUrl = 'index.php?' + parentQueryParams.toString();
          // TODO(eric) ask if this will ever be nonnull and what to link to if so --
          // index.php#subspecies perhaps?
					const childUrl = '';
					// if (res.spp.length) {
					// 	childUrl = "#subspecies";
					// }

          setData({
            sciName: res.sciname,
            vernacularNames: res.vernacular.names,
            images: res.imagesBasis.HumanObservation,
            rankId: res.rankId,
            context: {
              "Related": [res.sciname, parentUrl, childUrl],
              family: res.family,
              status: res.characteristics.conservation_status,
              ecoregion: res.characteristics.ecoregion,
              counties: [], // TODO(eric): figure out how to get this data
              habitat: res.characteristics.habitat,
              elevation: elevationToString(res.characteristics.elevation),
              floweringTime: rangeToString(res.characteristics.bloom_months),
            },
            surveyManage: {
              bestSurveyStatus: res.characteristics.best_survey_status,
              bestSurveyTime: rangeToString(res.characteristics.best_survey_months),
              threats: res.characteristics.threats,
              management: res.characteristics.management,
            },
            description: res.descriptions[0].desc, // TODO(eric): filter for the correct source?
            lookalikes: [],
            associatedSpecies: [],
            literature: [],
          });
        } catch (err) {
          // TODO(eric): add error handling
          console.error(err);
        } finally {
          setIsLoading(false);
          updateViewport();
        }
      }

      const fetchGlossary = async () => {
        try {
          const res = await httpGet('../glossary/rpc/getterms.php');
          setGlossary(JSON.parse(res));
        } catch (err) {
          // TODO(eric): add some error handling/state to page
          console.error(err);
        }
      }

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
	}
	const toggleImageModal = (_currImage) => {
    setCurrImage(_currImage);
    setIsImageModalOpen(!isImageModalOpen);
  }

  // const data = dummyData;
  const needsPermission = true;

  const titleElement = document.getElementsByTagName("title")[0];
  const pageTitle = `${props.defaultTitle} ${data.sciName}`
  titleElement.innerHTML = pageTitle;
  return (
    <div className="container mx-auto pl-4 pr-4 pt-5" style={{ minHeight: "45em" }}>
      <Loading
        clientRoot={ props.clientRoot }
        isLoading={ isLoading }
      />
      <div className="print-header">
      { pageTitle }<br />
      { window.location.href }
      </div>
      <div className="row print-start">
        <div className="col">
          <h1 className="font-italic">{ data.sciName }</h1>
          <h2 className="">{ data.vernacularNames[0] }</h2>
        </div>
        <div className="col-auto">
          <button className="d-block my-2 btn-primary print-trigger" onClick={() => window.print()}>Print page</button>
          {/*<button className="d-block my-2 btn-secondary" disabled={ true }>Add to basket</button>*/}
        </div>
      </div>
      <div className="row mt-2 main-wrapper">
        <div className="col-md-8 main-section">

          { data.images.length > 0 &&
            <figure>
              <div className="img-main-wrapper">
                <img
                  id="img-main"
                  src={ data.images[0].url }
                  alt={ data.sciName }
                />
              </div>
            <figcaption>{ data.images[0].photographer }</figcaption>
            </figure>
          }

	  { data.images.length > 0 &&
            <div className="mt-4 dashed-border taxa-slideshows">

              <h3 className="text-light-green font-weight-bold mt-2">{ data.vernacularNames[0] } images</h3>
              <div className="slider-wrapper">
              <ImageCarousel
                images={data.images}
                imageCount={ length }
                slideshowCount= { slideshowCount }
              >
                {
                  data.images.map((image,index) => {
                    return (
                      <div key={image.url}>
                        <div className="card" style={{padding: "0.6em"}}>
                          <div style={{ position: "relative", width: "100%", height: "7em", borderRadius: "0.25em"}}>

                            <img
                              className="d-block"
                              style={{width: "100%", height: "100%", objectFit: "cover"}}
                              src={image.thumbnailurl}
                              alt={image.thumbnailurl}
                              onClick={() => toggleImageModal(index)}
                            />
                          </div>
                        </div>
                      </div>
                    );
                  })
                }
              </ImageCarousel>
              </div>

            </div>
          }

          <div className={`taxa-prose${data.images.length > 0 ? ' mt-4 dashed-border' : ' no-images'}`}>
            <h2>Summary</h2>

            {data.description &&
              <>
                <h2>Taxon description</h2>
                {data.description.map((desc, index) => (
                  <p
                    key={`desc-${index}`}
                    dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(desc, glossary) }}
                  />
                ))}
              </>
            }

            <h2>Relevant literature</h2>
            {data.literature.map((entry, index) => (
              <p key={`literature-${index}`} dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(entry) }} />
            ))}
          </div>
        </div>
        <ImageModal
          show={isImageModalOpen}
          currImage={currImage}
          images={data.images}
          onClose={toggleImageModal}
          clientRoot={ props.clientRoot }
        >
          <h3>
            <span>{ data.vernacularNames[0] }</span> images
          </h3>
        </ImageModal>
        <div className="col-md-4 sidebar-section">
          <SideBarSection title="Context" items={ data.context } rankId={ data.rankId } glossary={ glossary } />
          <MapItem title={ data.sciName } tid={ tid } clientRoot={ props.clientRoot } needsPermission={ needsPermission } />
          <SideBarSection title="Survey & Manage" items={ data.surveyManage } glossary={ glossary } />
          <SideBarSectionLookalikesTable title="Look-Alikes" items={ data.lookalikes } glossary={ glossary } />
          <SideBarSectionSpeciesList title="Associated species" items={ data.associatedSpecies } />
          <div className="taxa-link">
            <a href={ getTaxaPage(props.clientRoot, tid) }><button className="d-block my-2 btn-primary">Core profile page</button></a>
          </div>
        </div>
      </div>
    </div>
  );
}

const headerContainer = document.getElementById("react-header");
const dataProps = JSON.parse(headerContainer.getAttribute("data-props"));
const domContainer = document.getElementById("react-taxa-rare-app");
const queryParams = getUrlQueryParams(window.location.search);

// Use both taxon and tid (symbiota-light) to denote the taxon
if (queryParams.tid) {
  queryParams.taxon = queryParams.tid;
}

if (queryParams.search) {
  window.location = `./search.php?search=${encodeURIComponent(queryParams.search)}`;
} else if (queryParams.taxon) {
  ReactDOM.render(
    <TaxaRareApp tid={ queryParams.taxon } defaultTitle={ dataProps["defaultTitle"] } clientRoot={ dataProps["clientRoot"] } synonym={ queryParams.synonym - 0 } />,
    domContainer
  );
} else {
  window.location = "/";
}
