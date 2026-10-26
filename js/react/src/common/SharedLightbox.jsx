import { useEffect, useRef } from 'react';

/**
 * React adapter for the site-wide image viewer loaded by header.php.
 * Legacy PHP and React pages therefore share the same viewer behavior.
 */
export default function SharedLightbox(props) {
  const retries = useRef(0);
  const timeout = useRef(null);
  const latestProps = useRef(props);
  latestProps.current = props;

  useEffect(() => {
    const syncViewer = () => {
      const currentProps = latestProps.current;

      if (!currentProps.isOpen) {
        if (window.symbMediaViewer && window.symbMediaViewer.isOpen) window.symbMediaViewer.close();
        return;
      }

      if (!window.symbMediaViewer) {
        // media-viewer.js is loaded by header.php; give it a moment, but don't spin forever if it never arrives.
        retries.current += 1;
        if (retries.current <= 100) timeout.current = window.setTimeout(syncViewer, 50);
        return;
      }
      retries.current = 0;

      window.symbMediaViewer.open(currentProps.images || [], {
        index: currentProps.photoIndex || 0,
        sciName: currentProps.sciName,
        clientRoot: currentProps.clientRoot,
        onNavigate: (index) => latestProps.current.onNavigate(index),
        onClose: () => latestProps.current.onClose(),
      });
    };

    syncViewer();

    return () => {
      if (timeout.current !== null) {
        window.clearTimeout(timeout.current);
        timeout.current = null;
      }
    };
  }, [props.isOpen, props.images, props.sciName]);

  useEffect(() => () => {
    if (window.symbMediaViewer && window.symbMediaViewer.isOpen) window.symbMediaViewer.close();
  }, []);

  return null;
}
