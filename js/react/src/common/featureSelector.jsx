import React from "react";
import { RangeSlider } from "@blueprintjs/core";//
import CheckboxItem from "../common/checkboxItem.jsx";

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core";
import { faExternalLinkAlt } from '@fortawesome/free-solid-svg-icons'
library.add( faExternalLinkAlt)

class CheckboxList extends React.Component {
  constructor(props) {
    super(props);
    //this.getDropdownId = this.getDropdownId.bind(this);
    this.onAttrClicked = this.props.onAttrClicked.bind(this);
  }

	render() {
	
	
		return (
	 		<ul
				className="list-unstyled"
				style={{ overflow: "hidden", whiteSpace: "nowrap" }}>
				{
					Object.keys(this.props.states).map((itemKey) => {
						let itemVal = this.props.states[itemKey];
						let attr = itemVal.cid + '-' + itemVal.cs;
						let checked = (this.props.attrs[attr] ? true: false );
						let href = '';
						if (itemVal.clid && itemVal.pid) {
							href= `${this.props.clientRoot}/checklists/checklist.php?cl=` + itemVal.clid + `&pid=` + itemVal.pid;
						}
						return (
							<li key={ attr }>
								
									<input 
										type="checkbox" 
										name={ attr } 
										value={ 'on' } 
										checked={ checked? 'checked' : '' }
										onChange={() => {
											this.onAttrClicked(attr,itemVal.charstatename,(checked? 'off':'on'))
										}}
									/>
									<label htmlFor={ attr }>{ itemVal.charstatename }
									{ href && 
										<a 
											href={ href }
											className="external-link"
											title="List of all native species this nursery carries"
										>
											<FontAwesomeIcon icon="external-link-alt" />
										</a>		
									}							
									</label>
					
							</li>
						)
					})
				}
			</ul>
	
		);
	}
}

class SelectDropdown extends React.Component {

  constructor(props) {
    super(props);
    //this.getDropdownId = this.getDropdownId.bind(this);
    this.onAttrClicked = this.props.onAttrClicked.bind(this);
  }
  
  selectChange = (e) => {
  	Object.keys(e.target.options).map((idx) => {
  		if (e.target.options[idx].value != '') {
				if (idx == e.target.selectedIndex) {
					this.onAttrClicked(e.target.options[idx].value,e.target.options[idx].text,'on');
				}else{
					this.onAttrClicked(e.target.options[idx].value,e.target.options[idx].text,'off');
				}
			}
  	});
  }
  
	render() {
		let selected = '';
		let cidPrefix = this.props.cid + '-';
		Object.entries(this.props.attrs).map((attr) => {//figure out if an attr is selected, since we have to set the value attribute on the <select> tag - React eyeroll
			if (attr[0].substring(0,cidPrefix.length) == cidPrefix) {
				selected = attr[0].toString();
			}
		});
		return (

			<select
				className="form-control"
				onChange={ this.selectChange }
				value={ selected } 
			>
				<option key="select" value="">Select</option>
				{
					Object.keys(this.props.states).map((itemKey) => {
						let itemVal = this.props.states[itemKey];
						let attr = itemVal.cid + '-' + itemVal.cs;
						return (
							<option 
								key={ attr }
								value={ attr } 
							>
							{ this.props.states[itemKey].charstatename}
							</option>
						)
					})

				}
			</select>
		);
	}
}
class GroupFilter extends React.Component {
  constructor(props) {
    super(props);
    //this.getDropdownId = this.getDropdownId.bind(this);
    //this.onAttrClicked = this.props.onAttrClicked.bind(this);
  }

	render() {
		//console.log(this.props.states);
		return (
	 		<div
				className="group-filter"
				style={{ overflow: "hidden", whiteSpace: "nowrap" }}>
				{
					Object.keys(this.props.states).map((itemKey) => {
						let itemVal = this.props.states[itemKey];
						let attr = itemVal.cid + '-' + itemVal.cs;
						let title = itemVal.charstatename;
						let checked = (this.props.attrs[attr] ? true: false );
						return (
							<div
									key={ attr }>
								<span 
									className="btn btn-primary alt-button region" 
									role="button" 
									name={ attr } 
									onClick={() => {
										this.props.onGroupFilterClicked(itemVal.children)
									}}
								>
								{ title }
								</span>
							</div>
						)
					})
				}
			</div>
	
		);
	}
}
/**
 * Slider from 0, 50+ with minimum and maximum value handles
 */
