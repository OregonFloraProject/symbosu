import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import ImageCarousel from "../common/imageCarousel.jsx";
import Loading from "../common/loading.jsx";
import ImageModal from "../common/modal.jsx";
import httpGet from "../common/httpGet.js";
import { getUrlQueryParams } from "../common/queryParams.js";
import { getTaxaPage } from "../common/taxaUtils";
import ExplorePreviewModal from "../explore/previewModal.jsx";

function showItem(item) {
  const isArray = Array.isArray(item);
  return (!isArray && item !== '') || item.length > 0;
}

// TODO(eric): extract these 4 components out from here and taxa-garden
function BorderedItem(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="list-unstyled p-0 m-0">
        { props.value.map((v) => <li key={ v }>{ v }</li>) }
      </ul>
    );
  }

  return (
    <div className={ "row dashed-border" }>
      <div className="col px-0 font-weight-bold char-label">{ props.keyName }</div>
      <div className="col px-0 char-value">{ value }</div>
    </div>
  );
}

function BorderedItemVendor(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="list-unstyled p-0 m-0">
        { props.value.map((v) => {
        		return (
        			<li key={ v.clid }><a href={ props.clientRoot + '/checklists/checklist.php?cl=' + v.clid + '&pid=4' }>{ v.name }</a></li>
        		)	 
        	})
      	}
        
      </ul>
    );
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

function SideBarSectionVendor(props) {
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
            return <BorderedItemVendor key={ key } keyName={ key } value={ val } clientRoot={ props.clientRoot } />
          })
        }
        <span className="row dashed-border"/>
    </div>
  );
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

  const titleElement = document.getElementsByTagName("title")[0];
  const pageTitle = `${props.defaultTitle} ${sciName}`
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
          <h1 className="">{ vernacularNames[0] }</h1>
          <h2 className="font-italic">{ sciName }</h2>
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
                  alt={ sciName }
                />
              </div>
            <figcaption>{ images[0].photographer}</figcaption>
            </figure>
          }
          
          <p className="mt-4">
            {/*
              Description includes HTML tags & URL-encoded characters in the db.
              It's dangerous to pull/render arbitrary HTML w/ react, so just render the
              plain text & remove any HTML in it.
            */}
            { description.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "") }
          </p>
          <div className="mt-4 dashed-border taxa-slideshows">
          
            <h3 className="text-light-green font-weight-bold mt-2">{ vernacularNames[0] } images</h3>
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
        </div>
        <ImageModal 
          show={isOpen}
          currImage={currImage}
          images={images}
          onClose={toggleImageModal}
          clientRoot={ props.clientRoot }
        >
          <h3>
            <span>{ vernacularNames[0] }</span> images
          </h3>
        </ImageModal>
        <div className="col-md-4 sidebar-section">
          <SideBarSection title="Highlights" items={ highlights } />
          { nativeGroups.length > 0 &&
          <div className={ "mb-4 sidebar-canned" }>
              <h3 className="text-light-green font-weight-bold mb-1">Native plant groups</h3>
              <p>Containing <strong>{ vernacularNames[0] }:</strong></p>
                <div className="canned-results dashed-border">
                {
                  nativeGroups.map((checklist) => {
                                
                    return (
                      <div key={ checklist.clid } className={"py-2 canned-search-result"}>
                        <h4 className="canned-title" onClick={() => togglePreviewModal(checklist.clid)}>{checklist.name}</h4>
                        <div className="card" style={{padding: "0.5em"}}>
                          <div className="card-body" style={{padding: "0"}}>
                            <div style={{ position: "relative", width: "100%", height: "7em", borderRadius: "0.25em"}}>
                              <img
                                className="d-block"
                                style={{width: "100%", height: "100%", objectFit: "cover"}}
                                src={checklist.iconUrl}
                                alt={checklist.description}
                                onClick={() => togglePreviewModal(checklist.clid)}
                                //onMouseOver={ this.onMouseOver }
                              />
                              {/*
                              <div
                                className="text-center text-sentence w-100 h-100 px-2 py-1 align-items-center"
                                style={{
                                  //display: hover ? "flex" : "none",
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
                    )
                  })
                }
                </div>
                <span className="row mt-2 dashed-border"/>						
                <ExplorePreviewModal 
                  key={currClid}
                  show={isPreviewOpen}
                  onTogglePreviewClick={togglePreviewModal}
                  clid={currClid}
                  pid={currPid}
                  clientRoot={props.clientRoot}
                  referrer={ 'taxa-garden' } 
                ></ExplorePreviewModal>
              </div>

            }

          <SideBarSection title="Plant Facts" items={ plantFacts } />
          <SideBarSection title="Growth and Maintenance" items={ growthMaintenance } />
          <SideBarSectionVendor title="Commercial Availability" items={ commercialAvailability } clientRoot={props.clientRoot}/>
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
