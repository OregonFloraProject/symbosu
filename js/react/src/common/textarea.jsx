import React from "react";
import TextField from "../common/formFields.jsx";


export default class TextareaField extends TextField {

	render() {
		return (
			<textarea
				aria-label={this.state.name}
				name={this.state.name}
				section={this.state.section}
				defaultValue={this.state.value}
				placeholder={this.state.placeholder}
				onBlur={ this.handleBlur.bind(this)}
				onChange={ this.handleChange}
			/>
		);
	}

}
