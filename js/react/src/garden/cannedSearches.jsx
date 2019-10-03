const CLIENT_ROOT = "..";

function CannedSearchResult(props) {
  return (
    <div className="mx-1 p-2 col" style={ Object.assign({ background: "#EFFFE3", color: "#3B631D", textAlign: "center", borderRadius: "2%" }, props.style) }>
      <h4 className="canned-title">{ props.title }</h4>
      <div className="card" style={{ padding: "0.5em" }} >
        <a href={ props.href }>
          <div className="card-body" style={{ padding: "0" }}>
            <img
              className="d-block"
              style={{ width: "100%", height: "7em", borderRadius: "0.25em", objectFit: "cover" }}
              src={ props.src }
              alt={ props.src }
            />
          </div>
        </a>
      </div>
      <div className="mt-2 px-2">
        <button className="w-100 px-3 my-1 btn-filter" onClick={ props.onFilter }>
          Filter for these
        </button>
        <button className="w-100 px-3 my-1 btn-learn" onClick={ props.onLearnMore }>
          Learn more
        </button>
      </div>
    </div>
  );
}

class CannedSearchContainer extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      offset: this.props.children.length,
    };

    this.scrollLeft = this.scrollLeft.bind(this);
    this.scrollRight = this.scrollRight.bind(this);
  }

  scrollLeft() {
    let newOffset = this.state.offset === 0 ? this.props.children.length - 1 : this.state.offset - 1;
    this.setState({ offset: newOffset });
  }

  scrollRight() {
    this.setState({ offset: (this.state.offset + 1) % this.props.children.length });
  }

  render() {
    return (
      <div id="canned-searches" className="w-100 mt-1 p-3 rounded-border" style={{ background: "#DFEFD3" }}>
          <h1 style={{color: "black", fontWeight: "bold", fontSize: "1.75em"}}>
            Or start with these plant combinations:
          </h1>

        <div className="w-100 row mt-3">
          <div className="d-flex align-items-center p-0 m-0 col-auto">
              <button onClick={ this.scrollLeft }>
                <img
                  className="mx-auto"
                  style={{transform: "rotate(-90deg)", width: "2em", height: "2em" }}
                  src={ `${CLIENT_ROOT}/images/garden/collapse-arrow.png` }
                  alt="scroll left"/>
              </button>
          </div>

          <div className="px-2 m-1 col">
            <div
              className="row"
            >
              {
                [0, 1, 2, 3].map((i) => {
                  return this.props.children[(i + this.state.offset) % this.props.children.length]
                })
              }
            </div>
          </div>

          <div className="d-flex align-items-center p-0 m-0 col-auto">
            <button onClick={ this.scrollRight }>
              <img
                className="mx-auto"
                style={{ transform: "rotate(90deg)", width: "2em", height: "2em" }}
                src={ `${CLIENT_ROOT}/images/garden/collapse-arrow.png` }
                alt="scroll right"/>
            </button>
          </div>
        </div>
      </div>
    );
  }
}

export { CannedSearchContainer, CannedSearchResult };