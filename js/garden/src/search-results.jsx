function SearchResult(props) {
  let resStyle = Object.assign(
    { width: "100%", height: "100%", padding: "0.5em" },
    props.style
  );
  return (
    <div className="card" style={ resStyle }>
      <a href={ props.href }>
        <img
          className="card-img-top d-block"
          style={{ height: "60%", width: "100%", objectFit: "cover", borderRadius: "0.25em" }}
          alt={ props.title }
          src={ props.src }
        />
        <div className="card-body" style={{ height: "40%", overflow: "hidden" }}>
          <div className="card-text">{ props.commonName }</div>
          <div className="card-text">{ props.sciName }</div>
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