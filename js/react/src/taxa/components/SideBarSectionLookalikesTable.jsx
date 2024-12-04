import React from 'react';
import { addGlossaryTooltips } from '../../common/glossary';
import { getTaxaPage } from '../../common/taxaUtils';

function LookalikesTableRow(props) {
  return (
    <div className={`row dashed-border${props.isHeader ? ' is-header' : ''}`}>
      <div className={`col px-0 char-label${props.isRow ? ' lookalike-sciname' : ''}`}>
        {props.url ? <a href={props.url}>{props.keyName}</a> : props.keyName}
      </div>
      {props.isRow ? (
        <div
          className="col px-0 char-value"
          dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(props.value, props.glossary) }}
        />
      ) : (
        <div className="col px-0 char-value">{props.value}</div>
      )}
    </div>
  );
}

function SideBarSectionLookalikesTable(props) {
  const { items, clientRoot } = props;
  return (
    <div className={'sidebar-section mb-4 ' + (items.length > 0 ? '' : 'd-none')}>
      <h3 className="text-light-green font-weight-bold mb-3">{props.title}</h3>
      <LookalikesTableRow isHeader keyName="Taxon" value="Differs from featured plant by" />
      {items.map(({ tidassociate, sciname, notes }) => (
        <LookalikesTableRow
          isRow
          key={sciname}
          url={tidassociate ? getTaxaPage(clientRoot, tidassociate) : null}
          keyName={sciname}
          value={notes}
          glossary={props.glossary}
        />
      ))}
      <span className="row dashed-border" />
    </div>
  );
}

export default SideBarSectionLookalikesTable;
