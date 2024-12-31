import React from 'react';

function GardenCredits(props) {
  return (
    <div className="p-3 natives-credits">
      <p>
        <a href="https://www.oregonmetro.gov/" target="_blank" rel="noreferrer">
          <img src={props.clientRoot + '/images/metro_logo_t.png'} className="metro" />
        </a>
        Support for the Grow Natives section of the site provided by{' '}
        <a href="https://www.oregonmetro.gov/" target="_blank" rel="noreferrer">
          Metro
        </a>
        &mdash; protecting clean air, water and habitat in greater Portland.
      </p>

      <p>
        <a
          href="https://www.nrcs.usda.gov/conservation-basics/conservation-by-state/oregon/"
          target="_blank"
          rel="noreferrer"
        >
          <img src={props.clientRoot + '/images/nrcs-color-lockup-4x.png'} />
        </a>
        Additional support provided by{' '}
        <a
          href="https://www.nrcs.usda.gov/conservation-basics/conservation-by-state/oregon"
          target="_blank"
          rel="noreferrer"
        >
          Oregon NRCS
        </a>
        .
      </p>

      <p>
        See contributing partners to OregonFlora <a href={props.clientRoot + '/pages/project-participants.php'}>here</a>
        .
      </p>
    </div>
  );
}

export default GardenCredits;
