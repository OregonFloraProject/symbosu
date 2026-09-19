import React from 'react';
import Pagination from 'react-responsive-pagination';

function ResultPagination(props) {
  const {
    page,
    totalPages,
    countLimit,
    onPageChange,
    onCountLimitChange,
    pageSizes = [20, 50, 100],
  } = props;

  if (totalPages < 1) {
    return null;
  }

  return (
    <div style={{ display: 'flex', alignItems: 'center' }}>
      <div style={{ flex: 1 }} />
      <div style={{ flex: 1 }}>
        <Pagination
          total={totalPages}
          current={page}
          onPageChange={onPageChange}
        />
      </div>
      <div style={{
        flex: 1,
        display: 'flex',
        justifyContent: 'flex-end',
        marginBottom: '0.7rem'
      }}>
        <select
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
