import React from 'react';
import { IconButton } from '../../common/iconButton.jsx';

function SortOptions(props) {
  const SciNameSortButton = (
    <IconButton
      title="Scientific"
      onClick={() => props.onSortByChanged('sciName')}
      isSelected={props.sortBy === 'sciName'}
    />
  );
  const VernacularNameSortButton = (
    <IconButton
      title="Common"
      onClick={() => props.onSortByChanged('vernacularName')}
      isSelected={props.sortBy === 'vernacularName'}
    />
  );
  return (
    <div className="col-5 col pt-2 container settings px-2">
      <div className="col-lg-6 col-sm-12 row mx-0 mb-2 px-2 settings-section">
        <div className="col-5 text-right px-2 pt-1 toggle-labels">View as:</div>
        <div className="col-7 p-0">
          <IconButton
            title="Grid"
            icon={`${props.clientRoot}/images/garden/gridViewIcon.png`}
            onClick={() => props.onViewTypeChanged('grid')}
            isSelected={props.viewType === 'grid'}
          />
          <IconButton
            title="List"
            icon={`${props.clientRoot}/images/garden/listViewIcon.png`}
            onClick={() => props.onViewTypeChanged('list')}
            isSelected={props.viewType === 'list'}
          />
        </div>
      </div>

      <div className="col-lg-6 col-sm-12 row mx-0 mb-2 px-0 settings-section">
        <div className="col-5 text-right px-2 pt-1 toggle-labels">Sort by name:</div>
        <div className="col-7 p-0">
          {/* order the buttons so the default is first */}
          {props.defaultSortBy === 'vernacularName' ? VernacularNameSortButton : SciNameSortButton}
          {props.defaultSortBy === 'vernacularName' ? SciNameSortButton : VernacularNameSortButton}
        </div>
      </div>
    </div>
  );
}

export default SortOptions;
