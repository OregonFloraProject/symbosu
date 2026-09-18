import { useState, useEffect } from 'react';

export function useSlideshowCount() {
  const [slideshowCount, setSlideshowCount] = useState(5);

  const updateViewport = () => {
    let newSlideshowCount = 5;
    if (window.innerWidth < 1200) {
      newSlideshowCount = 4;
    }
    if (window.innerWidth < 992) {
      newSlideshowCount = 3;
    }
    setSlideshowCount(newSlideshowCount);
  };

  useEffect(() => {
    window.addEventListener('resize', updateViewport);
    return () => window.removeEventListener('resize', updateViewport);
  }, []);

  return [slideshowCount, updateViewport];
}
