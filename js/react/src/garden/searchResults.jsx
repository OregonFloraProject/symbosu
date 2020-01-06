import React from "react";

function SearchResult(props) {
  const useGrid = props.viewType === "grid";

  if (props.display) {
    return (
      <div className={ "card search-result " + (useGrid ? "grid-result" : "list-result") }>
        <a href={props.href}>
          <div className={useGrid ? "" : "card-body"}>
            <img
              className={useGrid ? "card-img-top grid-image" : "d-inline-block mr-1 list-image"}
              alt={props.title}
              src={props.src}
            />
            <div className={(useGrid ? "card-body" : "d-inline py-1") + " px-0"} style={{overflow: "hidden"}}>
              <div className={"card-text" + (useGrid ? "" : " d-inline")}>
                <span className="text-capitalize">{props.commonName}</span>
                {useGrid ? <br/> : " - "}
                <span className="font-italic">{props.sciName}</span>
              </div>
            </div>
          </div>
        </a>
      </div>
    );
  }

  return <span style={{ display: "none" }}/>;
}

class SearchResultContainer extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div
        id="search-results"
        className={ "mt-4 w-100" + (this.props.viewType === "grid" ? " search-result-grid" : "") }
      >
        { this.props.children }
      </div>
    );
  }
}

export { SearchResultContainer, SearchResult };