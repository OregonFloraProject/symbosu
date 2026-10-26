import React from 'react';
import Loading from '../../common/loading.jsx';

function classNames(...names) {
  return names.filter(Boolean).join(' ');
}

function TaxaPageShell(props) {
  const { containerClassName, wrapperClassName, mainClassName, sidebarClassName, pageTitle, titleBlock, sidebar, isLoading, clientRoot, children } = props;

  return (
    <div className={containerClassName} style={{ minHeight: '45em' }}>
      <Loading clientRoot={clientRoot} isLoading={isLoading} />
      <div className="print-header">
        {pageTitle}
        <br />
        {window.location.href}
      </div>
      <div className="row print-start">
        <div className="col">{titleBlock}</div>
        <div className="col-auto">
          <button className="d-block my-2 btn-primary print-trigger" onClick={() => window.print()}>
            Print page
          </button>
          {/*<button className="d-block my-2 btn-secondary" disabled={ true }>Add to basket</button>*/}
        </div>
      </div>
      <div className={classNames('row', 'mt-2', wrapperClassName, 'main-wrapper')}>
        <div className={classNames('col-md-8', mainClassName, 'main-section')}>{children}</div>
        <div className={classNames('col-md-4', sidebarClassName, 'sidebar-section')}>{sidebar}</div>
      </div>
    </div>
  );
}

export default TaxaPageShell;
