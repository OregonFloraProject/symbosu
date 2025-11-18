import React from 'react';
import { showItem } from './utils.js';
import { Link } from 'react-scroll';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { SynonymItem } from './SynonymItem.jsx';
import { RANK_FAMILY } from '../constants/index.js';

function BorderedItem(props) {
  let value = props.value;
  const isArray = Array.isArray(value);

  if (isArray) {
    value = (
      <ul className="border-item list-unstyled p-0 m-0">
        {props.value.map((v) => (
          <li key={v}>{v}</li>
        ))}
      </ul>
    );
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

function RelatedBorderedItem(props) {
  let value = '';
  value = (
    <div className="col-sm-12 related py-2 row">
      <div className="col-sm-8 related-sciname">{props.value[0]}</div>
      <div className="col-sm-4 related-nav pr-0">
        <span className="related-label">Related</span>
        <span className="related-links">
          {props.rankId > RANK_FAMILY && (
            <a href={props.value[1]}>
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
    <div className={'sidebar-section mb-5 ' + props.classes + ' ' + (itemKeys.length > 0 ? '' : 'd-none')}>
      <h3 className="text-light-green font-weight-bold mb-3">{props.title}</h3>
      {itemKeys.map((key) => {
        const val = props.items[key];
        if (key == 'webLinks') {
          return <SingleBorderedItem key={val} keyName={val} value={val} />;
        } else if (key == 'Related') {
          return <RelatedBorderedItem key={key} keyName={key} value={val} rankId={props.rankId} />;
        } else if (key == 'More info') {
          return <MoreInfoItem key={key} keyName={key} value={val} clientRoot={props.clientRoot} />;
        } else if (key == 'Synonyms') {
          return <SynonymItem key={val} value={val} glossary={props.glossary} />;
        } else if (val) {
          return <BorderedItem key={key} keyName={key} value={val} />;
        }
      })}
      <span className="row dashed-border" />
    </div>
  );
}

export default SideBarSection;