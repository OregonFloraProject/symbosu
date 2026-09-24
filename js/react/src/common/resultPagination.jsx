import React from 'react';
import Pagination from 'react-responsive-pagination';

// Bounds the number of page buttons the library renders so they can wrap onto
// multiple rows instead of the library shrinking the set to fit one row.
const MAX_PAGINATION_WIDTH = 200;

function ResultPagination(props) {
  const {
    page,
    totalPages,
    countLimit,
    onPageChange,
    onCountLimitChange,
    pageSizes = [20, 50, 100],
    style
  } = props;

  if (totalPages < 1) {
    return null;
  }

  return (
    <div className="result-pagination" style={{ ...style }}>
      <div className="result-pagination__spacer" />
      <div className="result-pagination__pager">
        <Pagination
          total={totalPages}
          current={page}
          maxWidth={MAX_PAGINATION_WIDTH}
          onPageChange={(e) => onPageChange(Number(e))}
        />
      </div>
      <div className="result-pagination__page-size">
        <select
          className="pageSizeSelector"
          value={countLimit}
          onChange={(e) => onCountLimitChange(Number(e.target.value))}
        >
          {pageSizes.map((size, i) => {
            return <option key={i} value={size}>{size}</option>;
          })}
        </select>
      </div>
    </div>
  );
}

export default ResultPagination;
