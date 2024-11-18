import React from 'react';
import { IconButton, CancelButton } from '../common/iconButton.jsx';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faTimesCircle } from '@fortawesome/free-solid-svg-icons';
library.add(faTimesCircle);

function arrayCompare(a1, a2) {
  if (a1.length !== a2.length) {
    return false;
  }
  for (let i in a1) {
    if (a1[i] !== a2[i]) {
      return false;
    }
  }
  return true;
}

function getPlantAttrText(filter) {
  const attrKeys = Object.keys(filter.val);
  let itemText = [];
  for (let i in attrKeys) {
    let attrKey = attrKeys[i];
    if (filter.val[attrKey].length > 0) {
      itemText.push(`${attrKey.replace(/_/g, ' ')}: ${filter.val[attrKey].join(', ')}`);
    }
  }

  return itemText.join('<br />');
}

class ViewOpts extends React.Component {
  buildButton(filterKey, itemText) {
    return (
      <CancelButton
        key={filterKey + ':' + itemText}
        title={itemText}
        isSelected={true}
        style={{ margin: '0.1em' }}
        onClick={() => {
          this.props.onFilterClicked(filterKey, itemText, 'off');
        }}
      />
    );
  }

  render() {
    const buttons = [];
    //console.log(this.props.filters);
    Object.keys(this.props.filters).map((filterKey) => {
      let filter = this.props.filters[filterKey];
      let showItem = true;
      let itemText = '';
      switch (filter.key) {
        case 'searchText':
          if (filter.val === ViewOpts.DEFAULT_SEARCH_TEXT) {
            showItem = false;
          } else {
            itemText = `Search: ${filter.val}`;
            buttons.push({ key: filter.key, text: itemText.toString() });
          }
          break;

        case 'attrs': {
          Object.entries(filter.val).map((feature) => {
            if (feature[1].toString().length) {
              buttons.push({ key: feature[0], text: feature[1].toString() });
            }
          });
          break;
        }
        case 'checklist': {
          let checklist = filter.val;
          if (checklist.clid > -1) {
            buttons.push({ key: checklist.clid, text: checklist.name });
          }
          break;
        }
        case 'ranges': {
          Object.entries(filter.val).forEach(([cid, featureObj]) => {
            buttons.push({ key: cid, text: featureObj.name });
          });
          break;
        }
        case 'sliders': {
          Object.entries(filter.val).map((feature, idx) => {
            let cid = feature[0];
            if (feature[1].label.toString().length) {
              let states = feature[1].originalStates;

              let min = 0;
              let max = 0;
              if (feature[1].step < 1) {
                min = Number(feature[1].range[0]).toFixed(1);
                max = Number(feature[1].range[1]).toFixed(1);
              } else {
                min = Number(feature[1].range[0]);
                max = Number(feature[1].range[1]);
              }

              if (states.length > 0 && max == states[states.length - 1].numval) {
                //show max "10+" labels
                max = states[states.length - 1].charstatename;
              }

              let txt = feature[1].label.toString() + ': ' + min + feature[1].units + '-' + max + feature[1].units;
              buttons.push({ key: cid, text: txt });
            }
          });
          break;
        }
        default:
          break;
      }
    });
    if (buttons.length > 0) {
      return (
        <div className="view-opts">
          <div className="button-wrapper">
            <div className="d-flex flex-row flex-wrap">
              <span className="filter-status">Filtered by:</span>
              {buttons.map((buttonItem) => {
                let button = this.buildButton(buttonItem.key, buttonItem.text);
                return button;
              })}
            </div>

            <CancelButton
              key={'reset'}
              title={'Clear all'}
              classes="reset"
              isSelected={true}
              onClick={() => {
                this.props.onReset();
              }}
            />
          </div>
        </div>
      );
    }
    return <p className="no-results">{this.props.defaultMessage}</p>;
  }
}

ViewOpts.defaultProps = {
  filters: [],
  onFilterClicked: () => {},
  onReset: () => {},
};

ViewOpts.DEFAULT_SEARCH_TEXT = '';
ViewOpts.DEFAULT_CLID = -1;

export default ViewOpts;
