import React from 'react';
import { addGlossaryTooltips } from '../../common/glossary';

function LookalikesTableRow(props) {
  return (
    <div className={`row dashed-border${props.isHeader ? ' is-header' : ''}`}>
      <div className={`col px-0 char-label${props.isRow ? ' lookalike-sciname' : ''}`}>
        { props.keyName }
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
  const { items } = props;
  return (
      <div className={ "sidebar-section mb-4 " + (items.length > 0 ? "" : "d-none") }>
        <h3 className="text-light-green font-weight-bold mb-3">{ props.title }</h3>
        <LookalikesTableRow isHeader keyName="Taxon" value="Differs from featured plant by" />
        {items.map(({ taxon, description }) => (
          <LookalikesTableRow
            isRow
            key={taxon}
            keyName={taxon}
            value={description}
            glossary={props.glossary}
          />
        ))}
        <span className="row dashed-border"/>
    </div>
  );
}

export default SideBarSectionLookalikesTable;
