import ReactDOM from 'react-dom';
import React from 'react';
import httpGet from '../common/httpGet.js';
import { getUrlQueryParams } from '../common/queryParams.js';
import { getGardenTaxaPage, getRareTaxaPage } from '../common/taxaUtils';
import { TaxaChooser, TaxaDetail } from './components/TaxaMainComponents.jsx';
import { CLID_RARE_ALL, RANK_GENUS } from './constants';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faArrowCircleUp,
  faArrowCircleDown,
  faEdit,
  faChevronDown,
  faChevronUp,
} from '@fortawesome/free-solid-svg-icons';
library.add(faArrowCircleUp, faArrowCircleDown, faEdit, faChevronDown, faChevronUp);

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
      acceptedSynonyms: [],
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
          } else if (res.rarePlantFactSheet.length) {
            moreInfo.push({ title: 'Rare Plant Fact Sheet', url: res.rarePlantFactSheet });
          }
          if (res.gardenId > 0) {
            let gardenUrl = getGardenTaxaPage(this.props.clientRoot, res.gardenId);
            moreInfo.push({ title: 'Garden Fact Sheet', url: gardenUrl });
          }

          // Create web links for sidebar section
          let web_links = []
          // Replace IPNI and USDA links with new ones
          const replacement_web_links = [
            {
              url: `https://www.ipni.org/search?q=${res.sciname}`,
              title: 'IPNI'
            },
            {
              url: `https://plants.usda.gov/`,
              title: 'USDA PLANTS Database'
            }
          ];

          res.taxalinks.forEach((link) => {
            // Filter IPNI, USDA link out due to being outdate
            const filterTitles = ['ipni', 'usda'];
            const linkTitle = link.title.toLowerCase();
            // Check if linkTitle contains any of the substrings
            if (filterTitles.some(sub => linkTitle.includes(sub))) {
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
            acceptedSynonyms: res.acceptedSynonyms,
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
