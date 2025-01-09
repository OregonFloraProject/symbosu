import ReactDOM from 'react-dom';
import React, { useMemo, useState, useEffect } from 'react';
import httpGet from '../common/httpGet.js';
import { getUrlQueryParams } from '../common/queryParams.js';
import Table from './table.jsx';
import PageHeader from '../common/pageHeader.jsx';
import { getChecklistPage } from '../common/taxaUtils';
import Loading from '../common/loading.jsx';
//const ScriptLoaded = require("../../docs/ScriptLoaded").default;

import { MapContainer } from 'react-leaflet/MapContainer';
import { TileLayer } from 'react-leaflet/TileLayer';
import { Marker } from 'react-leaflet/Marker';
import MarkerClusterGroup from 'react-leaflet-markercluster';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faSearchPlus, faListUl, faChevronDown, faChevronUp, faTimesCircle } from '@fortawesome/free-solid-svg-icons';
library.add(faSearchPlus, faListUl, faChevronDown, faChevronDown, faTimesCircle);

function ChecklistTable(props) {
  const columns = useMemo(
    () => [
      {
        Header: 'Checklist Name',
        accessor: 'name', // accessor is the "key" in the data
      },
      {
        Header: 'Actions',
        accessor: 'clid',
        disableSortBy: true,
      },
      {
        Header: 'Longitude',
        accessor: 'longcentroid',
        //disableSortBy: true
      },
    ],
    [],
  );
  return (
    <div className="App">
      <Table columns={columns} data={props.checklists} pid={props.pid} clientRoot={props.clientRoot} />
    </div>
  );
}

