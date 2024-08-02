import React from 'react';

function SideBarSectionSpeciesList(props) {
  const { items } = props
  return (
    <div className={ "sidebar-section mb-4 " + (items.length > 0 ? "" : "d-none") }>
      <h3 className="text-light-green font-weight-bold mb-3">{ props.title }</h3>
      <div className="list">
        {
          items.map((val) => {
            return (
              <div className="associated-sciname" key={val}>{val}</div>
            );
          })
        }
      </div>
    </div>
  )
}

export default SideBarSectionSpeciesList;
