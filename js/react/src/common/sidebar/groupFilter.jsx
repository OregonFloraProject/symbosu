import React from 'react';

function GroupFilter(props) {
  return (
    <div className="group-filter" style={{ overflow: 'hidden', whiteSpace: 'nowrap' }}>
      {Object.values(props.states).map((itemVal) => {
        const attr = `${itemVal.cid}-${itemVal.cs}`;
        return (
          <div key={attr}>
            <span
              className="btn btn-primary alt-button region"
              role="button"
              name={attr}
              onClick={() => props.onGroupFilterClicked(itemVal.children)}
            >
              {itemVal.charstatename}
            </span>
          </div>
        );
      })}
    </div>
  );
}

export default GroupFilter;