class InventoryDetail extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isLoading: true,
      pid: null,
      projName: '',
      managers: '',
      briefDescription: '',
      fullDescription: '',
      isPublic: null,
      checklists: [],
      zoomLevel: 7,
    };
    this.getPid = this.getPid.bind(this);
    this.updateViewport = this.updateViewport.bind(this);
  }

  getPid() {
    return parseInt(this.props.pid);
  }
  updateViewport() {
    let newZoom = 7;
    if (window.innerWidth < 992) {
      newZoom = 6;
    }
    this.setState({ zoomLevel: newZoom });
  }
  componentDidMount() {
    httpGet(`./rpc/api.php?pid=${this.props.pid}`)
      .then((res) => {
        // /projects/rpc/api.php?pid=2454
        res = JSON.parse(res);
        this.setState({
          projname: res.projname,
          managers: res.managers,
          briefDescription: res.briefDescription,
          fullDescription: res.fullDescription,
          isPublic: res.isPublic,
          checklists: res.checklists,
          //googleMapUrl: googleMapUrl
        });
        const pageTitle = document.getElementsByTagName('title')[0];
        pageTitle.innerHTML = `${pageTitle.innerHTML} ${res.projname}`;
      })
      .catch((err) => {
        //window.location = "/";
        console.error(err);
      })
      .finally(() => {
        this.setState({ isLoading: false });
        this.updateViewport();
      });

    window.addEventListener('resize', this.updateViewport);
  } //componentDidMount

  render() {
    let pid = this.getPid();

    let checklistTxt = '(referenced in the map above)';
    if (pid === 3) {
      checklistTxt = '(see also the <a href="' + this.props.clientRoot + '/garden/index.php">Grow Natives</a> page)';
    }

    return (
      <div className="wrapper">
        <Loading clientRoot={this.props.clientRoot} isLoading={this.state.isLoading} />
        <div className="page-header">
          <PageHeader bgClass="explore" title={this.state.projname} />
        </div>
        <div className="container inventory-detail" style={{ minHeight: '45em' }}>
          <div className="row">
            <div className="col">
              <h2 dangerouslySetInnerHTML={{ __html: this.state.briefDescription }} />
            </div>
          </div>
          <div className="row">
            <div className="col">
              <p dangerouslySetInnerHTML={{ __html: this.state.fullDescription }}></p>
            </div>
          </div>
          {(pid == 1 || pid == 2) && (
            <div className="row mt-2 project-header">
              <div className="col">
                <h3>Interactive map</h3>
                <span className="explain">(or explore areas from list below)</span>
              </div>
            </div>
          )}
          {(pid == 1 || pid == 2) && (
            <div className="row map">
              <div className="col">
                <MapContainer center={[44.156944, -120.490556]} zoom={7} scrollWheelZoom={false}>
                  <TileLayer
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                  />
                  <MarkerClusterGroup
                    iconCreateFunction={(cluster) => {
                      const childCount = cluster.getChildCount();
                      let iconUrl;
                      if (childCount < 10) {
                        iconUrl = this.props.clientRoot + '/images/icons/map_markers/2-9.png';
                      } else if (childCount < 100) {
                        iconUrl = this.props.clientRoot + '/images/icons/map_markers/10-100.png';
                      } else {
                        iconUrl = this.props.clientRoot + '/images/icons/map_markers/101+.png';
                      }
                      // eslint-disable-next-line no-undef
                      return L.icon({ iconUrl });
                    }}
                  >
                    {this.state.checklists.map((checklist) => {
                      let position = [checklist.latcentroid, checklist.longcentroid];
                      let href = getChecklistPage(this.props.clientRoot, checklist.clid, pid);

                      return (
                        <Marker
                          key={checklist.clid}
                          // eslint-disable-next-line no-undef
                          icon={L.icon({ iconUrl: this.props.clientRoot + '/images/icons/map_markers/single.png' })}
                          position={position}
                          eventHandlers={{
                            click: () => {
                              location.href = href;
                            },
                          }}
                        />
                      );
                    })}
                  </MarkerClusterGroup>
                </MapContainer>
              </div>
            </div>
          )}
          <div className="row mt-4 project-header ">
            <div className="col research-checklists">
              <h3>Checklists</h3>
              <span className="explain" dangerouslySetInnerHTML={{ __html: checklistTxt }}></span>
            </div>
          </div>
          <div className="row mt-2 project-key project-checklists">
            <div className="col">
              <div className="project-icons">
                <FontAwesomeIcon icon="list-ul" />
              </div>
              <span className="verticalSeparator"></span>
              <p>
                <strong>EXPLORE</strong> plants that have been discovered at the listed location.
              </p>
            </div>
          </div>
          <div className="row mt-2 project-key project-identify">
            <div className="col">
              <div className="project-icons">
                <FontAwesomeIcon icon="search-plus" />
              </div>
              <span className="verticalSeparator"></span>
              <p>
                <strong>IDENTIFY</strong> a plant you&apos;ve discovered at that location, using a host of
                characteristics.
              </p>
            </div>
          </div>
          <div className="row mt-4 mb-4 checklists-table">
            <div className="col">
              <ChecklistTable checklists={this.state.checklists} pid={pid} clientRoot={this.props.clientRoot} />
            </div>
          </div>
        </div>
      </div>
    );
  }
}

InventoryDetail.defaultProps = {
  pid: -1,
};