class PlantSlider extends React.Component {
  constructor(props) {
    super(props);
    this.state = { 
    	description: "(Any size)",
    	slider: null,
    	//sliderId: '',
    	cid: -1,
    	minMax: [1,10],
    	step: 1,
    	labelPrecision: 1,
    	labelStepSize: 1,
    	label: '',
    	units: '',
    	states: [],
    	range: [0,0]
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
		minMax[1] = minMax[1].toString().replace(/[>+]/g,'') - 0;
		let step = 1;
		if (this.props.states.length > 1) {
			let firstStep = this.props.states[1].numval - this.props.states[0].numval;
			step = (firstStep < 1 ? .1 : 1);
		}
		let labelPrecision = (step < 1? 1 : 0);
		let labelStepSize = (this.props.states.length > 10? this.props.states.length : step);//no labels where > 10

		let range = this.props.range;
		if (this.props.range[0] == PlantSlider.defaultProps.range[0] && this.props.range[0] == PlantSlider.defaultProps.range[0]) {
			range = minMax;
		}

    this.setState({ 
    	description: this.getSliderDescription(this.props,minMax) ,
    	minMax: minMax,
    	step: step,
    	labelPrecision: labelPrecision,
    	labelStepSize: labelStepSize,
    	label: this.props.label,
    	states: this.props.states,
    	units: this.props.units,
    	cid: this.props.cid,
    	range: range
    });
    //this.registerSliderEvent();
  }

  registerSliderRelease(range) {//fires the search
    if (range) {
			const onChangeEvent = this.props.onSliderChanged;  
			onChangeEvent(this.state, range);
    }
  }
  registerSliderChange(range) {//for display purposes only
    let desc = this.getSliderDescription(this.state,range) ;
    this.setState( { description: desc, range: range } );
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
			}else{
				return Number(value);
			}
		});		
		return cleanRange;
  }
  
  /**
	* @param valueArray {number[]} An array in the form [min, max]
	* @returns {string} An English description of the [min, max] values
	*/
	getSliderDescription(sliderState,range) {
		let valueArray = this.cleanRange(range);
		let valueDesc = '';
		if (Array.isArray(valueArray)) {
			let min = valueArray[0];
			let max = valueArray[1];
			if (sliderState.states.length > 0 && max == sliderState.states[sliderState.states.length - 1].numval) {//show max "10+" labels
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
	  let desc = this.getSliderDescription(this.state,this.cleanRange(range))
    return (
      <div>
        <RangeSlider
					min={ this.state.minMax[0] }
					max={ this.state.minMax[1] }
					stepSize={ this.state.step }
					value={ range }
					onRelease={this.registerSliderRelease}
					onChange={ this.registerSliderChange }
					labelPrecision={ this.state.labelPrecision }
					labelStepSize={ this.state.labelStepSize }
				/>     
        <label className="slider-description" htmlFor={ this.props.name }>
          { desc }
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
  states: [{charstatename:'',}],
  units: '',
  range: [0, 0],
  onSliderChanged: () => {},
};


class FeatureSelector extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    	showFeature: false
    };
    
    this.getDropdownId = this.getDropdownId.bind(this);
    this.toggleFeature = this.toggleFeature.bind(this);
    this.onAttrClicked = this.props.onAttrClicked.bind(this);
    this.onGroupFilterClicked = this.props.onGroupFilterClicked.bind(this);
    this.onSliderChanged = this.props.onSliderChanged.bind(this);
  }

  toggleFeature = () => {
  	this.setState({ showFeature: !this.state.showFeature });
  }
  getDropdownId() {
    return `feature-selector-${this.props.cid}`;
  }
  showFeature(featureType) {
  	switch(featureType) {
  		case 'slider':
  			return (
 			 		<PlantSlider
						states={ this.props.states }
						attrs={ this.props.attrs }
						sliders={ this.props.sliders }
						label={ this.props.title }
						cid={ this.props.cid }
						units={ this.props.units }
						onSliderChanged={ this.props.onSliderChanged }
					/>
  			)
  		case 'select':
  			return (
  				<SelectDropdown 
						states={ this.props.states }
						attrs={ this.props.attrs }
						cid={ this.props.cid }
						onAttrClicked={ this.onAttrClicked }
					/>
			 	)
  		case 'groupfilter':
  			return (
  				<GroupFilter 
						states={ this.props.states }
						attrs={ this.props.attrs }
						cid={ this.props.cid }
						onGroupFilterClicked={ this.onGroupFilterClicked }
					/>
			 	)
  	
  		default:
  			return (
  				<CheckboxList 
						states={ this.props.states }
						attrs={ this.props.attrs }
						onAttrClicked={ this.onAttrClicked }
						clientRoot={ this.props.clientRoot }
					/>
			 	)

  	}
  }

  render() {
		let classes =  "feature-input" + (this.state.showFeature == true ? '' :" short") + (this.props.display == 'slider' ? ' slider' :"");
		
		//if (this.props.display == 'slider') {
		//	classes = "slider ";//collapse
		//}

    return (
      <div className="second-level">
        <div className="feature-selectors">
          <a
          	className="feature-selector-header"
            onClick={this.toggleFeature}
          >
            <span>{ this.props.title.replace(/_/g, ' ') }</span>
            
            <img
              className={ "will-v-flip" }
              src={ `${this.props.clientRoot}/images/garden/expand-arrow.png` }
              alt="collapse"
            />
          </a>
          <div id={ this.getDropdownId() } className={ classes }>
						{this.showFeature(this.props.display)}
          </div>
        </div>
      </div>
    );
  }
}

FeatureSelector.defaultProps = {
  states: [],
  onAttrClicked: () => {},
  clientRoot: '',
};

export default FeatureSelector;