import React, { useState } from 'react';
import CheckboxList from './checkboxList.jsx';
import GroupFilter from './groupFilter.jsx';
import SelectDropdown from './selectDropdown.jsx';
import Slider from './slider.jsx';
import SliderOld from './sliderOld.jsx';

function FeatureSelector({
  states = [],
  onAttrClicked = () => {},
  onGroupFilterClicked = () => {},
  onSliderChanged = () => {},
  onRangeChanged = () => {},
  clientRoot = '',
  collapsible = true,
  ...props
}) {
  const [showFeature, setShowFeature] = useState(!collapsible);
  const toggleFeature = () => setShowFeature(!showFeature);
  const dropdownId = `feature-selector-${props.cid}`;

  const renderFeature = (featureType) => {
    switch (featureType) {
      case 'slider':
        if (props.useNewSlider) {
          return (
            <Slider
              states={states}
              ranges={props.ranges}
              label={props.title || props.heading}
              cid={props.cid}
              units={props.units}
              onRangeChanged={onRangeChanged}
            />
          );
        }
        return (
          <SliderOld
            states={states}
            attrs={props.attrs}
            sliders={props.sliders}
            label={props.title}
            cid={props.cid}
            units={props.units}
            onSliderChanged={onSliderChanged}
          />
        );
      case 'select':
        return <SelectDropdown states={states} attrs={props.attrs} cid={props.cid} onAttrClicked={onAttrClicked} />;
      case 'groupfilter':
        return (
          <GroupFilter
            states={states}
            attrs={props.attrs}
            cid={props.cid}
            onGroupFilterClicked={onGroupFilterClicked}
          />
        );
      default:
        return (
          <CheckboxList states={states} attrs={props.attrs} onAttrClicked={onAttrClicked} clientRoot={clientRoot} />
        );
    }
  };

  const className = 'feature-input' + (showFeature ? '' : ' short') + (props.display === 'slider' ? ' slider' : '');
  const titleElement = <span dangerouslySetInnerHTML={{ __html: props.title.replace(/_/g, ' ') }}></span>;

  return (
    <div className="second-level">
      <div className="feature-selectors">
        {collapsible ? (
          <a className="feature-selector-header" onClick={toggleFeature} aria-expanded={showFeature}>
            {titleElement}
            <img className="will-v-flip" src={`${clientRoot}/images/garden/expand-arrow.png`} alt="collapse" />
          </a>
        ) : (
          titleElement
        )}
        <div id={dropdownId} className={className}>
          {renderFeature(props.display)}
        </div>
      </div>
    </div>
  );
}

export default FeatureSelector;
