import React from "react";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faExternalLinkAlt } from '@fortawesome/free-solid-svg-icons';

library.add(faExternalLinkAlt);

function CheckboxList(props) {
  return (
    <ul className="list-unstyled">
      {
        Object.values(props.states).map((itemVal) => {
          const attr = `${itemVal.cid}-${itemVal.cs}`;
          const checked = !!props.attrs[attr];
          let href = '';
          if (itemVal.clid && itemVal.pid) {
            href = `${props.clientRoot}/checklists/checklist.php?cl=${itemVal.clid}&pid=${itemVal.pid}`;
          }
          return (
            <li key={attr}>
              <input
                type="checkbox"
                name={attr}
                value="on"
                checked={checked ? 'checked' : ''}
                onChange={() => {
                  props.onAttrClicked(attr, itemVal.charstatename, checked ? 'off' : 'on');
                }}
              />
              { href ? (
                  <label htmlFor={attr}>
                    <a
                      href={ href }
                      className="external-link"
                      title="List of all native species this nursery carries"
                    >
                      {itemVal.charstatename} <FontAwesomeIcon icon="external-link-alt" />
                    </a>
                  </label>
                ) : (
                  <label htmlFor={attr} dangerouslySetInnerHTML={{ __html: itemVal.charstatename }} />
                )
              }
            </li>
          );
        })
      }
    </ul>
  );
}

export default CheckboxList;
