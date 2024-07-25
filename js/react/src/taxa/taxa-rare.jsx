import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import ImageCarousel from "../common/imageCarousel.jsx";
import Loading from "../common/loading.jsx";
import ImageModal from "../common/modal.jsx";
import httpGet from "../common/httpGet.js";
import { getUrlQueryParams } from "../common/queryParams.js";
import { getTaxaPage } from "../common/taxaUtils";

function showItem(item) {
  const isArray = Array.isArray(item);
  return (!isArray && item !== '') || item.length > 0;
}

// TODO(eric): extract these components out from here and taxa-garden?
function BorderedItem(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="list-unstyled p-0 m-0">
        { props.value.map((v) => <li key={ v }>{ v }</li>) }
      </ul>
    );
  } else if (typeof value === 'object') {
    value = (
      <ul className="list-unstyled p-0 m-0">
        { Object.entries(props.value).map(([k, v]) => <li key={ k }>{ k } { v }</li>) }
      </ul>
    )
  }

  return (
    <div className={ "row dashed-border" }>
      <div className="col px-0 font-weight-bold char-label">{ props.keyName }</div>
      <div className="col px-0 char-value">{ value }</div>
    </div>
  );
}

function SideBarSection(props) {
  let itemKeys = Object.keys(props.items);
  itemKeys = itemKeys.filter((k) => {
    const v = props.items[k];
    return showItem(v);
  });

  return (
      <div className={ "sidebar-section mb-4 " + (itemKeys.length > 0 ? "" : "d-none") }>
        <h3 className="text-light-green font-weight-bold mb-3">{ props.title }</h3>
        {
          itemKeys.map((key) => {
            const val = props.items[key];
            return <BorderedItem key={ key } keyName={ key } value={ val } />
          })
        }
        <span className="row dashed-border"/>
    </div>
  );
}

function SideBarSectionList(props) {
  const { items } = props
  return (
    <div className={ "sidebar-section mb-4 " + (items.length > 0 ? "" : "d-none") }>
      <h3 className="text-light-green font-weight-bold mb-3">{ props.title }</h3>
      <span className="row dashed-border"/>
      {
        items.map((val) => {
          return (
            <div className="associated-sciname" key={val}>{val}</div>
          );
        })
      }
      <span className="row dashed-border" />
    </div>
  )
}

function LookalikesTableRow(props) {
  return (
    <div className={`row dashed-border${props.isHeader ? ' is-header' : ''}`}>
      <div className={`col px-0 char-label${props.isRow ? ' lookalike-sciname' : ''}`}>{ props.keyName }</div>
      <div className="col px-0 char-value">{ props.value }</div>
    </div>
  );
}

function SideBarSectionTable(props) {
  const { items } = props;
  return (
      <div className={ "sidebar-section mb-4 " + (items.length > 0 ? "" : "d-none") }>
        <h3 className="text-light-green font-weight-bold mb-3">{ props.title }</h3>
        <LookalikesTableRow isHeader keyName="Taxon" value="Differs from featured plant by" />
        {items.map(({ taxon, description }) => (
          <LookalikesTableRow isRow keyName={taxon} value={description} />
        ))}
        <span className="row dashed-border"/>
    </div>
  );
}

