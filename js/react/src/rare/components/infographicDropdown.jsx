import React, { useState } from 'react';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faChevronUp } from '@fortawesome/free-solid-svg-icons';
library.add(faChevronUp);

function InfographicDropdown(props) {
  const [isCollapsed, setIsCollapsed] = useState(false);

  return (
    <div
      id="infographic-dropdown"
      className="container-fluid d-print-none"
      style={{
        position: 'relative',
        backgroundImage: `url(${props.clientRoot}/images/header/h1casrub2.jpg)`,
      }}
    >
      <div className="container mx-auto p-4">
        <div className="row" style={{ position: 'relative' }}>
          <div className="col">
            <h1 style={{ fontWeight: 'bold', width: '90%' }}>Rare Plant Guide</h1>
            <h3 id="infographic-inner-text" className={'w-90 will-collapse' + (isCollapsed ? ' is-collapsed' : '')}>
              A tool to help land managers conserve Oregon&apos;s imperiled species and bring new appreciation of these
              special plants to everyone.
            </h3>
          </div>
          <button
            style={{
              position: 'absolute',
              bottom: 0,
              right: 0,
              marginRight: '-2.5em',
              marginBottom: '-3.5em',
              background: 'none',
            }}
            onClick={() => {
              setIsCollapsed(!isCollapsed);
            }}
          >
            <FontAwesomeIcon
              icon="chevron-up"
              color="white"
              className={'will-v-flip' + (isCollapsed ? ' v-flip' : '')}
              alt="toggle collapse"
            />
          </button>
        </div>
      </div>
    </div>
  );
}

export default InfographicDropdown;
