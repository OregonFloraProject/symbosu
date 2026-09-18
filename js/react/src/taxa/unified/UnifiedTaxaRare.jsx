import React, { useEffect, useMemo } from 'react';
import { getTaxaPage } from '../../common/taxaUtils';
import DescriptionTabs from '../components/DescriptionTabs.jsx';
import MapItem from '../components/MapItem.jsx';
import SideBarSectionLookalikesTable from '../components/SideBarSectionLookalikesTable.jsx';
import SideBarSectionSpeciesList from '../components/SideBarSectionSpeciesList.jsx';
import { csRangeToString, checkNullThumbnailUrl } from '../utils';
import { useTaxonApi } from '../shared/useTaxonApi.js';
import { useGlossary } from '../shared/useGlossary.js';
import { useSlideshowCount } from '../shared/useSlideshowCount.js';
import TaxaPageShell from '../shared/TaxaPageShell.jsx';
import ProfileHeroImage from '../shared/ProfileHeroImage.jsx';
import TaxaImageGallery from '../shared/TaxaImageGallery.jsx';
import SidebarSection from '../shared/SidebarSection.jsx';

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

function UnifiedTaxaRare({ tid, defaultTitle, clientRoot, synonym }) {
  const { data, apiError, isLoading } = useTaxonApi(tid, 'rare');
  const glossary = useGlossary();
  const [slideshowCount, updateViewport] = useSlideshowCount();

  const mappedData = useMemo(() => {
    if (!data) {
      return EMPTY_DATA;
    }

    const res = data;

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
    ].filter((d) => d.desc && d.desc.length > 0);

    checkNullThumbnailUrl(res.imagesBasis.HumanObservation, '../images/icons/no-thumbnail.jpg');
    checkNullThumbnailUrl(res.imagesBasis.PreservedSpecimen, '../images/icons/no-thumbnail.jpg');

    return {
      sciName: res.sciname,
      vernacularNames: res.vernacular.names,
      images: res.imagesBasis.HumanObservation,
      herbariumImages: res.imagesBasis.PreservedSpecimen,
      rankId: res.rankId,
      context: {
        Related: [res.sciname, parentUrl, childUrl],
        family: res.family,
        synonyms: res.synonyms,
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
    };
  }, [data]);

  useEffect(() => {
    if (!data) {
      return;
    }
    const titleElement = document.getElementsByTagName('title')[0];
    titleElement.innerHTML = `${defaultTitle} - ${data.sciname} - Rare Plant Profile`;
  }, [data, defaultTitle]);

  useEffect(() => {
    if (!isLoading) {
      updateViewport();
    }
  }, [isLoading]);

  const profileImage = mappedData.images.length
    ? mappedData.images[0]
    : mappedData.herbariumImages.length
      ? mappedData.herbariumImages[0]
      : null;

  return (
    <TaxaPageShell
      containerClassName="container mx-auto py-5"
      mainClassName="pr-4"
      pageTitle={`${defaultTitle} - ${mappedData.sciName} - Rare Plant Profile`}
      titleBlock={
        <>
          <h1 className="font-italic">{mappedData.sciName}</h1>
          <h2>{mappedData.vernacularNames[0]}</h2>
        </>
      }
      sidebar={
        <>
          <SidebarSection
            title="Context"
            items={mappedData.context}
            variant="rare"
            rankId={mappedData.rankId}
            glossary={glossary}
            isTaxaRare
          />
          <MapItem
            title={mappedData.sciName}
            tid={tid}
            clientRoot={clientRoot}
            needsPermission={mappedData.accessRestricted}
          />
          <SidebarSection title="Survey & Manage" items={mappedData.surveyManage} variant="rare" glossary={glossary} />
          <SideBarSectionLookalikesTable
            title="Look-Alikes"
            items={mappedData.lookalikes}
            glossary={glossary}
            clientRoot={clientRoot}
          />
          <SideBarSectionSpeciesList
            title="Associated species"
            items={mappedData.associatedSpecies}
            clientRoot={clientRoot}
          />
          <div className="taxa-link">
            <a href={getTaxaPage(clientRoot, tid)} style={{ marginRight: '1rem' }}>
              <button className="my-2 btn-primary">Core profile page</button>
            </a>
            {mappedData.legacyFactSheetUrl && (
              <a href={`${clientRoot}${mappedData.legacyFactSheetUrl}`} target="_blank" rel="noreferrer">
                <button className="my-2 btn-primary">
                  <img
                    src={`${clientRoot}/images/pdf24.png`}
                    style={{ paddingRight: '3px', marginLeft: '-0.2em' }}
                  />
                  Legacy fact sheet
                </button>
              </a>
            )}
          </div>
        </>
      }
      isLoading={isLoading}
      clientRoot={clientRoot}
    >
      {apiError && (
        <div className="alert alert-danger" role="alert">
          An error occurred. Please try again later.
        </div>
      )}

      <div className="profile-type pr-4">Rare Plant Profile</div>
      <hr />

      {profileImage !== null && <ProfileHeroImage image={profileImage} alt={mappedData.sciName} />}

      {mappedData.descriptions.length > 0 && (
        <div className="taxa-prose">
          <DescriptionTabs descriptions={mappedData.descriptions} glossary={glossary} />
        </div>
      )}

      <TaxaImageGallery
        title={
          <span>
            <i>{mappedData.sciName}</i> images
          </span>
        }
        images={mappedData.images}
        herbariumImages={mappedData.herbariumImages}
        altname={'Photo of ' + mappedData.sciName}
        herbariumTitle="Herbarium specimens"
        herbariumAltname={'Herbarium specimen of ' + mappedData.sciName}
        modalTitle={mappedData.vernacularNames[0]}
        modalAltname={mappedData.sciName}
        slideshowCount={slideshowCount}
        clientRoot={clientRoot}
      />
    </TaxaPageShell>
  );
}

export default UnifiedTaxaRare;
