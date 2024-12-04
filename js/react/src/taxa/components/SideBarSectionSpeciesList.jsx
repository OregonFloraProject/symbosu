import React from 'react';
import { getTaxaPage } from '../../common/taxaUtils';

function SideBarSectionSpeciesList(props) {
  const { items, clientRoot } = props;
  return (
    <div className={'sidebar-section mb-4 ' + (items.length > 0 ? '' : 'd-none')}>
      <h3 className="text-light-green font-weight-bold mb-3">{props.title}</h3>
      <div className="list">
        {items.map(({ tidassociate, sciname }) => {
          return (
            <div className="associated-sciname" key={sciname}>
              {tidassociate ? <a href={getTaxaPage(clientRoot, tidassociate)}>{sciname}</a> : sciname}
            </div>
          );
        })}
      </div>
    </div>
  );
}

export default SideBarSectionSpeciesList;
