import React from "react";
import { showItem } from "./utils";

function BorderedItemVendor(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="list-unstyled p-0 m-0">
        { props.value.map((v) => {
            return (
              <li key={ v.clid }><a href={ props.clientRoot + '/checklists/checklist.php?cl=' + v.clid + '&pid=4' }>{ v.name }</a></li>
            )
          })
        }

      </ul>
    );
  }
  return (
    <div className={ "row dashed-border" }>
      <div className="col px-0 font-weight-bold char-label">{ props.keyName }</div>
      <div className="col px-0 char-value">{ value }</div>
    </div>
  );
}

function SideBarSectionVendor(props) {
  let itemKeys = Object.keys(props.items);
  itemKeys = itemKeys.filter((k) => {
    const v = props.items[k];
    return showItem(v);
  });

  return (
      <div className={ "sidebar-section mb-4 " + (itemKeys.length > 0 ? "" : "d-none") }>
        <h3 className="text-light-green font-weight-bold mb-3">{ props.title }</h3>
        {
          itemKeys.map((key) => {
            const val = props.items[key];
            return <BorderedItemVendor key={ key } keyName={ key } value={ val } clientRoot={ props.clientRoot } />
          })
        }
        <span className="row dashed-border"/>
    </div>
  );
}

export default SideBarSectionVendor;
