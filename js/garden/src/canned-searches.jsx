function CannedSearchResult(props) {
  return (
    <div>
      <h5>{ props.title }</h5>
      <div className="card" style={{ padding: "0.5em" }} >
        <div className="card-body">
          <img
            className="d-block"
            style={{ width: "100%", height: "100%", objectFit: "cover" }}
            src={ props.src }
            alt={ props.src }
          />
        </div>
      </div>
    </div>
  );
}

function CannedSearchContainer(props) {
  return (
    <div className="row">
      <h1 style={{ color: "black", fontWeight: "bold", fontSize: "1.75em" }}>
        Kickstart your search with one of our native plant collections:
      </h1>
      <div
        className="w-100 rounded-border"
        // data-ride="carousel"
        style={{ display: "grid", gridTemplateColumns: "repeat(4, 1fr)", background: "#DFEFD3", minHeight: "10em"  }}
      >
        { props.children }
      </div>
    </div>
  );
}

export default CannedSearchContainer;