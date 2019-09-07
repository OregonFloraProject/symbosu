"use strict";

const helpButtonStyle = {
  float: "right",
  padding: 0,
  marginLeft: "auto",
  borderRadius: "50%",
  background: "#5FB021",
};

const searchButtonStyle = {
  width: "2em",
  height: "2em",
  padding: "0.3em",
  marginLeft: "0.5em",
  borderRadius: "50%",
  background: "rgba(255, 255, 255, 0.5)"
};

/**
 * @param {Element} slider Bootstrap slider
 * @returns {number[]} Current [min, max] for the given slider
 */
function getSliderValues(slider) {
  return slider.val().trim("[]").split(",").map((str) => parseInt(str));
}

/**
 * Sidebar header with title, subtitle, and help
 */
function SideBarHeading(props) {
  return (
    <div style={{color: "black"}}>
      <div className="mb-1" style={{color: "inherit"}}>
        <h3 className="font-weight-bold d-inline">Search for plants</h3>
        <button style={ helpButtonStyle }>
          <img
            style={{ width: "1.25em" }}
            alt="help"
            src="/images/garden/help.png"/>
        </button>
      </div>
      <p>
        Start applying characteristics, and the matching plants will appear at
        right.
      </p>
    </div>
  );
}

/**
 * Sidebar 'plant search' button
 */
function SearchButton(props) {
  return (
    <button className="my-auto" style={ searchButtonStyle } onClick={ props.onClick }>
      <img
        style={{ display: props.showLoading ? "none" : "block" }}
        src="/images/garden/search-green.png"
        alt="search plants"/>
      <div
        className="mx-auto text-success spinner-border spinner-border-sm"
        style={{ display: props.showLoading ? "block" : "none" }}
        role="status"
        aria-hidden="true"/>
    </button>
  );
}

/**
 * Sidebar 'plant search' text field & button
 */
function SideBarSearch(props) {
  return (
    <div className="input-group w-100 mb-4 p-2">
      <input
        name="search"
        type="text"
        placeholder="Search plants by name"
        className="form-control"
        onChange={ props.onChange } />
      <SearchButton onClick={ props.onClick } showLoading={ props.isLoading } />
    </div>
  );
}

/**
 * 'Plant Need' dropdown with label
 */
function PlantNeed(props) {
  return (
    <div className = "input-group pt-3 mt-3" style={{ borderTop: "1px dashed black" }}>
      <label className="font-weight-bold" htmlFor={ props.label.toLowerCase() }>
        { props.label }
      </label>
      <select
        id="sunlight"
        name={ props.label.toLowerCase() }
        className="form-control ml-auto"
        style={{ maxWidth: "50%" }}
        defaultValue=""
        onChange={ props.onChange }>
        <option value="" disabled hidden>Select...</option>
        {
          props.choices.map((opt) =>
            <option key={ opt.toLowerCase() } value={ opt.toLowerCase() }>
              { opt }
            </option>
          )
        }
      </select>
    </div>
  );
}

function PlantSlider(props) {
  return (
    <div>
      <label className="d-block text-center" htmlFor={ props.label.toLowerCase() }>{ props.label }</label>
      <input
        type="text"
        className="bootstrap-slider"
        name={ props.label.toLowerCase() }
        data-provide="slider"
        data-slider-value="[0, 50]"
        data-slider-ticks="[0, 10, 20, 30, 40, 50]"
        data-slider-ticks-labels='["0", "", "", "", "", "50+"]'
        data-slider-ticks-snap-bounds="1"
        value=""
        onInput={ props.onChange }
        onChange={ props.onChange }
      />
      <br/>
      <label className="d-block text-center" htmlFor={ props.label.toLowerCase() }>
        { props.description }
      </label>
    </div>
  );
}

/**
 * Full sidebar
 */
class SideBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      search: '',
      sunlight: '',
      moisture: '',
      width: [0, 50],
      height: [0, 50],
      isLoading: false
    }
  }

  onSearchTextChanged() {
    this.setState({ searchText: event.target.value });
  }

  onSearch() {
    this.setState({ isLoading: true });

    // TODO: Search!
    console.log("The current search value is '" + this.state.search + "'");
    setTimeout(() => { this.setState({ isLoading: false }) }, 3000);
  }

  onSunlightChanged() {
    console.log("The current sunlight value is '" + event.target.value + "'");
    this.setState({ sunlight: event.target.value });
  }

  onMoistureChanged() {
    console.log("The current moisture value is '" + event.target.value + "'");
    this.setState({ moisture: event.target.value });
  }

  onHeightChanged() {
    console.log("The current height value is '" + event.target.value + "'");
    this.setState({ height: getSliderValues(event.target.value) });
  }

  onWidthChanged() {
    console.log("The current width value is '" + event.target.value + "'");
    this.setState({ width: getSliderValues(event.target.value) });
  }

  render() {
    return (
      <div
        id="sidebar"
        className="col-sm-3 m-2 p-5 rounded-border"
        style={ this.props.style }>
        {/* Title & Subtitle */}
        <SideBarHeading />

        {/* Search */}
        <SideBarSearch
          onChange={ this.onSearchTextChanged.bind(this) }
          onClick={ this.onSearch.bind(this) }
          isLoading={ this.state.isLoading }
        />

        {/* Sunlight & Moisture */}
        <div style={{ background: "white" }} className="rounded-border p-4">
          <h4>Plant needs</h4>
          <PlantNeed
            label="Sunlight"
            choices={ ["Sun", "Part-Shade", "Full-Shade"] }
            onChange={ this.onSunlightChanged.bind(this) } />
          <PlantNeed
            label="Moisture"
            choices={ ["Dry", "Moderate", "Wet"] }
            onChange={ this.onMoistureChanged.bind(this) } />
        </div>

        {/* Sliders */}
        <div className="my-5">
          <h4 className="mr-2 mb-2 d-inline">Mature Size</h4>
          <span>(Just grab the slider dots)</span><br />
          <div className="mt-2 row d-flex justify-content-center">
            <div className="col-sm-5 mr-2">
              <PlantSlider
                label="Height (ft)"
                description="(Any size)"
                onChange={ this.onHeightChanged.bind(this) } />
            </div>
            <div
              style={{ width: "1px", borderRight: "1px dashed grey", marginLeft: "-0.5px" }}
            />
            <div className="col-sm-5 ml-2">
              <PlantSlider
                label="Width (ft)"
                description="(Any size)"
                onChange={ this.onWidthChanged.bind(this) } />
            </div>
          </div>
        </div>

      </div>
    );
  }
}

export default SideBar;
