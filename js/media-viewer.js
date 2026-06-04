class MediaViewer {
    constructor() {
        this.svgExpand = '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>';
        this.svgCompress = '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path></svg>';
        this.svgZoomIn = '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>';
        this.svgZoomOut = '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>';
        this.svgClose = '<svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

        this.zoomLevel = 1;
        this.translateX = 0;
        this.translateY = 0;
        this.isDragging = false;
        this.isFullscreen = false;

        this.buildDOM();
        this.bindEvents();
        this.isOpen = false;
    }

    buildDOM() {
        this.container = document.createElement('div');
        this.container.className = 'symb-media-viewer';

        const backdrop = document.createElement('div');
        backdrop.className = 'symb-mv-backdrop';
        this.container.appendChild(backdrop);

        this.topControls = document.createElement('div');
        this.topControls.className = 'symb-mv-top-controls';

        this.fullscreenBtn = document.createElement('div');
        this.fullscreenBtn.className = 'symb-mv-icon-btn';
        this.fullscreenBtn.innerHTML = this.svgExpand;
        this.fullscreenBtn.title = 'Toggle Fullscreen';
        this.topControls.appendChild(this.fullscreenBtn);

        this.closeBtn = document.createElement('div');
        this.closeBtn.className = 'symb-mv-icon-btn';
        this.closeBtn.innerHTML = this.svgClose;
        this.closeBtn.title = 'Close';
        this.topControls.appendChild(this.closeBtn);

        this.container.appendChild(this.topControls);

        this.imageContainer = document.createElement('div');
        this.imageContainer.className = 'symb-mv-image-container';

        this.loader = document.createElement('div');
        this.loader.className = 'symb-mv-loader';
        this.imageContainer.appendChild(this.loader);

        this.image = document.createElement('img');
        this.image.className = 'symb-mv-image';
        this.imageContainer.appendChild(this.image);

        this.zoomControls = document.createElement('div');
        this.zoomControls.className = 'symb-mv-zoom-controls';

        this.zoomOutBtn = document.createElement('div');
        this.zoomOutBtn.className = 'symb-mv-icon-btn';
        this.zoomOutBtn.innerHTML = this.svgZoomOut;
        this.zoomOutBtn.title = 'Zoom Out';
        this.zoomControls.appendChild(this.zoomOutBtn);

        this.zoomInBtn = document.createElement('div');
        this.zoomInBtn.className = 'symb-mv-icon-btn';
        this.zoomInBtn.innerHTML = this.svgZoomIn;
        this.zoomInBtn.title = 'Zoom In';
        this.zoomControls.appendChild(this.zoomInBtn);

        this.imageContainer.appendChild(this.zoomControls);
        this.container.appendChild(this.imageContainer);

        this.panel = document.createElement('div');
        this.panel.className = 'symb-mv-panel';
        this.container.appendChild(this.panel);

        document.body.appendChild(this.container);
    }

    updateTransform() {
        this.image.style.transform = `translate(${this.translateX}px, ${this.translateY}px) scale(${this.zoomLevel})`;
    }

    bindEvents() {
        this.closeBtn.addEventListener('click', () => this.close());
        this.container.addEventListener('click', (e) => {
            if (e.target === this.imageContainer || e.target === this.container || e.target.classList.contains('symb-mv-backdrop')) {
                this.close();
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        this.image.addEventListener('load', () => {
            this.container.classList.remove('loading');
        });
        this.image.addEventListener('error', () => {
            this.container.classList.remove('loading');
        });

        // Zoom Controls
        this.zoomInBtn.addEventListener('click', () => {
            this.zoomLevel = Math.min(this.zoomLevel * 1.5, 10);
            this.updateTransform();
        });

        this.zoomOutBtn.addEventListener('click', () => {
            this.zoomLevel = Math.max(this.zoomLevel / 1.5, 1);
            if (this.zoomLevel === 1) {
                this.translateX = 0;
                this.translateY = 0;
            }
            this.updateTransform();
        });

        this.image.addEventListener('wheel', (e) => {
            e.preventDefault();
            if (e.deltaY < 0) {
                this.zoomLevel = Math.min(this.zoomLevel * 1.1, 10);
            } else {
                this.zoomLevel = Math.max(this.zoomLevel / 1.1, 1);
            }
            if (this.zoomLevel === 1) {
                this.translateX = 0;
                this.translateY = 0;
            }
            this.updateTransform();
        });

        // Panning Logic
        this.image.addEventListener('mousedown', (e) => {
            if (this.zoomLevel > 1) {
                this.isDragging = true;
                this.startX = e.clientX - this.translateX;
                this.startY = e.clientY - this.translateY;
                this.image.style.cursor = 'grabbing';
                e.preventDefault();
            }
        });

        window.addEventListener('mousemove', (e) => {
            if (this.isDragging) {
                this.translateX = e.clientX - this.startX;
                this.translateY = e.clientY - this.startY;
                this.updateTransform();
            }
        });

        window.addEventListener('mouseup', () => {
            if (this.isDragging) {
                this.isDragging = false;
                this.image.style.cursor = this.zoomLevel > 1 ? 'grab' : 'default';
            }
        });

        // Fullscreen Toggle
        this.fullscreenBtn.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                this.container.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });

        document.addEventListener('fullscreenchange', () => {
            this.isFullscreen = !!document.fullscreenElement;
            this.fullscreenBtn.innerHTML = this.isFullscreen ? this.svgCompress : this.svgExpand;
        });
    }

    open(data) {
        this.isOpen = true;
        this.zoomLevel = 1;
        this.translateX = 0;
        this.translateY = 0;
        this.updateTransform();
        this.image.style.cursor = 'default';

        this.container.classList.add('active');
        this.container.classList.add('loading');
        document.body.style.overflow = 'hidden';

        // Set image source safely
        this.image.src = data.lgurl || data.url || '';
        this.image.alt = data.caption || 'Image';

        let occUrl = '';
        if (data.occid) {
            occUrl = '../collections/individual/index.php?occid=' + data.occid;
        } else if (data.imgid) {
            occUrl = '../imagelib/imgdetails.php?imgid=' + data.imgid;
        } else if (data.sourceurl) {
            occUrl = data.sourceurl;
        }

        let titleHtml = data.title ? `<div class="symb-mv-panel-title">${data.title}</div>` : '';
        let captionHtml = data.caption ? `<div class="symb-mv-panel-sub">${data.caption}</div>` : '';
        
        let photographerHtml = data.photographer ? `<div>&copy; ${data.photographer}</div>` : '';
        let ownerHtml = data.owner ? `<div>Owner: ${data.owner}</div>` : '';
        let rightsHtml = data.rights ? `<div>Rights: ${data.rights}</div>` : '';
        let accessRightsHtml = data.accessRights ? `<div>Access Rights: ${data.accessRights}</div>` : '';
        let copyrightHtml = data.copyright ? `<div>License: ${data.copyright}</div>` : '';

        let buttonHtml = `
            <div class="symb-mv-panel-col symb-mv-panel-right">
                ${occUrl ? `<a class="symb-mv-btn" href="${occUrl}" target="_blank">Full Record &#x2197;</a>` : ''}
            </div>
        `;

        this.panel.innerHTML = `
            <div class="symb-mv-panel-col symb-mv-panel-left desktop-only">
                ${titleHtml}
                ${captionHtml}
            </div>
            <div class="symb-mv-panel-col symb-mv-panel-mid desktop-only">
                ${photographerHtml}
                ${ownerHtml}
                ${rightsHtml}
                ${accessRightsHtml}
                ${copyrightHtml}
            </div>
            
            <div class="symb-mv-panel-col symb-mv-panel-left mobile-only" style="margin-bottom:10px;">
                ${titleHtml}
            </div>
            <details class="symb-mv-mobile-details mobile-only">
                <summary class="symb-mv-mobile-summary">More Info</summary>
                <div class="symb-mv-panel-col">
                    ${captionHtml}
                    ${photographerHtml}
                    ${ownerHtml}
                    ${rightsHtml}
                    ${accessRightsHtml}
                    ${copyrightHtml}
                </div>
            </details>

            ${buttonHtml}
        `;
    }

    close() {
        this.isOpen = false;
        this.container.classList.remove('active');
        document.body.style.overflow = '';
        if (document.fullscreenElement) {
            document.exitFullscreen().catch(()=>{});
        }
        // Clear image to prevent seeing old image on next open before new one loads
        setTimeout(() => {
            if (!this.isOpen) {
                this.image.src = '';
            }
        }, 300);
    }
}

// Initialize globally
window.symbMediaViewer = null;
document.addEventListener('DOMContentLoaded', () => {
    if (!window.symbMediaViewer) {
        window.symbMediaViewer = new MediaViewer();
    }
});
