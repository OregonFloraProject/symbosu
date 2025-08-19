import React from 'react';
import { Link } from 'react-scroll';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faArrowCircleUp, faArrowCircleDown } from '@fortawesome/free-solid-svg-icons';
import { addGlossaryTooltips } from '../../common/glossary';
import { KEY_NAMES, RANK_FAMILY, SUB_KEY_LIST_ORDERS } from '../constants';
import { showItem } from './utils';
library.add(faArrowCircleUp, faArrowCircleDown);

function BorderedItem(props) {
  let value = props.value;

  if (Array.isArray(value)) {
    value = (
      <ul className="list-unstyled p-0 m-0">
        {props.value.map((v) => (
          <li key={v} dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(v, props.glossary) }} />
        ))}
      </ul>
    );
  } else {
    value = <span dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(value, props.glossary) }} />;
  }

  const keyName = KEY_NAMES[props.keyName] || props.keyName;

  return (
    <div className={'row dashed-border'}>
      <div
        className="col px-0 font-weight-bold char-label"
        dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(keyName, props.glossary) }}
      />
      <div className="col px-0 char-value">{value}</div>
    </div>
  );
}

/**
 * BorderedItem used for rows that have labeled sub-items (e.g. conservation status). Uses
 * SUB_KEY_LIST_ORDERS to determine ordering of sub-items.
 *
 * If no value is provided for a key in the SUB_KEY_LIST_ORDERS ordering, props.defaultValue will be
 * used.
 */
function OrderedObjectBorderedItem(props) {
  const keyName = KEY_NAMES[props.keyName] || props.keyName;

  return (
    <div className={'row dashed-border'}>
      <div
        className="col px-0 font-weight-bold char-label"
        dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(keyName, props.glossary) }}
      />
      <div className="col px-0 char-value">
        <ul className="list-unstyled p-0 m-0">
          {SUB_KEY_LIST_ORDERS[props.keyName].map((k) => (
            <li key={k}>
              <span
                className="subheading-key"
                dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(KEY_NAMES[k] || k, props.glossary) }}
              />
              <span
                dangerouslySetInnerHTML={{
                  __html: addGlossaryTooltips(props.value[k] || props.defaultValue, props.glossary),
                }}
              />
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}

function RelatedBorderedItem(props) {
  let value = '';
  value = (
    <div className="col-sm-12 related py-2 row">
      <div className="col-sm-8 related-sciname">{props.value[0]}</div>
      <div className="col-sm-4 related-nav pr-0">
        <span className="related-label">Related</span>
        <span className="related-links">
          {props.rankId > RANK_FAMILY && (
            <a className="related-link" href={props.value[1]} target="_blank" rel="noreferrer">
              <FontAwesomeIcon icon="arrow-circle-up" />
            </a>
          )}
          {props.rankId > RANK_FAMILY && props.value[2].length > 0 && (
            /* two statements here because I don't want to wrap them in one div */
            <span className="separator">/</span>
          )}
          {props.value[2].length > 0 && (
            <Link to="spp-wrapper" spy={true} smooth={true} duration={400} offset={-180}>
              <FontAwesomeIcon icon="arrow-circle-down" />
            </Link>
          )}
        </span>
      </div>
    </div>
  );
  return <div className={'row'}>{value}</div>;
}

function SideBarSection(props) {
  let itemKeys = Object.keys(props.items);
  itemKeys = itemKeys.filter((k) => {
    const v = props.items[k];
    return showItem(v);
  });

  return (
    <div className={'sidebar-section mb-4 ' + (itemKeys.length > 0 ? '' : 'd-none')}>
      <h3 className="text-light-green font-weight-bold mb-3">{props.title}</h3>
      {itemKeys.map((key) => {
        const val = props.items[key];
        if (key === 'Related') {
          return <RelatedBorderedItem key={key} keyName={key} value={val} rankId={props.rankId} />;
        }
        if (key === 'status') {
          return (
            <OrderedObjectBorderedItem
              key={key}
              keyName={key}
              value={val}
              defaultValue="not listed"
              glossary={props.glossary}
            />
          );
        }
        return <BorderedItem key={key} keyName={key} value={val} glossary={props.glossary} />;
      })}
      <span className="row dashed-border" />
    </div>
  );
}

export default SideBarSection;
