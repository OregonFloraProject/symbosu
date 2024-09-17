import React from "react";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faExternalLinkAlt } from '@fortawesome/free-solid-svg-icons';
library.add(faExternalLinkAlt);

const PARTNERS = [
  { url: "https://npsoregon.org/", name: "Native Plant Society of Oregon" },
  { url: "https://inr.oregonstate.edu/orbic/rare-species/rare-species-oregon-publications", name: "Oregon Biodiversity Information Center" },
  { url: "https://www.blm.gov/oregon-washington", name: "OR/WA Bureau of Land Management" },
  { url: "https://www.fs.usda.gov/r6", name: "US Forest Service Region 6" },
  { url: "https://www.oregon.gov/oda/programs/PlantConservation/Pages/Default.aspx", name: "Oregon Dept. Agriculture Plant Conservation" },
];

function AboutDropdown(props) {
  return (
    <div id="about-guide" className="row mb-2" hidden={props.hidden}>
      <div className="col-md mb-4">
        <div className="about-guide-header">
          <h4>Scope</h4>
        </div>
          We present {props.numSpecies} species with a {props.lists.join(' or ')} NatureServe (Heritage) program ranking. Filtering options include plant features and conservation management actions.
      </div>
      <div className="col-md mb-4">
        <div className="about-guide-header">
          <h4>Data</h4>
        </div>
          The following partners contribute to the occurrence data and conservation strategies:
          {PARTNERS.map(({ url, name }) => (
            <p key={name}>
              <a href={url} target="_blank">
                {name}
                {' '}
                <FontAwesomeIcon icon="external-link-alt" />
              </a>
            </p>
          ))}
      </div>
      <div className="col-md mb-4">
        <div className="about-guide-header">
          <h4>Information Access</h4>
        </div>
          Detailed location data is limited to agency workers and researchers, in accordance with practices of the organizations responsible for protecting these species. Please see our <a href="#">policy</a> for details.
      </div>
    </div>
  )
}

export default AboutDropdown;
