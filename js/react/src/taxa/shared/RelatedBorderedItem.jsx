import React from 'react';
import { Link } from 'react-scroll';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faArrowCircleUp, faArrowCircleDown } from '@fortawesome/free-solid-svg-icons';
import { RANK_FAMILY } from '../constants';
library.add(faArrowCircleUp, faArrowCircleDown);

function RelatedBorderedItem(props) {
  let value = '';
  value = (
    <div className="col-sm-12 related py-2 row">
      <div className="col-sm-8 related-sciname">{props.value[0]}</div>
      <div className="col-sm-4 related-nav pr-0">
        <span className="related-label">Related</span>
        <span className="related-links">
          {props.rankId > RANK_FAMILY &&
            (props.variant === 'rare' ? (
              <a className="related-link" href={props.value[1]} target="_blank" rel="noreferrer">
                <FontAwesomeIcon icon="arrow-circle-up" />
              </a>
            ) : (
              <a href={props.value[1]}>
                <FontAwesomeIcon icon="arrow-circle-up" />
              </a>
            ))}
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

export default RelatedBorderedItem;
