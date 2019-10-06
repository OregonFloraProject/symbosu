import React from "react";
import Carousel from "react-slick";

const CLIENT_ROOT = "..";

function getChecklistPage(clid) {
  const gardenPid = 3;
  return `${CLIENT_ROOT}/checklists/checklist.php?cl=${clid}&pid=${gardenPid}`;
}

function CannedSearchButton(props) {
  return (
    <div className="p-0 m-0">
      <button className="p-0 scroll-btn" onClick={ props.onClick }>
        <img
          style={{transform: `rotate(${props.rotate}deg)`, width: "3em", height: "3em" }}
          src={ `${CLIENT_ROOT}/images/garden/collapse-arrow.png` }
          alt="scroll"/>
      </button>
    </div>
  );
}

CannedSearchButton.defaultProps = {
  rotate: 0
};

function CannedSearchResult(props) {
  return (
    <div
        className={ "py-2 canned-search-result" }
        style={ Object.assign({ background: "#EFFFE3", color: "#3B631D", textAlign: "center", borderRadius: "2%" }, props.style) }>
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
  }

  render() {
    const slickSettings = {
      dots: false,
      infinite: true,
      slidesToShow: 4,
      slidesToScroll: 1
    };

    return (
      <div id="canned-searches" className="w-100 mt-1 p-3 mx-0 rounded-border" style={{ background: "#DFEFD3" }}>
        <h1 style={{color: "black", fontWeight: "bold", fontSize: "1.75em"}}>
          Or start with these plant combinations:
        </h1>

        <div className="mt-3 mx-0 px-0">
          <Carousel { ...slickSettings }>
            {
              this.props.searches.map((searchResult) => {
                return (
                  <div key={searchResult.clid}>
                    <CannedSearchResult
                      title={searchResult.name}
                      src={searchResult.iconurl}
                      href={getChecklistPage(searchResult.clid)}
                      onLearnMore={() => {
                        console.log(`Learn more about ${searchResult.name}!`)
                      }}
                      onFilter={() => {
                        console.log(`Filter for ${searchResult.name}!`)
                      }}
                    />
                  </div>
                );
              })
            }
          </Carousel>
        </div>
      </div>
    );
  }
}

export default CannedSearchContainer;