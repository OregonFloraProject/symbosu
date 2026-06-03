import React from 'react';
import Lightbox from 'react-image-lightbox';

export default function SharedLightbox({ isOpen, images, photoIndex, sciName, clientRoot, onClose, onNavigate }) {
  if (!isOpen || !images || images.length === 0) return null;
  const currentImg = images[photoIndex];
  if (!currentImg) return null;

  const recordLink = currentImg.occid 
    ? `${clientRoot}/collections/individual/index.php?occid=${currentImg.occid}`
    : `${clientRoot}/imagelib/imgdetails.php?imgid=${currentImg.imgid}`;

  const lightboxCaption = (
    <div className="lightbox-caption-container">
      
      <div className="lightbox-caption-name">
        <span className="font-italic">{sciName}</span>
      </div>

      <div className="lightbox-caption-loc desktop-only">
        {currentImg.fulldate && <span>{currentImg.fulldate}</span>}
        {currentImg.locality && (
          <span>
            {' '}in <a href={recordLink} target="_blank" rel="noopener noreferrer">{currentImg.locality}</a>
          </span>
        )}
      </div>

      <div className="lightbox-caption-middle desktop-only">
        {currentImg.photographer && <div>&copy; {currentImg.photographer}</div>}
        {currentImg.collectionname && <div>Courtesy of {currentImg.collectionname}</div>}
        {currentImg.owner && <div>Owner: {currentImg.owner}</div>}
        {currentImg.rights && <div>Rights: {currentImg.rights}</div>}
        {currentImg.accessRights && <div>Access Rights: {currentImg.accessRights}</div>}
        {currentImg.copyright && <div>License: {currentImg.copyright}</div>}
      </div>

      <details className="lightbox-mobile-details mobile-only">
        <summary className="lightbox-mobile-summary">More Info</summary>
        
        <div className="lightbox-caption-loc">
          {currentImg.fulldate && <span>{currentImg.fulldate}</span>}
          {currentImg.locality && (
            <span>
              {' '}in <a href={recordLink} target="_blank" rel="noopener noreferrer">{currentImg.locality}</a>
            </span>
          )}
        </div>

        <div className="lightbox-caption-middle">
          {currentImg.photographer && <div>&copy; {currentImg.photographer}</div>}
          {currentImg.collectionname && <div>Courtesy of {currentImg.collectionname}</div>}
          {currentImg.owner && <div>Owner: {currentImg.owner}</div>}
          {currentImg.rights && <div>Rights: {currentImg.rights}</div>}
          {currentImg.accessRights && <div>Access Rights: {currentImg.accessRights}</div>}
          {currentImg.copyright && <div>License: {currentImg.copyright}</div>}
        </div>
      </details>

      <div className="lightbox-caption-right">
        <a 
          href={recordLink} 
          target="_blank" 
          rel="noopener noreferrer"
          className="lightbox-caption-btn"
        >
          Full Record &#x2197;
        </a>
      </div>

    </div>
  );

  return (
    <Lightbox
      mainSrc={images[photoIndex].url}
      nextSrc={images.length > 1 ? images[(photoIndex + 1) % images.length].url : undefined}
      prevSrc={images.length > 1 ? images[(photoIndex + images.length - 1) % images.length].url : undefined}
      imagePadding={typeof window !== 'undefined' && window.innerWidth <= 768 ? 10 : 85}
      onCloseRequest={onClose}
      onMovePrevRequest={() => onNavigate((photoIndex + images.length - 1) % images.length)}
      onMoveNextRequest={() => onNavigate((photoIndex + 1) % images.length)}
      imageCaption={lightboxCaption}
      toolbarButtons={[
        <div key="counter" style={{ color: '#bbb', fontSize: '1.2rem', paddingRight: '20px', lineHeight: '50px' }}>
          {photoIndex + 1} of {images.length}
        </div>,
        <button
          key="fullscreen"
          type="button"
          className="ril__toolbarItemChild ril__builtinButton"
          title="Toggle Fullscreen"
          style={{
            background: 'url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGcgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIGZpbGw9Im5vbmUiPjxwYXRoIGQ9Ik0yIDdWMmg1Ii8+PHBhdGggZD0iTTE4IDdWMmgtNSIvPjxwYXRoIGQ9Ik0yIDEzdjVoNSIvPjxwYXRoIGQ9Ik0xOCAxM3Y1aC01Ii8+PC9nPjwvc3ZnPg==") no-repeat center'
          }}
          onClick={() => {
            if (!document.fullscreenElement) {
              document.documentElement.requestFullscreen().catch(() => {});
            } else {
              document.exitFullscreen();
            }
          }}
        />
      ]}
      reactModalStyle={{ overlay: { zIndex: 100001 } }}
    />
  );
}