class InventoryChooser extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isLoading: true,
      projects: [],
    };
  }
  toggleProjectDisplay = (index) => {
    let newArr = this.state.projects;
    let newVal = 'default';
    if (this.state.projects[index].display == 'default') {
      newVal = 'expanded';
    }
    newArr[index].display = newVal;
    this.setState({
      projects: newArr,
    });
  };
  componentDidMount() {
    let url = `${this.props.clientRoot}/projects/rpc/api.php`;
    httpGet(url)
      .then((res) => {
        // /projects/rpc/api.php
        res = JSON.parse(res);

        res.map((project, index) => {
          res[index].display = 'default';
        });
        this.setState({
          projects: res,
        });
        const pageTitle = document.getElementsByTagName('title')[0];
        pageTitle.innerHTML = `${pageTitle.innerHTML} Inventories`;
      })
      .catch((err) => {
        // TODO: Something's wrong
        console.error(err);
      })
      .finally(() => {
        this.setState({ isLoading: false });
      });
  } //componentDidMount
  render() {
    let inventoryImages = {
      1: 'botanical_diversity.jpg',
      2: 'species_list.jpg',
      3: 'garden_with_natives.jpg',
    };

    return (
      <div className="wrapper">
        <Loading clientRoot={this.props.clientRoot} isLoading={this.state.isLoading} />
        <div className="page-header">
          <PageHeader bgClass="explore" title={'Inventories—Places and their Plants'} />
        </div>
        <div className="container inventory-chooser" style={{ minHeight: '45em' }}>
          <div className="row">
            <div className="col">
              <h2>
                Inventories are curated species lists of a defined area. They are a great way to explore all that we
                know about the plant diversity of a place.
              </h2>
            </div>
          </div>
          <div className="row">
            <div className="col">
              <p>
                Inventories give you an in-depth look at exceptional places throughout Oregon. They are based on plant
                observations gathered by one or more researchers visiting a site, frequently over several years. Records
                making an inventory include herbarium specimens, unvouchered observations, and photographs. OregonFlora
                has also compiled and presents here 5,198 species lists (checklists) derived from observations made by
                researchers, agencies, and the public.
              </p>
              <p>
                Each inventory project has a theme and contains numerous species lists from places reflecting that
                theme. Open any list to <strong>Explore</strong> the plants discovered there; sort the list by
                scientific or common name, or view as thumbnail images. Want to identify a plant you have found at a
                listed site? Select the <strong>Identify</strong> icon to open the checklist as an interactive key.
              </p>
            </div>
          </div>
          <div className="row mt-3">
            <div className="col">
              {this.state.projects.map((project, index) => {
                let projectUrl = '';
                projectUrl = this.props.clientRoot + '/projects/index.php?pid=' + project.pid;
                let shortClass = '';
                shortClass = project.pid === 1 ? '' : ' no-map';

                return (
                  <div key={index} className="project-item">
                    {project.display == 'default' && (
                      <div className="project-default">
                        <div className="project-header">
                          <div className="more more-less" onClick={() => this.toggleProjectDisplay(index)}>
                            More
                            <FontAwesomeIcon icon="chevron-down" />
                          </div>
                          <div className="">
                            <a className="btn btn-primary" role="button" href={projectUrl}>
                              Explore
                            </a>
                            <h3>{project.projname}</h3>
                          </div>
                        </div>
                        <div
                          className="project-content"
                          dangerouslySetInnerHTML={{ __html: project.briefdescription }}
                        />
                      </div>
                    )}
                    {project.display == 'expanded' && (
                      <div className={'project-expanded' + shortClass}>
                        <div className="project-image col-12 col-md-8 p-0">
                          <h2>{project.projname}</h2>
                          <img
                            className="img-fluid"
                            src={this.props.clientRoot + '/images/inventory/' + inventoryImages[project.pid]}
                          />
                        </div>
                        <div className="col-12 col-md-4 p-0 project-other">
                          <div className="less more-less" onClick={() => this.toggleProjectDisplay(index)}>
                            <FontAwesomeIcon icon="times-circle" size="2x" />
                          </div>
                          {project.pid === 1 && (
                            <div className="project-map-image">
                              <img
                                className="img-fluid"
                                src={this.props.clientRoot + '/images/inventory/project1_map.png'}
                              />
                            </div>
                          )}
                          <div
                            className="project-description"
                            dangerouslySetInnerHTML={{ __html: project.fulldescription }}
                          />
                          <a className="btn btn-primary project-explore" role="button" href={projectUrl}>
                            Explore
                          </a>
                        </div>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    );
  }
}

const headerContainer = document.getElementById('react-header');
const dataProps = JSON.parse(headerContainer.getAttribute('data-props'));
const domContainer = document.getElementById('react-inventory-app');
const queryParams = getUrlQueryParams(window.location.search);
if (queryParams.search) {
  window.location = `./search.php?search=${encodeURIComponent(queryParams.search)}`;
} else if (queryParams.pid) {
  ReactDOM.render(
    <InventoryDetail
      pid={queryParams.pid}
      googleMapKey={dataProps['googleMapKey']}
      clientRoot={dataProps['clientRoot']}
    />,
    domContainer,
  );
} else {
  ReactDOM.render(<InventoryChooser clientRoot={dataProps['clientRoot']} />, domContainer);
}
