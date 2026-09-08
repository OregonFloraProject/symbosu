import React from 'react';

/**
 * React adapter for the site-wide image viewer loaded by header.php.
 * Legacy PHP and React pages therefore share the same viewer behavior.
 */
export default class SharedLightbox extends React.Component {
  componentDidMount() {
    this.syncViewer();
  }

  componentDidUpdate(previousProps) {
    if (
      this.props.isOpen !== previousProps.isOpen ||
      this.props.images !== previousProps.images ||
      this.props.sciName !== previousProps.sciName
    ) {
      this.syncViewer();
    }
  }

  componentWillUnmount() {
    if (window.symbMediaViewer && window.symbMediaViewer.isOpen) window.symbMediaViewer.close();
  }

  syncViewer() {
    if (!this.props.isOpen) {
      if (window.symbMediaViewer && window.symbMediaViewer.isOpen) window.symbMediaViewer.close();
      return;
    }

    if (!window.symbMediaViewer) {
      // media-viewer.js is loaded by header.php; give it a moment, but don't spin forever if it never arrives.
      this.retries = (this.retries || 0) + 1;
      if (this.retries <= 100) window.setTimeout(() => this.syncViewer(), 50);
      return;
    }
    this.retries = 0;

    window.symbMediaViewer.open(this.props.images || [], {
      index: this.props.photoIndex || 0,
      sciName: this.props.sciName,
      clientRoot: this.props.clientRoot,
      onNavigate: (index) => this.props.onNavigate(index),
      onClose: () => this.props.onClose(),
    });
  }

  render() {
    return null;
  }
}
