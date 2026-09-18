import React, { useEffect, useMemo } from 'react';
import { getGardenTaxaPage, getRareTaxaPage } from '../../common/taxaUtils';
import DescriptionTabs from '../components/DescriptionTabs.jsx';
import MapItem from '../components/MapItem.jsx';
import { CLID_RARE_ALL, RANK_GENUS } from '../constants';
import { checkNullThumbnailUrl } from '../utils.js';
import { useTaxonApi } from '../shared/useTaxonApi.js';
import { useGlossary } from '../shared/useGlossary.js';
import { useSlideshowCount } from '../shared/useSlideshowCount.js';
import TaxaPageShell from '../shared/TaxaPageShell.jsx';
import ProfileHeroImage from '../shared/ProfileHeroImage.jsx';
import TaxaImageGallery from '../shared/TaxaImageGallery.jsx';
import SidebarSection from '../shared/SidebarSection.jsx';

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
  let sppUrl = window.location.pathname + '?taxon=' + encodeURIComponent(item.tid);
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

function UnifiedTaxaCore(props) {
  const { tid, defaultTitle, clientRoot } = props;
  const { data, isLoading } = useTaxonApi(tid);
  const glossary = useGlossary();
  const [slideshowCount, updateViewport] = useSlideshowCount();

  // old .finally() ran after load; updateViewport owns the resize listener
  useEffect(() => {
    if (!isLoading) {
      updateViewport();
    }
  }, [isLoading]);

  const view = useMemo(() => {
    if (!data) {
      return {
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
        acceptedSynonyms: [],
        origin: '',
        taxalinks: [],
        gardenId: null,
        rarePlantFactSheet: '',
        accessRestricted: false,
        highlights: {},
        spp: [],
        rankId: null,
        related: [],
      };
    }

    // /taxa/rpc/api.php?taxon=2454
    let url = new URL(window.location);
    let parentQueryParams = new URLSearchParams(url.search);
    parentQueryParams.set('taxon', data.parentTid);
    let parentUrl = window.location.pathname + '?' + parentQueryParams.toString();

    let childUrl = '';
    if (data.spp.length) {
      childUrl = '#subspecies';
    }

    const relatedArr = [data.sciname, parentUrl, childUrl];

    // Show buttons in More Info in Context bar
    let moreInfo = [];
    // Show status in Context bar
    const Status = [];
    if (data.specialChecklists && data.specialChecklists.includes(CLID_RARE_ALL)) {
      const rareProfileUrl = getRareTaxaPage(clientRoot, tid);
      moreInfo.push({ title: 'Rare Plant Profile', url: rareProfileUrl });
    } else if (data.rarePlantFactSheet.length) {
      moreInfo.push({ title: 'Rare Plant Fact Sheet', url: data.rarePlantFactSheet });
    }
    if (data.gardenId > 0) {
      let gardenUrl = getGardenTaxaPage(clientRoot, data.gardenId);
      moreInfo.push({ title: 'Garden Fact Sheet', url: gardenUrl });
    }

    // Create web links for sidebar section
    let web_links = [];
    // Replace IPNI and USDA links with new ones
    const replacement_web_links = [
      {
        url: `https://www.ipni.org/search?q=${data.sciname}`,
        title: 'IPNI',
      },
      {
        url: `https://plants.usda.gov/`,
        title: 'USDA PLANTS Database',
      },
    ];

    data.taxalinks.forEach((link) => {
      // Filter IPNI, USDA link out due to being outdate
      const filterTitles = ['ipni', 'usda'];
      const linkTitle = link.title.toLowerCase();
      // Check if linkTitle contains any of the substrings
      if (filterTitles.some((sub) => linkTitle.includes(sub))) {
        return;
      }
      web_links.push(
        <div key={link.url}>
          <a href={link.url} target="_blank" rel="noreferrer">
            {link.title}
          </a>
        </div>
      );
    });

    replacement_web_links.forEach((link) => {
      web_links.push(
        <div key={link.url}>
          <a href={link.url} target="_blank" rel="noreferrer">
            {link.title}
          </a>
        </div>
      );
    });

    let synonym = '';
    if (props.synonym) {
      Object.keys(data.synonyms).map((key) => {
        if (props.synonym === data.synonyms[key].tid) {
          synonym = data.synonyms[key].sciname;
        }
      });
    }

    // Add noxious weed Status
    // Only shows Conservation Status when there's no noxious weed Status
    if (data.characteristics.noxious_weed) {
      Status.push(data.characteristics.noxious_weed);
    } else if (data.characteristics.conservation_status) {
      Status.push({ type: 'conservation_status', ...data.characteristics.conservation_status });
    }

    return {
      tid: parseInt(tid),
      sciName: data.sciname,
      author: data.author,
      basename: data.vernacular.basename,
      vernacularNames: data.vernacular.names,
      images: data.imagesBasis,
      gardenId: data.gardenId,
      rankId: data.rankId,
      descriptions: data.descriptions,
      highlights: {
        Related: relatedArr,
        Family: data.family,
        'Common Names': data.vernacular.names,
        Synonyms: data.synonyms,
        Origin: data.origin,
        Status,
        'More info': moreInfo,
      },
      taxalinks: {
        webLinks: web_links,
      },
      accessRestricted: !!data.accessRestricted,
      spp: data.spp,
      related: relatedArr,
      family: data.family,
      synonym: synonym,
      acceptedSynonyms: data.acceptedSynonyms,
    };
  }, [data, clientRoot, tid, props.synonym]);

  // choose page
  const isChooser = view.rankId <= RANK_GENUS;
  const pageTitle = isChooser
    ? defaultTitle + ' ' + (view.sciName ? view.sciName : view.family)
    : defaultTitle + ' ' + view.sciName;

  useEffect(() => {
    const titleElement = document.getElementsByTagName('title')[0];
    titleElement.innerHTML = pageTitle;
  }, [pageTitle]);

  if (isChooser) {
    checkNullThumbnailUrl(view.images.HumanObservation, '../images/icons/no-thumbnail.jpg');
    checkNullThumbnailUrl(view.images.PreservedSpecimen, '../images/icons/no-thumbnail.jpg');

    return (
      <TaxaPageShell
        containerClassName="container mx-auto py-5 taxa-detail"
        wrapperClassName="row-cols-sm-2"
        mainClassName="pr-4"
        sidebarClassName="pl-4 sidebar"
        pageTitle={pageTitle}
        titleBlock={
          <h1>
            {view.sciName} {view.author}
          </h1>
        }
        sidebar={
          <>
            <SidebarSection
              title="Context"
              items={view.highlights}
              variant="main"
              classes="highlights"
              rankId={view.rankId}
              clientRoot={clientRoot}
              glossary={glossary}
            />
            <SidebarSection
              title="Web links"
              items={view.taxalinks}
              variant="main"
              classes="weblinks"
              rankId={view.rankId}
              clientRoot={clientRoot}
            />
          </>
        }
        isLoading={isLoading}
        clientRoot={clientRoot}
      >
        <p className="mt-4">
          {/*
            Description includes HTML tags & URL-encoded characters in the db.
            It's dangerous to pull/render arbitrary HTML w/ react, so just render the
            plain text & remove any HTML in it.
          */}
          {/*this.state.descriptions.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "") */}
        </p>
        {view.descriptions.length > 0 && <DescriptionTabs descriptions={view.descriptions} glossary={glossary} />}

        {view.spp.length > 0 && (
          <div className="mt-4 dashed-border" id="subspecies">
            <h3 className="text-light-green font-weight-bold mt-2">Species, subspecies and varieties</h3>
            <div className="spp-wrapper search-result-grid">
              {view.spp.map((spp) => {
                return <SppItem item={spp} key={spp.tid} clientRoot={clientRoot} />;
              })}
            </div>
          </div>
        )}
      </TaxaPageShell>
    );
  }

  const allImages = view.images.HumanObservation.concat(view.images.PreservedSpecimen);
  const showDescriptions = view.descriptions ? true : false;
  let h2 = view.vernacularNames[0];
  let h2class = '';

  /* handle unusual cases of ambiguous synonyms like 6617 */
  let otherH2 = '';
  const numAcceptedSynonyms = view.acceptedSynonyms.length;
  const ambiguousTaxon = numAcceptedSynonyms > 0;
  const taxonWord = numAcceptedSynonyms === 1 ? 'taxon' : 'taxa';
  if (ambiguousTaxon) {
    otherH2 = `In Oregon, this name is a synonym for the following accepted ${taxonWord}: `;
    h2class = 'ambiguous';
    h2 = view.acceptedSynonyms
      .map((accepted) => {
        return (
          <a key={accepted.tid} href={`${clientRoot}/taxa/index.php?taxon=${accepted.tid}`}>
            {accepted.sciname}
          </a>
        );
      })
      .reduce((prev, curr) => [prev, ', ', curr]);
  }

  return (
    <TaxaPageShell
      containerClassName="container mx-auto py-5 taxa-detail"
      wrapperClassName="row-cols-sm-2"
      mainClassName="pr-4"
      sidebarClassName="sidebar"
      pageTitle={pageTitle}
      titleBlock={
        <>
          <h1>
            <span className="font-italic">{view.sciName}</span> {view.author}
          </h1>

          <h2 className={h2class}>
            {otherH2}
            <span className="font-italic">{h2}</span>
            {view.synonym && (
              <span className="synonym">
                {' '}
                (also known as: <span className="font-italic">{view.synonym}</span>)
              </span>
            )}
          </h2>
        </>
      }
      sidebar={
        <>
          {!ambiguousTaxon && (
            <>
              <SidebarSection
                title="Context"
                items={view.highlights}
                variant="main"
                classes="highlights"
                rankId={view.rankId}
                clientRoot={clientRoot}
                glossary={glossary}
              />
              <MapItem
                title={view.sciName}
                tid={view.tid}
                clientRoot={clientRoot}
                needsPermission={view.accessRestricted}
              />
            </>
          )}
          <SidebarSection
            title="Web links"
            items={view.taxalinks}
            variant="main"
            classes="weblinks"
            rankId={view.rankId}
            clientRoot={clientRoot}
          />
        </>
      }
      isLoading={isLoading}
      clientRoot={clientRoot}
    >
      {!ambiguousTaxon && allImages.length > 0 && <ProfileHeroImage image={allImages[0]} alt={view.sciName} />}

      {ambiguousTaxon && (
        <div className="mb-4" id="subspecies">
          <h3 className="text-light-green font-weight-bold mt-2">Accepted {taxonWord}</h3>
          <div className="spp-wrapper search-result-grid">
            {view.acceptedSynonyms.map((spp) => {
              return <SppItem item={spp} key={spp.tid} clientRoot={clientRoot} />;
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
      {showDescriptions && <DescriptionTabs descriptions={view.descriptions} glossary={glossary} />}

      {view.spp.length > 0 && (
        <div className="mt-4 dashed-border" id="subspecies">
          <h3 className="text-light-green font-weight-bold mt-2">Subspecies and varieties</h3>
          <div className="spp-wrapper search-result-grid">
            {view.spp.map((spp) => {
              return <SppItem item={spp} key={spp.tid} clientRoot={clientRoot} />;
            })}
          </div>
        </div>
      )}

      <TaxaImageGallery
        images={ambiguousTaxon ? [] : view.images.HumanObservation}
        herbariumImages={
          ambiguousTaxon || view.images.PreservedSpecimen.length === 0 ? [] : view.images.PreservedSpecimen
        }
        title={`Photo images`}
        altname={'Photo of ' + view.sciName}
        herbariumTitle={'Herbarium specimens'}
        herbariumAltname={'Herbarium specimen of ' + view.sciName}
        modalTitle={view.vernacularNames[0]}
        modalAltname={view.sciName}
        slideshowCount={slideshowCount}
        clientRoot={clientRoot}
      />
    </TaxaPageShell>
  );
}

export default UnifiedTaxaCore;
