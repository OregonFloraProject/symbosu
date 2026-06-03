class MediaViewer {
    constructor() {
        this.buildDOM();
        this.bindEvents();
        this.isOpen = false;
    }

    buildDOM() {
        // Build the viewer following secure DOM manipulation guidelines
        this.container = document.createElement('div');
        this.container.className = 'symb-media-viewer';

        const backdrop = document.createElement('div');
        backdrop.className = 'symb-mv-backdrop';
        this.container.appendChild(backdrop);

        this.closeBtn = document.createElement('div');
        this.closeBtn.className = 'symb-mv-close';
        this.closeBtn.innerHTML = '&times;';
        this.container.appendChild(this.closeBtn);

        this.imageContainer = document.createElement('div');
        this.imageContainer.className = 'symb-mv-image-container';

        this.loader = document.createElement('div');
        this.loader.className = 'symb-mv-loader';
        this.imageContainer.appendChild(this.loader);

        this.image = document.createElement('img');
        this.image.className = 'symb-mv-image';
        this.imageContainer.appendChild(this.image);

        this.container.appendChild(this.imageContainer);

        this.panel = document.createElement('div');
        this.panel.className = 'symb-mv-panel';

        this.title = document.createElement('h2');
        this.title.className = 'symb-mv-title';
        this.panel.appendChild(this.title);

        this.metaGrid = document.createElement('div');
        this.metaGrid.className = 'symb-mv-meta-grid';
        this.panel.appendChild(this.metaGrid);

        this.container.appendChild(this.panel);

        document.body.appendChild(this.container);
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
    }

    createMetaItem(label, value, isLink = false, linkTarget = '_blank') {
        if (!value) return null;
        
        const item = document.createElement('div');
        item.className = 'symb-mv-meta-item';
        
        const strong = document.createElement('strong');
        strong.textContent = label;
        item.appendChild(strong);
        
        if (isLink) {
            const a = document.createElement('a');
            a.href = value.url || value;
            a.target = linkTarget;
            a.textContent = value.text || value.url || value;
            item.appendChild(a);
        } else {
            const span = document.createElement('span');
            span.textContent = value;
            item.appendChild(span);
        }
        
        return item;
    }

    open(data) {
        this.isOpen = true;
        this.container.classList.add('active');
        this.container.classList.add('loading');
        document.body.style.overflow = 'hidden';

        // Set image source safely
        this.image.src = data.lgurl || data.url || '';
        this.image.alt = data.caption || 'Image';

        // Clear previous metadata securely
        this.title.textContent = data.title || 'Image Details';
        if (this.metaGrid.replaceChildren) {
            this.metaGrid.replaceChildren();
        } else {
            this.metaGrid.textContent = '';
        }

        // Add metadata items
        const items = [
            this.createMetaItem('Caption', data.caption),
            this.createMetaItem('Photographer', data.photographer),
            this.createMetaItem('Copyright', data.copyright),
            this.createMetaItem('Source', data.sourceurl ? {url: data.sourceurl, text: data.sourceurl} : null, true),
            this.createMetaItem('View Full Image', data.lgurl || data.url, true)
        ];

        items.forEach(item => {
            if (item) this.metaGrid.appendChild(item);
        });
    }

    close() {
        this.isOpen = false;
        this.container.classList.remove('active');
        document.body.style.overflow = '';
        // Clear image to prevent seeing old image on next open before new one loads
        setTimeout(() => {
            if (!this.isOpen) {
                this.image.src = '';
            }
        }, 300);
    }
}

// Initialize globally
let symbMediaViewer = null;
document.addEventListener('DOMContentLoaded', () => {
    if (!symbMediaViewer) {
        symbMediaViewer = new MediaViewer();
    }
});
