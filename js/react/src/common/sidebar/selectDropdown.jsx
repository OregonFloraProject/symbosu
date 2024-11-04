import React from 'react';

function SelectDropdown(props) {
  const cidPrefix = props.cid + '-';
  let selected = '';
  // figure out if an attr is selected
  Object.keys(props.attrs).forEach((attr) => {
    if (attr.substring(0, cidPrefix.length) === cidPrefix) {
      selected = attr.toString();
    }
  });

  const onChange = (e) => {
    Object.keys(e.target.options).map((idx) => {
      if (e.target.options[idx].value != '') {
        if (idx == e.target.selectedIndex) {
          props.onAttrClicked(e.target.options[idx].value, e.target.options[idx].text, 'on');
        } else {
          props.onAttrClicked(e.target.options[idx].value, e.target.options[idx].text, 'off');
        }
      }
    });
  };

  return (
    <select className="form-control" onChange={onChange} value={selected}>
      <option key="select" value="">
        Select
      </option>
      {Object.values(props.states).map((itemVal) => {
        const attr = `${itemVal.cid}-${itemVal.cs}`;
        return (
          <option key={attr} value={attr}>
            {itemVal.charstatename}
          </option>
        );
      })}
    </select>
  );
}

export default SelectDropdown;
