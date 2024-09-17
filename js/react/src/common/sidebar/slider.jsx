import React, { useEffect, useState } from "react";
import { RangeSlider } from "@blueprintjs/core";

function getSliderDescription(charstates, unit, state) {
  if (!Array.isArray(state)) {
    return '';
  }
  const min = state[0];
  let max = state[1];
  // if final charstatename has + or >, use the full charstatename instead of just the numval
  if (charstates.length > 0 && max === charstates[charstates.length - 1].numval) {
    max = charstates[charstates.length - 1].charstatename;
  }
  return `${min} ` + unit + ` - ${max} ` + unit;
}

/**
 * floats from slider sometimes have rounding errors (e.g. 5.70000000001)
 * so we correct the ones for our use, while leaving displayRange alone for the slider to use
 * (the slider fixes those errors for its internal use)
 */
function cleanRange(range, stepSize) {
  let cleanRange = range;
  cleanRange = range.map((value) => {
    if (stepSize < 1) {
      return Number(value).toFixed(1);
    } else {
      return Number(value);
    }
  });
  return cleanRange;
}

function getCsValuesForRange(range, states) {
  let min = states[0].cs;
  let max = (states.length > 1? states[1].cs : states[0].cs);
  Object.keys(states).map((key) => {
    let stateNum = Number(states[key].numval);
    let stateCs = Number(states[key].cs);
    if (stateNum <= range[0]) {
      min = stateCs;
    }
    if (stateNum < range[1]) {
      max = stateCs + 1;
    }
  });
  if (max > states[states.length - 1].cs) {
    max = states[states.length - 1].cs;
  }
  return { min, max };
}

function Slider({
  states = [{charstatename:'',}],
  ranges = {},
  label = '',
  cid = -1,
  units = '',
  onRangeChanged = () => {},
}) {
  const overallMin = states[0].numval;
  const overallMax = parseInt(states[states.length - 1].numval.toString().replace(/[>+]/g,'') - 0);

  const [displayRange, setDisplayRange] = useState([overallMin, overallMax]);

  // if the slider is externally reset (e.g. from ViewOpts), pass this through
  useEffect(() => {
    let newMin, newMax;
    if (ranges[cid]) {
      newMin = ranges[cid].values[0];
      newMax = ranges[cid].values[1];
    }
    setDisplayRange([newMin || overallMin, newMax || overallMax]);
  }, [ranges]);

  let stepSize = 1;
  let labelPrecision = 0;
  if (states.length > 1) {
    const diff = states[1].numval - states[0].numval;
    if (diff < 1) {
      stepSize = 0.1;
      labelPrecision = 1;
    } else if (diff > 1) {
      stepSize = diff;
      labelPrecision = 0;
    }
  }
  const labelStepSize = states.length > 10 ? states.length : stepSize; // no labels with > 10 charstates
  const sliderKey = `${cid}-slider`;

  return (
    <div name={sliderKey}>
      <RangeSlider
        min={overallMin}
        max={overallMax}
        stepSize={stepSize}
        value={displayRange}
        onChange={range => {
          setDisplayRange(range);
        }}
        onRelease={range => {
          // only actually fire search onRelease
          const cleanedRange = cleanRange(range, stepSize);
          let featureObj;
          if (cleanedRange[0] !== overallMin || cleanedRange[1] !== overallMax) {
            const { min, max } = getCsValuesForRange(cleanedRange, states);

            let maxTextValue = cleanedRange[1];
            if (maxTextValue === overallMax) {
              // show + or > if it's in the charstatename
              maxTextValue = states[states.length - 1].charstatename;
            }

            featureObj = {
              keys: [`${cid}-n-${min}`, `${cid}-x-${max}`],
              values: cleanedRange,
              name: `${label}: ${cleanedRange[0]} - ${maxTextValue} ${units}`,
            };
          }
          onRangeChanged(cid, featureObj);
        }}
        labelPrecision={labelPrecision}
        labelStepSize={labelStepSize}
      />
      <label className="slider-description" htmlFor={sliderKey}>
        {getSliderDescription(states, units, displayRange)}
      </label>
    </div>
  )
}

export default Slider;
