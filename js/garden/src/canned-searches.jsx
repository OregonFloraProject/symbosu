function CannedSearchResult(props) {
  return (
    <div className="mx-1" style={ props.style }>
      <h5 className="canned-title">{ props.title }</h5>
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
    </div>
  );
}

function CannedSearchContainer(props) {
  return (
    <div className="w-100 mt-1">
      <h1 style={{ color: "black", fontWeight: "bold", fontSize: "1.75em" }}>
        Kickstart your search with one of our native plant collections:
      </h1>
      <div
        className="w-100 rounded-border p-3"
        // data-ride="carousel"
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(4, 1fr)",
          background: "#DFEFD3",
          overflow: "hidden"
        }}
      >
        { props.children }
      </div>
    </div>
  );
}

export { CannedSearchContainer, CannedSearchResult };