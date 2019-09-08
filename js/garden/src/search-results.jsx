function SearchResult(props) {
  let resStyle = Object.assign({ position: "relative", width: "100%", height: "100%" }, props.style);
  return (
    <div className="card" style={ resStyle }>
      <a href={ props.href }>
        <img
          className="card-img-top d-block"
          style={{ maxHeight: "50%", width: "100%", objectFit: "cover" }}
          alt={ props.title }
          src={ props.src }
        />
        <div className="card-body" style={{ maxHeight: "50%", overflow: "hidden" }}>
          <h5
            style={{
              textTransform: "capitalize",
              color: "black",
              whiteSpace: "nowrap",
              overflow: "hidden",
            }}>
            { props.title }
          </h5>
          <p className="card-text" style={{ textTransform: "capitalize" }}>{ props.text }</p>
        </div>
      </a>
    </div>
  );
}

class SearchResultGrid extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div
        className="col m-2 p-5 rounded-border w-100"
        style={{
          background: "#DFEFD3",
          display: "grid",
          gridTemplateRows: "repeat(4, 15em)",
          gridTemplateColumns: "repeat(5, 1fr)",
          gridGap: "0.5em"
        }}
      >
        { this.props.children }
      </div>
    );
  }
}

export { SearchResultGrid, SearchResult };