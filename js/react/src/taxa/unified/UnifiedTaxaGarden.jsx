import React, { useState, useEffect, useMemo } from 'react';
import { addGlossaryTooltips } from '../../common/glossary.js';
import httpGet from '../../common/httpGet.js';
import ExplorePreviewModal from '../../explore/previewModal.jsx';
import { getTaxaPage } from '../../common/taxaUtils';
import SidebarSection from '../shared/SidebarSection.jsx';
import SideBarSectionVendor from '../components/SideBarSectionVendor.jsx';
import { sortKeyedCharObject, csRangeToString, checkNullThumbnailUrl } from '../utils';
import { useTaxonApi } from '../shared/useTaxonApi.js';
import { useGlossary } from '../shared/useGlossary.js';
import { useSlideshowCount } from '../shared/useSlideshowCount.js';
import TaxaPageShell from '../shared/TaxaPageShell.jsx';
import ProfileHeroImage from '../shared/ProfileHeroImage.jsx';
import TaxaImageGallery from '../shared/TaxaImageGallery.jsx';

function UnifiedTaxaGarden({ tid, defaultTitle, clientRoot }) {
  const { data, isLoading } = useTaxonApi(tid, 'garden');
  const glossary = useGlossary();
  const [slideshowCount, updateViewport] = useSlideshowCount();
  const [nativeGroups, setNativeGroups] = useState([]);
  const [commercialAvailability, setCommercialAvailability] = useState({});
  const [isPreviewOpen, setIsPreviewOpen] = useState(false); //explorePreviewModal
  const [currClid, setCurrClid] = useState(-1); //explorePreviewModal
  const currPid = 3; //explorePreviewModal

  const view = useMemo(() => {
    if (!data) {
      return {
        sciName: '',
        basename: '',
        vernacularNames: [],
        images: [],
        description: '',
        checklists: [],
        highlights: {},
        plantFacts: {},
        growthMaintenance: {},
      };
    }
    let plantType = '';
    let foliageType = data.characteristics.foliage_type;
    plantType += foliageType.length > 0 ? `${foliageType[0]} ` : '';

    if (data.characteristics.lifespan.length > 0) {
      plantType += `${data.characteristics.lifespan[0]}`.trim() + ' ';
    }
    if (data.characteristics.plant_type.length > 0) {
      plantType += data.characteristics.plant_type.join(' or ') + ' ';
    }

    const width = sortKeyedCharObject(data.characteristics.width);
    const height = sortKeyedCharObject(data.characteristics.height);
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

    const ease_of_growth = csRangeToString(data.characteristics.growth_maintenance.ease_of_growth);

    const spreads_vigorously = data.characteristics.growth_maintenance.spreads_vigorously;

    let moisture = [];
    if (data.characteristics.moisture.length > 0) {
      moisture.push(csRangeToString(data.characteristics.moisture));
    }
    if (data.characteristics.summer_moisture.length > 0) {
      moisture.push(`${csRangeToString(data.characteristics.summer_moisture)} summer water`);
    }

    checkNullThumbnailUrl(data.imagesBasis.HumanObservation, '../images/icons/no-thumbnail.jpg');

    return {
      sciName: data.sciname,
      basename: data.vernacular.basename,
      vernacularNames: data.vernacular.names,
      images: data.imagesBasis.HumanObservation,
      description: data.gardenDescription,
      checklists: data.specialChecklists,
      highlights: {
        'Plant type': plantType,
        'Size at maturity': sizeMaturity,
        'Light tolerance': data.characteristics.sunlight,
        'Ease of growth': ease_of_growth,
      },
      plantFacts: {
        'Flower color': data.characteristics.flower_color,
        'Bloom time': csRangeToString(data.characteristics.bloom_months, '-'),
        Moisture: moisture,
        'Wildlife support': data.characteristics.wildlife_support,
      },
      growthMaintenance: {
        'Spreads vigorously': spreads_vigorously === null ? '' : spreads_vigorously,
        'Cultivation preferences': data.characteristics.growth_maintenance.cultivation_preferences,
        'Plant behavior': data.characteristics.growth_maintenance.behavior,
        Propagation: data.characteristics.growth_maintenance.propagation,
        'Landscape uses': data.characteristics.growth_maintenance.landscape_uses,
      },
    };
  }, [data]);

  // old .finally() ran after load; updateViewport owns the resize listener
  useEffect(() => {
    if (!isLoading) {
      updateViewport();
    }
  }, [isLoading]);

  useEffect(() => {
    if (!data) {
      return;
    }
    const nativeGroups = [];
    httpGet(`${clientRoot}/garden/rpc/api.php?canned=true`).then((res) => {
      let cannedSearches = JSON.parse(res); //14796, 14797, 14798, 14799, 14800
      Object.values(cannedSearches).map((checklist) => {
        let match = view.checklists.indexOf(checklist.clid);
        if (match > -1) {
          nativeGroups.push(checklist);
        }
      });
      setNativeGroups(nativeGroups);
    });
  }, [data, clientRoot, view]);

  useEffect(() => {
    if (!data) {
      return;
    }
    const commercialAvailability = {};
    var vendorURL = `${clientRoot}/checklists/rpc/api-vendor.php?action=taxa_garden&tid=${tid}`;
    httpGet(vendorURL).then((res) => {
      res = JSON.parse(res);
      Object.values(res).map((taxon) => {
        let vendors = [];
        Object.values(taxon.vendors).map((vendor) => {
          vendors.push({ clid: vendor.clid, name: vendor.name });
        });
        commercialAvailability[taxon.sciname] = vendors;
      });
      setCommercialAvailability(commercialAvailability);
    });
  }, [data, clientRoot, tid]);

  const togglePreviewModal = (_currClid) => {
    setCurrClid(_currClid);
    setIsPreviewOpen(!isPreviewOpen);
  };

  const pageTitle = `${defaultTitle} ${view.sciName}`;
  useEffect(() => {
    const titleElement = document.getElementsByTagName('title')[0];
    titleElement.innerHTML = pageTitle;
  }, [pageTitle]);

  return (
    <TaxaPageShell
      containerClassName="container mx-auto pl-4 pr-4 pt-5"
      pageTitle={pageTitle}
      titleBlock={
        <>
          <h1 className="">{view.vernacularNames[0]}</h1>
          <h2 className="font-italic">{view.sciName}</h2>
        </>
      }
      isLoading={isLoading}
      clientRoot={clientRoot}
      sidebar={
        <>
          <SidebarSection title="Highlights" items={view.highlights} variant="rare" />
          {nativeGroups.length > 0 && (
            <div className={'mb-4 sidebar-canned'}>
              <h3 className="text-light-green font-weight-bold mb-1">Native plant groups</h3>
              <p>
                Containing <strong>{view.vernacularNames[0]}:</strong>
              </p>
              <div className="canned-results dashed-border">
                {nativeGroups.map((checklist) => {
                  return (
                    <div key={checklist.clid} className={'py-2 canned-search-result'}>
                      <h4 className="canned-title" onClick={() => togglePreviewModal(checklist.clid)}>
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
                              onClick={() => togglePreviewModal(checklist.clid)}
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
                key={currClid}
                show={isPreviewOpen}
                onTogglePreviewClick={togglePreviewModal}
                clid={currClid}
                pid={currPid}
                clientRoot={clientRoot}
                referrer={'taxa-garden'}
              ></ExplorePreviewModal>
            </div>
          )}

          <SidebarSection title="Plant Facts" items={view.plantFacts} variant="rare" />
          <SidebarSection title="Growth and Maintenance" items={view.growthMaintenance} variant="rare" />
          <SideBarSectionVendor
            title="Commercial Availability"
            items={commercialAvailability}
            clientRoot={clientRoot}
          />
          <div className="taxa-link">
            <a href={getTaxaPage(clientRoot, parseInt(tid))}>
              <button className="my-2 btn-primary">Core profile page</button>
            </a>
          </div>
        </>
      }
    >
      {view.images.length > 0 && <ProfileHeroImage image={view.images[0]} alt={view.sciName} />}

      <p
        className="mt-4"
        dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(view.description, glossary) }}
      />
      <TaxaImageGallery
        title={`${view.vernacularNames[0]} images`}
        images={view.images}
        altname={'Photo of ' + view.vernacularNames[0]}
        modalTitle={view.vernacularNames[0]}
        modalAltname={view.vernacularNames[0]}
        slideshowCount={slideshowCount}
        clientRoot={clientRoot}
      />
    </TaxaPageShell>
  );
}

export default UnifiedTaxaGarden;
