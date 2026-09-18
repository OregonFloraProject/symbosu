import React from 'react';
import { addGlossaryTooltips } from '../../common/glossary';
import { SynonymItem } from '../components/SynonymItem.jsx';
import { KEY_NAMES, SUB_KEY_LIST_ORDERS } from '../constants';
import { showItem } from '../components/utils.js';
import RelatedBorderedItem from './RelatedBorderedItem.jsx';

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

function MainBorderedItem(props) {
  const defaultValue = 'not listed';
  let value = props.value;

  if (Array.isArray(value)) {
    value = (
      <ul className="border-item list-unstyled p-0 m-0">
        {props.value.map((v, index) => {
          if (typeof v === 'object' && v.type === 'conservation_status') {
            /**
             * BorderedItem used for rows that have labeled sub-items (e.g. conservation
             * status). Uses SUB_KEY_LIST_ORDERS to determine ordering of sub-items.
             */
            return SUB_KEY_LIST_ORDERS['status'].map((key) => (
              <li key={key}>
                <span
                  className="subheading-key"
                  dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(KEY_NAMES[key], props.glossary) }}
                />
                <span
                  dangerouslySetInnerHTML={{
                    __html: addGlossaryTooltips(v[key] || defaultValue, props.glossary),
                  }}
                />
              </li>
            ));
          }
          return <li key={index} dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(v, props.glossary) }} />;
        })}
      </ul>
    );
  } else {
    value = <span dangerouslySetInnerHTML={{ __html: addGlossaryTooltips(value, props.glossary) }} />;
  }

  return (
    <div className={'row dashed-border py-2'}>
      <div className="col font-weight-bold char-label">{props.keyName}</div>
      <div className="col char-value">{value}</div>
    </div>
  );
}

function MoreInfoItem(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="list-unstyled p-0 m-0">
        {props.value.map((v) => {
          if (v.url.indexOf('pdf') > 0) {
            return (
              <li key={v.url}>
                <a href={v.url}>
                  <button className="d-block my-2 btn-primary">
                    <img src={`${props.clientRoot}/images/pdf24.png`} />
                    {v.title}
                  </button>
                </a>
              </li>
            );
          } else {
            return (
              <li key={v.url}>
                <a href={v.url}>
                  <button className="d-block my-2 btn-primary">{v.title}</button>
                </a>
              </li>
            );
          }
        })}
      </ul>
    );
  }

  return (
    <div className={'more-info row dashed-border py-2'}>
      <div className="col font-weight-bold">{props.keyName}</div>
      <div className="col">{value}</div>
    </div>
  );
}

function SingleBorderedItem(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="p-0 m-0 single-border-item">
        {props.value.map((v) => {
          return (
            <li className="col dashed-border py-2" key={v['key']}>
              {v}
            </li>
          );
        })}
      </ul>
    );
  }

  return <div className={'row'}>{value}</div>;
}

function SidebarSection(props) {
  // Flag to put SynonymItem at the bottom of Context sidebar for taxaRare
  let synonymExist = false;
  let itemKeys = Object.keys(props.items);
  itemKeys = itemKeys.filter((k) => {
    const v = props.items[k];
    return showItem(v);
  });

  const isRareVariant = props.variant === 'rare';
  const wrapperClass = isRareVariant
    ? 'sidebar-section mb-4 ' + (itemKeys.length > 0 ? '' : 'd-none')
    : 'sidebar-section mb-5 ' + props.classes + ' ' + (itemKeys.length > 0 ? '' : 'd-none');

  return (
    <div className={wrapperClass}>
      <h3 className="text-light-green font-weight-bold mb-3">{props.title}</h3>
      {itemKeys.map((key) => {
        const val = props.items[key];
        if (key === 'Related') {
          return (
            <RelatedBorderedItem key={key} keyName={key} value={val} rankId={props.rankId} variant={props.variant} />
          );
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
        if (key === 'webLinks') {
          return <SingleBorderedItem key={val} keyName={val} value={val} />;
        }
        if (key === 'More info') {
          return <MoreInfoItem key={key} keyName={key} value={val} clientRoot={props.clientRoot} />;
        }
        if (key === 'synonyms' || key === 'Synonyms') {
          if (props.isTaxaRare) {
            synonymExist = true;
            return null;
          } else {
            return <SynonymItem key={val} keyName={val} value={val} glossary={props.glossary} />;
          }
        }
        if (isRareVariant) {
          return <BorderedItem key={key} keyName={key} value={val} glossary={props.glossary} />;
        }
        if (val) {
          return <MainBorderedItem key={key} keyName={key} value={val} glossary={props.glossary} />;
        }
        return null;
      })}
      {synonymExist && (
        <SynonymItem key={props.items['synonyms']} value={props.items['synonyms']} glossary={props.glossary} />
      )}
      <span className="row dashed-border" />
    </div>
  );
}

export default SidebarSection;