const dummyData = {
  vernacularNames: ['Pale larkspur'],
  sciName: 'Delphinium leucophaeum',
  context: {
    family: 'Ranunculaceae',
    status: {
      ranking: 'G2/S2',
      federal: 'Species of concern',
      state: 'Endangered',
      orbic: 'List 1',
    },
    ecoregion: 'Willamette Valley',
    counties: ['Clackamas', 'Multnomah', 'Washington'],
    habitat: ['rock/cliff/scree/talus/bare ground', 'urban/agricultural/roadsides/human disturbance sites'],
    elevation: '15 - 320 meters',
    floweringTime: ['April', 'May'],
  },
  surveyManage: {
    bestSurveyStatus: 'flowering',
    bestSurveyTime: 'late May - early August',
    threats: ['development', 'habitat loss', 'hybridization', 'invasive species'],
    management: ['habitat preservation/conservation'],
  },
  lookalikes: [
    { taxon: 'Delphinium pavoneaceum', description: 'taller; larger flower parts; sepals +/- reflexed to spreading, lower petals with hairy tuft at base of blade, inflorescence raceme wider below, narrowed above' },
    { taxon: 'D. nuttallii', description: 'blue sepals' },
  ],
  associatedSpecies: [
    'Anthoxanthum odoratum',
    'Aquilegia formosa',
    'Arbutus menziesii',
    'Bromus',
    'Camassia quamash',
    'Collinsia parviflora',
    'etc.',
  ],
  description: {
    plants: '3-6 dm tall, root fleshy, tuberous.<br /><br />Stems usually single, slender, or rarely thickened, easily detached from tuber, puberulent.',
    leaves: 'principally cauline, the lower ones withered by flowering time, blades glabrous or puberulent, 2-7 cm wide, 1-3 times dissected, ultimate segments linear to oblanceolate or broadly elliptic, margins smooth.',
    inflorescences: 'simple of with several axillary branches below the terminal raceme, 1-20 flowers per stem and branch, pedicels 3/4-6 times the length of calyx spur, erect to spreading, puberulent.',
    flowers: 'sepals light yellow or white with bluish tips, spreading, 8-15 mm long, spur 9-11 mm. Lower petals white, 4-6 mm, emarginate or cleft up to 1 mm.',
    fruits: 'follicles erect, 10-15 mm, glabrous or puberulent.',
    seeds: 'wing-margined.<br /><br />2n = 16.<br /><br />River bluffs, cliffs, talus, rocky slopes and meadows, roadsides, low elevations. WV. WA. Native.',
  },
  habitatNotes: '<i>Delphinium leucophaeum</i> grows on the edges of oak woodlands, often associated with rocky, gravelly areas such as roadside ditches, rocky slopes, where materials accumulate from landslides or other erosion activities, and lowland meadows. Plants are observed in shallow soils high in organic matter and sand. Occurrences encompass a gradient of slopes and exposures, from flat areas to steep slopes, in full sun to relatively depp shade. Oregon\'s populations are restricted to the northern Willamette Valley in Clackamas, Marion, Multnomah, and Washington Counties. Further north, a small disjunct population can be found in Lewis County, Washington.',
  ecologyNotes: '<i>Delphinium leucophaeum</i> grows on the edges of oak woodlands, often associated with rocky, gravelly areas such as roadside ditches, rocky slopes, where materials accumulate from landslides or other erosion activities, and lowland meadows. Plants are observed in shallow soils high in organic matter and sand. Occurrences encompass a gradient of slopes and exposures, from flat areas to steep slopes, in full sun to relatively depp shade. Oregon\'s populations are restricted to the northern Willamette Valley in Clackamas, Marion, Multnomah, and Washington Counties. Further north, a small disjunct population can be found in Lewis County, Washington.',
  conservationNotes: '<i>Delphinium leucophaeum</i> grows on the edges of oak woodlands, often associated with rocky, gravelly areas such as roadside ditches, rocky slopes, where materials accumulate from landslides or other erosion activities, and lowland meadows. Plants are observed in shallow soils high in organic matter and sand. Occurrences encompass a gradient of slopes and exposures, from flat areas to steep slopes, in full sun to relatively depp shade. Oregon\'s populations are restricted to the northern Willamette Valley in Clackamas, Marion, Multnomah, and Washington Counties. Further north, a small disjunct population can be found in Lewis County, Washington.',
  literature: ['Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.<br />https://www.npmjs.com/package/less-loader/v/5.0.0', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.']
}

function TaxaRareApp(props) {
  const [isLoading, setIsLoading] = useState(true);
  const [sciName, setSciName] = useState('');
  const [basename, setBasename] = useState('');
  const [vernacularNames, setVernacularNames] = useState([]);
  const [images, setImages] = useState([]);
  const [description, setDescription] = useState("");
  const [highlights, setHighlights] = useState({});
  const [plantFacts, setPlantFacts] = useState({});
  const [growthMaintenance, setGrowthMaintenance] = useState({});
  const [commercialAvailability, setCommercialAvailability] = useState({});
  const [isOpen, setIsOpen] = useState(false); //imagemodal
  const [isPreviewOpen, setIsPreviewOpen] = useState(false); //explorePreviewModal
  const [currClid, setCurrClid] = useState(-1); //explorePreviewModal
  const [currPid, setCurrPid] = useState(3); //explorePreviewModal
  const [tid, setTid] = useState(parseInt(props.tid));
  const [currImage, setCurrImage] = useState(0);
  const [checklists, setChecklists] = useState([]);
  const [nativeGroups, setNativeGroups] = useState([]);
  const [slideshowCount, setSlideshowCount] = useState(5);

  useEffect(() => {
    // TODO(eric): switch to async/await
    if (tid === -1) {
      window.location = "/";
    } else {
    	let url = `./rpc/api.php?taxon=${tid}`;
      httpGet(url)
        .then((res) => {
          res = JSON.parse(res);
          let plantType = '';
          let foliageType = res.characteristics.features.foliage_type;
          plantType += foliageType.length > 0 ? `${foliageType[0]} `: '';

          if (res.characteristics.features.lifespan.length > 0) {
            plantType += `${res.characteristics.features.lifespan[0]}`.trim() + " ";
          }
          if (res.characteristics.features.plant_type.length > 0) {
            plantType += res.characteristics.features.plant_type.join(" or ") + " ";
          }

          const width = res.characteristics.width;
          const height = res.characteristics.height;
          let sizeMaturity = "";
          if (height.length > 0) {
            sizeMaturity += height.length > 1 ? `${height[0]}-${height[height.length - 1]}` : `${height[0]}`;
            sizeMaturity += "' high";
          }
          if (width.length > 0) {
            if (sizeMaturity !== '') {
              sizeMaturity += ", ";
            }
            sizeMaturity += (width.length > 1 ? `${width[0]}-${width[width.length - 1]}` : `${width[0]}`);
            sizeMaturity += "' wide";
          }

          let ease_of_growth = res.characteristics.growth_maintenance.ease_of_growth;
          ease_of_growth = ease_of_growth.length > 0 ? ease_of_growth[0] : "";

          const spreads_vigorously = res.characteristics.growth_maintenance.spreads_vigorously;
          
          let moisture = [];
          if (res.characteristics.moisture.length > 0) {
            moisture.push(`${res.characteristics.moisture[0]}`.trim());
          }
          if (res.characteristics.summer_moisture.length > 0) {
            moisture.push(`${res.characteristics.summer_moisture[0]}`.trim() + " summer water");
          }

          setSciName(res.sciname);
          setBasename(res.vernacular.basename);
          setVernacularNames(res.vernacular.names);
          setImages(res.imagesBasis.HumanObservation);
          setDescription(res.gardenDescription);
          setChecklists(res.checklists);
          setHighlights({
            "Plant type": plantType,
            "Size at maturity": sizeMaturity,
            "Light tolerance": res.characteristics.sunlight,
            "Ease of growth": ease_of_growth
          });
          setPlantFacts({
            "Flower color": res.characteristics.features.flower_color,
            "Bloom time": res.characteristics.features.bloom_months,
            "Moisture": moisture,
            "Wildlife support": res.characteristics.features.wildlife_support
          });
          setGrowthMaintenance({
            "Spreads vigorously": spreads_vigorously === null ? "" : spreads_vigorously,
            "Cultivation preferences": res.characteristics.growth_maintenance.cultivation_preferences,
            "Plant behavior": res.characteristics.growth_maintenance.behavior,
            "Propagation": res.characteristics.growth_maintenance.propagation,
            "Landscape uses": res.characteristics.growth_maintenance.landscape_uses
          });
          const nativeGroups = [];
					httpGet(`${props.clientRoot}/garden/rpc/api.php?canned=true`)
					.then((cannedSearchesRes) => {
						let cannedSearches = JSON.parse(cannedSearchesRes);//14796, 14797, 14798, 14799, 14800
						Object.entries(cannedSearches).map(([key, checklist]) => {
							let match = res.checklists.indexOf(checklist.clid);
							if (match > -1) {
								nativeGroups.push(checklist);
							}
						})
						setNativeGroups(nativeGroups);
					});
					
					const commercialAvailability = {};
					var vendorURL = `${props.clientRoot}/checklists/rpc/api-vendor.php?action=taxa_garden&tid=${tid}`;
					httpGet(vendorURL)
					.then((res) => {
          	res = JSON.parse(res);
						Object.entries(res).map(([key, taxon]) => {
							let vendors = [];
							Object.entries(taxon.vendors).map(([key,vendor]) => {
								vendors.push({'clid':vendor.clid,'name':vendor.name});
							});
							commercialAvailability[taxon.sciname] = vendors;
						})
						setCommercialAvailability(commercialAvailability);
					});
        })
        .catch((err) => {
          // TODO: Something's wrong
          console.error(err);
        })
				.finally(() => {
					setIsLoading(false);
					updateViewport();
				});
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
    setIsOpen(!isOpen);
  }
	const togglePreviewModal = (_currClid) => {
    setCurrClid(_currClid);
    setIsPreviewOpen(!isPreviewOpen);
  }

  const data = dummyData;

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
          <h1 className="">{ data.vernacularNames[0] }</h1>
          <h2 className="font-italic">{ data.sciName }</h2>
        </div>
        <div className="col-auto">
          <button className="d-block my-2 btn-primary print-trigger" onClick={() => window.print()}>Print page</button>
          {/*<button className="d-block my-2 btn-secondary" disabled={ true }>Add to basket</button>*/}
        </div>
      </div>
      <div className="row mt-2 main-wrapper">
        <div className="col-md-8 main-section">
          
          { images.length > 0 && 
            <figure>
              <div className="img-main-wrapper">
                <img
                  id="img-main"
                  src={ images[0].url }
                  alt={ data.sciName }
                />
              </div>
            <figcaption>{ images[0].photographer }</figcaption>
            </figure>
          }

          <div className="mt-4 dashed-border taxa-slideshows">
          
            <h3 className="text-light-green font-weight-bold mt-2">{ data.vernacularNames[0] } images</h3>
            <div className="slider-wrapper">
            <ImageCarousel
              images={images}
              imageCount={ length } 
              slideshowCount= { slideshowCount } 
            >
              {
                images.map((image,index) => {
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
          
          <div className="mt-4 dashed-border taxa-prose">
            <h2>Summary</h2>
            <p>
              {/*
                Description includes HTML tags & URL-encoded characters in the db.
                It's dangerous to pull/render arbitrary HTML w/ react, so just render the
                plain text & remove any HTML in it.
              */}
              <span class="taxa-prose-section-title">Habitat & distribution</span>{ data.habitatNotes.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "") }
            </p>
            <p>
              <span class="taxa-prose-section-title">Ecology, natural history & pollinator biology</span>{ data.ecologyNotes.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "") }
            </p>
            <p>
              <span class="taxa-prose-section-title">Trends & conservation</span>{ data.conservationNotes.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "") }
            </p>

            <h2>Taxon description</h2>
            {Object.entries(data.description).map(([key, value]) => (
              <p><span class="taxa-prose-section-title">{key}</span>{ value.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "") }</p>
            ))}

            <h2>Relevant literature</h2>
            {data.literature.map(entry => (
              <p>{entry}</p>
            ))}
          </div>
        </div>
        <ImageModal 
          show={isOpen}
          currImage={currImage}
          images={images}
          onClose={toggleImageModal}
          clientRoot={ props.clientRoot }
        >
          <h3>
            <span>{ data.vernacularNames[0] }</span> images
          </h3>
        </ImageModal>
        <div className="col-md-4 sidebar-section">
          <SideBarSection title="Context" items={ data.context } />

          <div className={ "mb-4 sidebar-canned" }>
            <h3 className="text-light-green font-weight-bold mb-1">Distribution</h3>
            <span className="row mt-2 dashed-border"/>
          </div>

          <SideBarSection title="Survey & Manage" items={ data.surveyManage } />
          <SideBarSectionTable title="Look-Alikes" items={ data.lookalikes } />
          <SideBarSectionList title="Associated species" items={ data.associatedSpecies } />
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
