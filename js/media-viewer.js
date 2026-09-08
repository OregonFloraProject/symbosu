class MediaViewer {
    constructor() {
        const viewerScript = document.querySelector('script[src*="/js/media-viewer.js"]');
        this.clientRoot = viewerScript ? (viewerScript.dataset.clientRoot || '') : '';
        this.items = [];
        this.index = 0;
        this.zoomLevel = 1;
        this.translateX = 0;
        this.translateY = 0;
        this.pointers = new Map();
        this.isOpen = false;
        this.buildDOM();
        this.bindEvents();
    }

    svg(path) {
        return `<svg aria-hidden="true" viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none">${path}</svg>`;
    }

    button(className, label, icon) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `symb-mv-icon-btn ${className}`;
        button.setAttribute('aria-label', label);
        button.title = label;
        button.innerHTML = icon;
        return button;
    }

    setText(element, text) {
        element.textContent = text;
    }

    httpUrl(value) {
        const url = String(value ?? '').trim();
        return /^https?:\/\//i.test(url) ? url : '';
    }

    decodeText(value) {
        if (value === undefined || value === null) return '';
        const decoder = document.createElement('textarea');
        decoder.innerHTML = String(value);
        return decoder.value;
    }

    buildDOM() {
        this.container = document.createElement('div');
        this.container.className = 'symb-media-viewer';
        this.container.setAttribute('role', 'dialog');
        this.container.setAttribute('aria-modal', 'true');
        this.container.setAttribute('aria-label', 'Image viewer');
        this.container.setAttribute('aria-hidden', 'true');

        this.imageContainer = document.createElement('div');
        this.imageContainer.className = 'symb-mv-image-container';
        this.loader = document.createElement('div');
        this.loader.className = 'symb-mv-loader';
        this.loader.setAttribute('aria-hidden', 'true');
        this.imageContainer.appendChild(this.loader);

        this.image = document.createElement('img');
        this.image.className = 'symb-mv-image';
        this.image.draggable = false;
        this.imageContainer.appendChild(this.image);

        this.previousBtn = this.button('symb-mv-previous', 'Previous image', this.svg('<path d="m15 18-6-6 6-6"></path>'));
        this.nextBtn = this.button('symb-mv-next', 'Next image', this.svg('<path d="m9 18 6-6-6-6"></path>'));
        this.imageContainer.appendChild(this.previousBtn);
        this.imageContainer.appendChild(this.nextBtn);

        const topControls = document.createElement('div');
        topControls.className = 'symb-mv-top-controls';
        this.counter = document.createElement('div');
        this.counter.className = 'symb-mv-counter';
        this.counter.setAttribute('aria-live', 'polite');
        topControls.appendChild(this.counter);
        this.fullscreenBtn = this.button('symb-mv-fullscreen', 'Enter fullscreen', this.svg('<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>'));
        this.closeBtn = this.button('symb-mv-close', 'Close image viewer', this.svg('<path d="M18 6 6 18M6 6l12 12"></path>'));
        topControls.appendChild(this.fullscreenBtn);
        topControls.appendChild(this.closeBtn);
        this.imageContainer.appendChild(topControls);

        const zoomControls = document.createElement('div');
        zoomControls.className = 'symb-mv-zoom-controls';
        this.zoomOutBtn = this.button('symb-mv-zoom-out', 'Zoom out', this.svg('<circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35M8 11h6"></path>'));
        this.zoomInBtn = this.button('symb-mv-zoom-in', 'Zoom in', this.svg('<circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35M11 8v6M8 11h6"></path>'));
        zoomControls.appendChild(this.zoomOutBtn);
        zoomControls.appendChild(this.zoomInBtn);
        this.imageContainer.appendChild(zoomControls);

        this.panel = document.createElement('div');
        this.panel.className = 'symb-mv-panel';
        this.container.appendChild(this.imageContainer);
        this.container.appendChild(this.panel);
        document.body.appendChild(this.container);
    }

    bindEvents() {
        this.closeBtn.addEventListener('click', () => this.close());
        this.previousBtn.addEventListener('click', () => this.navigate(-1));
        this.nextBtn.addEventListener('click', () => this.navigate(1));
        this.zoomInBtn.addEventListener('click', () => this.setZoom(this.zoomLevel * 1.35));
        this.zoomOutBtn.addEventListener('click', () => this.setZoom(this.zoomLevel / 1.35));
        this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
        this.image.addEventListener('load', () => this.container.classList.remove('loading'));
        this.image.addEventListener('error', () => {
            const item = this.items[this.index];
            if (!this.fallbackAttempted && item && item.url && item.url !== item.imageUrl) {
                this.fallbackAttempted = true;
                this.image.src = item.url;
                return;
            }
            this.container.classList.remove('loading');
        });
        this.imageContainer.addEventListener('wheel', (event) => {
            event.preventDefault();
            this.setZoom(this.zoomLevel * (event.deltaY < 0 ? 1.12 : 1 / 1.12), event.clientX, event.clientY);
        }, { passive: false });
        this.imageContainer.addEventListener('pointerdown', (event) => this.pointerDown(event));
        this.imageContainer.addEventListener('pointermove', (event) => this.pointerMove(event));
        this.imageContainer.addEventListener('pointerup', (event) => this.pointerUp(event));
        this.imageContainer.addEventListener('pointercancel', (event) => this.pointerUp(event));
        document.addEventListener('keydown', (event) => {
            if (!this.isOpen) return;
            if (event.key === 'Escape') this.close();
            if (event.key === 'ArrowLeft') this.navigate(-1);
            if (event.key === 'ArrowRight') this.navigate(1);
            if (event.key === '+' || event.key === '=') this.setZoom(this.zoomLevel * 1.35);
            if (event.key === '-') this.setZoom(this.zoomLevel / 1.35);
        });
        document.addEventListener('fullscreenchange', () => this.updateFullscreenButton());
        window.addEventListener('resize', () => {
            if (!this.isOpen) return;
            window.cancelAnimationFrame(this.resizeFrame);
            this.resizeFrame = window.requestAnimationFrame(() => this.setZoom(1));
        });
    }

    pointerDown(event) {
        if (event.target.closest('button, a, summary')) return;
        this.imageContainer.setPointerCapture(event.pointerId);
        this.pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
        if (this.pointers.size === 1) {
            this.dragStart = { x: event.clientX, y: event.clientY, translateX: this.translateX, translateY: this.translateY, time: Date.now() };
        } else if (this.pointers.size === 2) {
            const points = Array.from(this.pointers.values());
            this.pinchStart = { distance: this.distance(points[0], points[1]), zoom: this.zoomLevel };
        }
    }

    pointerMove(event) {
        if (!this.pointers.has(event.pointerId)) return;
        this.pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
        if (this.pointers.size === 2 && this.pinchStart) {
            const points = Array.from(this.pointers.values());
            this.setZoom(this.pinchStart.zoom * (this.distance(points[0], points[1]) / this.pinchStart.distance));
        } else if (this.pointers.size === 1 && this.zoomLevel > 1 && this.dragStart) {
            this.translateX = this.dragStart.translateX + event.clientX - this.dragStart.x;
            this.translateY = this.dragStart.translateY + event.clientY - this.dragStart.y;
            this.updateTransform();
        }
    }

    pointerUp(event) {
        const point = this.pointers.get(event.pointerId);
        this.pointers.delete(event.pointerId);
        if (this.pointers.size < 2) this.pinchStart = null;
        if (point && this.zoomLevel === 1 && this.dragStart && Date.now() - this.dragStart.time < 600) {
            const deltaX = point.x - this.dragStart.x;
            const deltaY = point.y - this.dragStart.y;
            if (Math.abs(deltaX) > 60 && Math.abs(deltaX) > Math.abs(deltaY)) this.navigate(deltaX < 0 ? 1 : -1);
        }
        if (this.pointers.size === 0) this.dragStart = null;
    }

    distance(first, second) {
        return Math.hypot(second.x - first.x, second.y - first.y);
    }

    setZoom(zoom, originX, originY) {
        const previousZoom = this.zoomLevel;
        this.zoomLevel = Math.max(1, Math.min(zoom, 10));
        if (this.zoomLevel === 1) {
            this.translateX = 0;
            this.translateY = 0;
        } else if (originX !== undefined && originY !== undefined && previousZoom !== this.zoomLevel) {
            const rect = this.imageContainer.getBoundingClientRect();
            const x = originX - rect.left - rect.width / 2;
            const y = originY - rect.top - rect.height / 2;
            const ratio = this.zoomLevel / previousZoom;
            this.translateX = x - (x - this.translateX) * ratio;
            this.translateY = y - (y - this.translateY) * ratio;
        }
        this.updateTransform();
    }

    updateTransform() {
        this.image.style.transform = `translate3d(${this.translateX}px, ${this.translateY}px, 0) scale(${this.zoomLevel})`;
        this.imageContainer.classList.toggle('is-zoomed', this.zoomLevel > 1);
        this.zoomOutBtn.disabled = this.zoomLevel <= 1;
        this.zoomInBtn.disabled = this.zoomLevel >= 10;
        this.previousBtn.disabled = this.zoomLevel > 1;
        this.nextBtn.disabled = this.zoomLevel > 1;
    }

    normalizeItem(item, options) {
        const clientRoot = String(options.clientRoot ?? this.clientRoot).replace(/\/+$/, '');
        let recordUrl = this.httpUrl(item.recordUrl || item.recordurl);
        if (!recordUrl && item.occid) recordUrl = `${clientRoot}/collections/individual/index.php?occid=${item.occid}`;
        if (!recordUrl && item.mediaid) recordUrl = `${clientRoot}/imagelib/imgdetails.php?mediaid=${item.mediaid}`;
        if (!recordUrl && item.imgid) recordUrl = `${clientRoot}/imagelib/imgdetails.php?imgid=${item.imgid}`;
        if (!recordUrl) recordUrl = this.httpUrl(item.sourceurl);
        const normalized = Object.assign({}, item, {
            imageUrl: item.lgurl || item.url || '',
            title: item.title || options.title || options.sciName || '',
            recordUrl,
            clientRoot
        });
        [
            'title', 'caption', 'photographer', 'collectionname', 'owner',
            'rights', 'accessRights', 'copyright', 'copyrightHolder',
            'rightsholder', 'licenseUrl', 'licenseLabel', 'locality', 'county',
            'stateprovince', 'country', 'fulldate'
        ].forEach((field) => {
            normalized[field] = this.decodeText(normalized[field]);
        });
        const isLicense = (value) => /creativecommons\.org\/(?:licenses|publicdomain)\//i.test(value)
            || /^(CC0|CC[ -]BY|Creative Commons|Public Domain)\b/i.test(value);
        normalized.licenseUrl = this.httpUrl(normalized.licenseUrl);
        const mediaRights = normalized.rights.trim();
        const rawCopyright = normalized.copyright.trim();
        if (!normalized.licenseUrl && /^https?:\/\//i.test(mediaRights) && isLicense(mediaRights)) normalized.licenseUrl = mediaRights;
        if (!normalized.licenseLabel && isLicense(mediaRights)) normalized.licenseLabel = mediaRights;
        if (!normalized.licenseLabel && isLicense(rawCopyright)) normalized.licenseLabel = rawCopyright;
        const publicDomain = normalized.publicDomain === true || normalized.publicDomain === 'true' || normalized.publicDomain === 1 || normalized.publicDomain === '1';
        if (!normalized.copyrightHolder && !publicDomain) {
            if (rawCopyright && !isLicense(rawCopyright)) normalized.copyrightHolder = rawCopyright;
            else if (mediaRights && !isLicense(mediaRights)) normalized.copyrightHolder = mediaRights.replace(/^\s*©\s*/u, '').trim();
            else normalized.copyrightHolder = normalized.rightsholder || normalized.photographer;
        }
        return normalized;
    }

    open(input, options = {}) {
        const config = Array.isArray(input) ? options : Object.assign({}, options, input.options || {});
        let rawItems = Array.isArray(input) ? input : (input.items || [input]);
        if (!Array.isArray(input) && config.triggerElement) {
            const galleryItems = Array.from(document.querySelectorAll('[data-symb-media]'));
            const triggerIndex = galleryItems.indexOf(config.triggerElement);
            if (triggerIndex !== -1) {
                rawItems = galleryItems.map((element) => Object.assign({}, element.dataset));
                config.index = triggerIndex;
            }
        }
        this.items = rawItems.map((item) => this.normalizeItem(item, config));
        if (!this.items.length) return;
        this.index = Math.max(0, Math.min(config.index ?? input.index ?? 0, this.items.length - 1));
        this.onNavigate = config.onNavigate || null;
        this.onClose = config.onClose || null;
        if (!this.isOpen) {
            this.previousBodyOverflow = document.body.style.overflow;
            this.previouslyFocused = document.activeElement;
        }
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
        this.container.classList.add('active');
        this.panel.classList.add('active');
        this.container.setAttribute('aria-hidden', 'false');
        this.renderItem();
        this.closeBtn.focus();
    }

    renderItem() {
        const item = this.items[this.index];
        this.setZoom(1);
        this.panel.classList.toggle('is-hidden', item.hideFooter === true || item.hideFooter === 'true');
        this.counter.classList.toggle('is-hidden', item.hideCounter === true || item.hideCounter === 'true');
        this.container.classList.add('loading');
        this.fallbackAttempted = false;
        this.image.src = item.imageUrl;
        this.image.alt = item.alt || item.caption || item.title || 'Viewed image';
        this.setText(this.counter, `${this.index + 1}/${this.items.length}`);
        this.previousBtn.hidden = this.items.length < 2;
        this.nextBtn.hidden = this.items.length < 2;
        this.renderPanel(item);
        if (this.onNavigate) this.onNavigate(this.index);
    }

    appendText(parent, className, text) {
        if (!text) return;
        const element = document.createElement('div');
        element.className = className;
        this.setText(element, text);
        parent.appendChild(element);
    }

    appendLicense(parent, licenseLabel, licenseUrl) {
        if (!licenseLabel && !licenseUrl) return;
        const element = document.createElement('div');
        element.appendChild(document.createTextNode('License: '));
        const target = licenseUrl || (/^https?:\/\//i.test(licenseLabel) ? licenseLabel : '');
        if (target) {
            const link = document.createElement('a');
            link.href = target;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            const ccMatch = target.match(/creativecommons\.org\/(?:licenses|publicdomain)\/([^/]+)\/(\d+(?:\.\d+)?)?/i);
            let label = licenseLabel || target;
            if (ccMatch && !licenseLabel) {
                const isCc0 = ccMatch[1].toLowerCase() === 'zero';
                const code = isCc0 ? 'CC0' : `CC ${ccMatch[1].toUpperCase()}`;
                const versionedCode = ccMatch[2] ? `${code} ${ccMatch[2]}` : code;
                label = versionedCode;
            }
            this.setText(link, label);
            element.appendChild(link);
        } else {
            element.appendChild(document.createTextNode(licenseLabel));
        }
        parent.appendChild(element);
    }

    renderPanel(item) {
        this.panel.replaceChildren();
        const identity = document.createElement('div');
        identity.className = 'symb-mv-identity';
        this.appendText(identity, 'symb-mv-panel-title', item.title);
        const placeBits = [item.county ? `${item.county} County` : '', item.stateprovince, item.country].filter(Boolean);
        const place = placeBits.length ? placeBits.join(', ') : item.locality;
        if (item.fulldate || place) {
            const locality = document.createElement('div');
            locality.className = 'symb-mv-locality';
            if (item.fulldate) {
                locality.appendChild(document.createTextNode(item.fulldate));
            }
            if (item.fulldate && place) {
                locality.appendChild(document.createTextNode(' in '));
            }
            if (place) {
                const hasGeography = item.country || item.stateprovince || item.county;
                const placeElement = document.createElement(hasGeography ? 'a' : 'span');
                this.setText(placeElement, place);
                if (hasGeography) {
                    const params = new URLSearchParams({ reset: '1' });
                    if (item.country) params.set('country', item.country);
                    if (item.stateprovince) params.set('state', item.stateprovince);
                    if (item.county) params.set('county', item.county);
                    placeElement.href = `${item.clientRoot}/collections/list.php?${params.toString()}`;
                    placeElement.target = '_blank';
                    placeElement.rel = 'noopener noreferrer';
                    placeElement.title = 'View occurrence records from this area';
                }
                locality.appendChild(placeElement);
            }
            identity.appendChild(locality);
        }
        this.panel.appendChild(identity);

        const attribution = document.createElement('div');
        attribution.className = 'symb-mv-attribution';
        this.appendText(attribution, '', item.copyrightHolder ? `© ${item.copyrightHolder}` : '');
        this.appendText(attribution, '', item.collectionname ? `Courtesy of ${item.collectionname}` : '');
        this.appendLicense(attribution, item.licenseLabel, item.licenseUrl);
        this.appendText(attribution, 'symb-mv-caption', item.caption);
        if (!attribution.children.length) attribution.setAttribute('aria-hidden', 'true');
        this.panel.appendChild(attribution);

        const link = document.createElement('a');
        link.className = 'symb-mv-btn';
        if (item.recordUrl) {
            link.href = item.recordUrl;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            this.setText(link, 'Full Record ↗');
        } else {
            link.classList.add('is-empty');
            link.setAttribute('aria-hidden', 'true');
            link.tabIndex = -1;
        }
        this.panel.appendChild(link);
    }

    navigate(offset) {
        if (this.items.length < 2 || this.zoomLevel > 1) return;
        this.index = (this.index + offset + this.items.length) % this.items.length;
        this.renderItem();
    }

    toggleFullscreen() {
        if (!document.fullscreenElement) this.container.requestFullscreen().catch(() => {});
        else if (document.exitFullscreen) document.exitFullscreen();
    }

    updateFullscreenButton() {
        const fullscreen = document.fullscreenElement === this.container;
        const label = fullscreen ? 'Exit fullscreen' : 'Enter fullscreen';
        this.fullscreenBtn.setAttribute('aria-label', label);
        this.fullscreenBtn.title = label;
    }

    close() {
        if (!this.isOpen) return;
        this.isOpen = false;
        this.container.classList.remove('active', 'loading');
        this.panel.classList.remove('active');
        this.container.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = this.previousBodyOverflow;
        this.pointers.clear();
        window.cancelAnimationFrame(this.resizeFrame);
        if (document.fullscreenElement === this.container && document.exitFullscreen) document.exitFullscreen().catch(() => {});
        this.image.removeAttribute('src');
        const onClose = this.onClose;
        this.onClose = null;
        this.onNavigate = null;
        if (this.previouslyFocused && document.contains(this.previouslyFocused)) this.previouslyFocused.focus();
        this.previouslyFocused = null;
        if (onClose) onClose();
    }
}

window.symbMediaViewer = null;
document.addEventListener('DOMContentLoaded', () => {
    if (!window.symbMediaViewer) window.symbMediaViewer = new MediaViewer();
});
