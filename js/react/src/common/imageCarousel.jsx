import React, { useState, useEffect } from 'react';
import Pagination from 'react-responsive-pagination';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faChevronRight, faChevronLeft, faChevronDown, faChevronUp } from '@fortawesome/free-solid-svg-icons';
library.add(faChevronRight, faChevronLeft, faChevronDown, faChevronUp);

const PHONE_ROW_LIMIT = 3;
const PC_ROW_LIMIT = 5;

function ImageCarousel(props) {
  const totalCount = props.images.length;
  // Generate id from this carousel
  const carouselId = `carousel-${Math.random().toString(36).substring(2)}`;
  const [collapsed, setCollapsed] = useState(true);
  // Persistent page size for expanded view, kept across collapse/expand
  const [countLimit, setCountLimit] = useState(20);
  // Preview size for collapsed view, set by device width only
  const [rowLimit, setRowLimit] = useState(PC_ROW_LIMIT);
  const [totalPages, setTotalPages] = useState(Math.ceil(totalCount / 20));
  const [page, setPage] = useState(1);
  const [index, setIndex] = useState(0);

  // Collapsed shows one preview row, expanded shows the full page
  // React re-renders ImageCarousel and visibleCount is recomputed from the new "collapsed" value. No need for useEffect
  const visibleCount = collapsed ? rowLimit : countLimit;
  
  const handlePageChange = (page) => {
    setPage(page);
    setIndex((page - 1) * countLimit);
  }

  const pageSize = [20, 50, 100];

  const handleChangePageSize = (e) => {
    const nextSize = Number(e.target.value);
    setCountLimit(nextSize);
    // New size starts from first page so index stays in range
    setPage(1);
    setIndex(0);
  }

  const handleCollapseToggle = () => {
    if (!collapsed) {
      document.getElementById(carouselId)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    setCollapsed(!collapsed);
  };

  // Device width controls the collapsed preview only, never the page size
  function adjustRowLimitByDevice() {
    if (window.innerWidth < 768) {
      setRowLimit(PHONE_ROW_LIMIT);
    } else {
      setRowLimit(PC_ROW_LIMIT);
    }
  }

  useEffect(() => {
    adjustRowLimitByDevice();
    window.addEventListener('resize', adjustRowLimitByDevice);
  }, []);

  // Page count follows the expanded page size, not the collapsed preview
  useEffect(() => {
    setTotalPages(Math.ceil(totalCount / countLimit));
  }, [countLimit])

  return (
    <div id={carouselId} className="mt-4 dashed-border taxa-slideshows">
      <h3 className="text-light-green font-weight-bold mt-2">{props.title}</h3>
      <div className={collapsed ? "slider-wrapper" : ""} style={{ display: 'flex', flexDirection: 'row', flexWrap: collapsed ? 'nowrap' : 'wrap' }}>
          {/* Slice from current page start, show preview row when collapsed */}
          {props.images.slice(index, index + visibleCount)
            .map((image, index) => {
            return (
              <div key={image.url} style={collapsed ? { flex: '1 1 0', minWidth: 0 } : { flex: `0 0 ${100 / rowLimit}%`, maxWidth: `${100 / rowLimit}%`, minWidth: 0 }}>
                <div className="card" style={{ padding: '0.6em' }}>
                  <div style={{ position: 'relative', width: '100%', height: '7em', borderRadius: '0.25em' }}>
                    <img
                      className="d-block"
                      style={{ width: '100%', height: '100%', objectFit: 'cover', cursor: 'pointer' }}
                      src={image.thumbnailurl}
                      alt={props.altname + ` ${index + 1}`}
                      onClick={() => props.onClick(index)}
                    />
                  </div>
                </div>
              </div>
            );
          })}
      </div>
      {collapsed ? <></> : (
        <div style={{ display: 'flex', alignItems: 'center' }}>
          <div style={{ flex: 1 }} />
          <div style={{ flex: 1 }}>
            <Pagination
              total={totalPages}
              current={page}
              onPageChange={handlePageChange}
            />
          </div>
          <div style={{ flex: 1, display: 'flex', justifyContent: 'flex-end' }}>
            <select id="pageSize" value={countLimit}
              onChange={handleChangePageSize}
            >
              {pageSize.map((size, i) => {
                return <option key={i} value={size}>{size}</option>
              })}
            </select>
          </div>
        </div>
      )}
      <div style={{ display: 'flex', justifyContent: 'center' }}>
        <FontAwesomeIcon className="slick-down" style={{cursor: 'pointer'}} icon={collapsed ? "chevron-down" : "chevron-up"} onClick={handleCollapseToggle}/>
      </div>
    </div>
  );
}

export default ImageCarousel;
