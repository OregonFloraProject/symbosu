import React, { useState } from 'react';

function MapItem(props) {
  const [showOverlay, setShowOverlay] = useState(false);

  const mapImage = `${props.clientRoot}/images/maps/${props.tid}.jpg`;
  const mapLink = `${props.clientRoot}/collections/map/googlemap.php?usethes=1&taxa=${props.tid}&minClusterSetting=10&gridSizeSetting=30`;

  const linkText = props.needsPermission ? 'Locality details restricted to authorized users' : 'Click/tap to launch';
  const onClickHandler = props.needsPermission
    ? () => {
        setShowOverlay(!showOverlay);
      }
    : () => window.open(mapLink);

  return (
    <div className={'sidebar-section mb-5 distribution'}>
      <h3 className="text-light-green font-weight-bold mb-3">Distribution</h3>
      <div className={'dashed-border pt-0 map-overlay-container'}>
        <a className="map-link" onClick={onClickHandler}>
          <img src={mapImage} alt={props.title} />
          {/* Conditionally rendering the whole div is causing a weird scrolling issue on page load,
              so just toggle display: none instead. */}
          <div className={`map-overlay-box${showOverlay ? '' : ' hidden'}`}>
            <div className="map-overlay">
              Access to detailed locality data limited. Please login to view interactive map or read our{' '}
              <span
                className="inner-link"
                onClick={() => {
                  /*window.open(`${props.clientRoot}/pages/contact.php`)*/
                }}
              >
                use policy
              </span>
              .
            </div>
          </div>
        </a>
      </div>
      <div className={'map-label text-right'}>
        <a className="map-link" onClick={onClickHandler}>
          {linkText}
        </a>
      </div>
    </div>
  );
}

export default MapItem;
