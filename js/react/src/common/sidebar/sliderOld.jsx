import React from 'react';
import { RangeSlider } from '@blueprintjs/core';

// TODO(eric): transition other sidebars over to new slider and get rid of this

/**
 * Slider from 0, 50+ with minimum and maximum value handles
 */
class PlantSlider extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      description: '(Any size)',
      slider: null,
      //sliderId: '',
      cid: -1,
      minMax: [1, 10],
      step: 1,
      labelPrecision: 1,
      labelStepSize: 1,
      label: '',
      units: '',
      states: [],
      range: [0, 0],
    };
    this.registerSliderRelease = this.registerSliderRelease.bind(this);
    this.registerSliderChange = this.registerSliderChange.bind(this);
    this.getSliderDescription = this.getSliderDescription.bind(this);
    this.cleanRange = this.cleanRange.bind(this);
  }

  componentDidMount() {
    let minMax = [];
    minMax[0] = this.props.states[0].numval;
    minMax[1] = this.props.states[this.props.states.length - 1].numval;
    minMax[1] = minMax[1].toString().replace(/[>+]/g, '') - 0;
    let step = 1;
    if (this.props.states.length > 1) {
      let firstStep = this.props.states[1].numval - this.props.states[0].numval;
      step = firstStep < 1 ? 0.1 : 1;
    }
    let labelPrecision = step < 1 ? 1 : 0;
    let labelStepSize = this.props.states.length > 10 ? this.props.states.length : step; //no labels where > 10

    let range = this.props.range;
    if (
      this.props.range[0] == PlantSlider.defaultProps.range[0] &&
      this.props.range[0] == PlantSlider.defaultProps.range[0]
    ) {
      range = minMax;
    }

    this.setState({
      description: this.getSliderDescription(this.props, minMax),
      minMax: minMax,
      step: step,
      labelPrecision: labelPrecision,
      labelStepSize: labelStepSize,
      label: this.props.label,
      states: this.props.states,
      units: this.props.units,
      cid: this.props.cid,
      range: range,
    });
    //this.registerSliderEvent();
  }

  registerSliderRelease(range) {
    //fires the search
    if (range) {
      const onChangeEvent = this.props.onSliderChanged;
      onChangeEvent(this.state, range);
    }
  }
  registerSliderChange(range) {
    //for display purposes only
    let desc = this.getSliderDescription(this.state, range);
    this.setState({ description: desc, range: range });
  }
  cleanRange(range) {
    /* 	floats from slider sometimes have rounding errors (e.g. 5.70000000001)
				so we correct the ones for our use and store in cleanRange, while leaving this.state.range alone for the slider to use
				(the slider fixes those errors for its internal use)
		 */
    let cleanRange = range;
    cleanRange = range.map((value) => {
      if (this.state.step < 1) {
        return Number(value).toFixed(1);
      } else {
        return Number(value);
      }
    });
    return cleanRange;
  }

  /**
   * @param valueArray {number[]} An array in the form [min, max]
   * @returns {string} An English description of the [min, max] values
   */
  getSliderDescription(sliderState, range) {
    let valueArray = this.cleanRange(range);
    let valueDesc = '';
    if (Array.isArray(valueArray)) {
      let min = valueArray[0];
      let max = valueArray[1];
      if (sliderState.states.length > 0 && max == sliderState.states[sliderState.states.length - 1].numval) {
        //show max "10+" labels
        max = sliderState.states[sliderState.states.length - 1].charstatename;
      }

      /*	if ( valueArray[0] > valueArray[1] ) {// Fix if the handles have switched
				let tmp = valueArray[0];
				valueArray[0] = valueArray[1];
				valueArray[1] = tmp;
			}*/
      valueDesc = `${min} ` + sliderState.units + ` - ${max} ` + sliderState.units + ``;
    }
    /*if (valueArray[0] === this.minMax[0] && valueArray[1] === this.minMax[1]) {
			valueDesc = "(Any size)";
		} else if (valueArray[0] === this.minMax[0]) {
			valueDesc = `(At most ${valueArray[1]} ` + units + `)`;
		} else if (valueArray[1] === this.minMax[1]) {
			valueDesc = `(At least ${valueArray[0]} ` + units + `)`;
		} else {
		valueDesc = `(${valueArray[0]} ` + units + ` - ${valueArray[1]} ` + units + `)`
		}*/

    return valueDesc;
  }

  render() {
    /* handles resets coming from ViewOpts */
    let range = this.state.range;
    if (!this.props.sliders[this.state.cid]) {
      range = this.state.minMax;
    }
    let desc = this.getSliderDescription(this.state, this.cleanRange(range));
    return (
      <div>
        <RangeSlider
          min={this.state.minMax[0]}
          max={this.state.minMax[1]}
          stepSize={this.state.step}
          value={range}
          onRelease={this.registerSliderRelease}
          onChange={this.registerSliderChange}
          labelPrecision={this.state.labelPrecision}
          labelStepSize={this.state.labelStepSize}
        />
        <label className="slider-description" htmlFor={this.props.name}>
          {desc}
        </label>
      </div>
    );
  }
}

PlantSlider.defaultProps = {
  attrs: {},
  cid: -1,
  label: '',
  onChange: () => {},
  onRelease: () => {},
  states: [{ charstatename: '' }],
  units: '',
  range: [0, 0],
  onSliderChanged: () => {},
};

export default PlantSlider;
