import React from "react";
import CheckboxItem from "../common/checkboxItem.jsx";

class FeatureSelector extends React.Component {
  constructor(props) {
    super(props);
    this.getDropdownId = this.getDropdownId.bind(this);
  }

  getDropdownId() {
    return `feature-selector-${this.props.title.replace(/[^A-Za-z0-9]/g, '_')}`;
  }

  render() {
    return (
      <div>
        <div className="text-capitalize">
          <a
            data-toggle="collapse"
            aria-expanded="false"
            aria-controls={ this.getDropdownId() }
            href={ `#${this.getDropdownId()}` }
          >
            <p style={{ fontSize: "1.1em" }}>{ this.props.title }</p>
          </a>
          <div id={ this.getDropdownId() } className="collapse">
            <div className="card card-body">
              <ul
                className="list-unstyled"
                style={{ overflow: "hidden", whiteSpace: "nowrap" }}>
                {
                  Object.keys(this.props.items).map((itemKey) => {
                    let itemVal = this.props.items[itemKey];
                    return (
                      <li key={ itemKey }>
                        <CheckboxItem
                          name={ itemVal }
                          value={ itemVal ? "on" : "off" }
                          onChange={ () => this.props.onChange(itemKey) }
                        />
                      </li>
                    )
                  })
                }
              </ul>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

FeatureSelector.defaultProps = {
  items: [],
  onChanged: () => {}
};

export default FeatureSelector;