import React from "react";
import Carousel from "react-slick";

const CLIENT_ROOT = "..";

function getChecklistPage(clid) {
  const gardenPid = 3;
  return `${CLIENT_ROOT}/checklists/checklist.php?cl=${clid}&pid=${gardenPid}`;
}

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
      <div id="canned-searches" className="row mt-1 p-3 mx-0 rounded-border" style={{ background: "#DFEFD3" }}>
        <div className="col">
          <div className="row">
            <h1 className="col" style={{color: "black", fontWeight: "bold", fontSize: "1.75em"}}>
              Or start with these plant combinations:
            </h1>
          </div>

          <div className="row">
            <div className="col">
              <Carousel { ...slickSettings } style={{ maxWidth: "85%" }} className="mx-auto">
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
        </div>
      </div>
    );
  }
}

export default CannedSearchContainer;