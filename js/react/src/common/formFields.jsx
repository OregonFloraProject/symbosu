import React from "react";

//function TextField(props) {
export default class TextField extends React.Component {
	constructor(props) {
		super(props);
		this.state = { 
			name: props.name,
			value: props.value,
			section: props.section,
			placeholder: props.placeholder
		};
    this.updateChange = this.props.onUpdate.bind(this);
	}

	handleBlur(e) {
		let obj = {'name': e.target.name, 'value': e.target.value, 'section': e.target.getAttribute("section")};
		this.updateChange(obj);
	}
	handleChange(e) {
		//console.log('null');
	}
	render() {
		return (
			<input 
				type="text"
				aria-label={this.state.name}
				name={this.state.name}
				section={this.state.section}
				defaultValue={this.state.value}
				//placeholder={this.state.placeholder}
				onBlur={ this.handleBlur.bind(this)}
				onChange={ this.handleChange}
			/>
		);
	}
}
TextField.defaultProps = {
  name: '',
  value: '',
  section: '',
};
