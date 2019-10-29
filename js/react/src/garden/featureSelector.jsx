import React from "react";

class FeatureSelector extends React.Component {
  render() {
    return (
      <div>
        <div className="text-capitalize">
          <h4 >{ this.props.title }</h4>
          <ul className="list-unstyled" style={{ overflow: "hidden", whiteSpace: "nowrap" }}>
            {
              this.props.values.map((v) => {
                return (
                  <li key={v}>
                    <input type="checkbox" name={v} onChange={ this.props.onChange }/>
                    <label className="ml-2">{v}</label>
                  </li>
                )
              })
            }
          </ul>
        </div>
      </div>
    );
  }
}

FeatureSelector.defaultProps = {
  onChanged: () => {}
};

export default FeatureSelector;