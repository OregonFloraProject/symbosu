/**
 * Replacement for inner getOnDemandLazySlides function in react-slick (exposed to props by package
 * patch in patches/folder) with: (1) fix for calculation of slides that must be loaded when going
 * backwards from 0 with uneven offset (i.e. `slideCount % slidesToShow !== 0`), and (2) adding a
 * buffer of `slidesToShow` on either side to allow for seamless dragging of the carousel.
 */
export function getOnDemandLazySlides(spec) {
  let onDemandSlides = [];
  let startIndex = spec.currentSlide;
  let endIndex = spec.currentSlide + spec.slidesToShow;
  // fix prev miscalculation in case of uneven offset
  if (endIndex > spec.slideCount) {
    endIndex = spec.slideCount;
    startIndex = endIndex - spec.slidesToShow;
  }
  // add buffer on either side
  endIndex = Math.min(endIndex + spec.slidesToShow, spec.slideCount);
  const startIndexAdjusted = Math.max(startIndex - spec.slidesToShow, 0);
  for (let slideIndex = startIndexAdjusted; slideIndex < endIndex; slideIndex++) {
    if (spec.lazyLoadedList.indexOf(slideIndex) < 0) {
      onDemandSlides.push(slideIndex);
    }
  }
  if (startIndex - spec.slidesToShow < 0) {
    const startIndexWrapped = (startIndex - spec.slidesToShow + spec.slideCount) % spec.slideCount;
    for (let slideIndexWrapped = startIndexWrapped; slideIndexWrapped < spec.slideCount; slideIndexWrapped++) {
      if (spec.lazyLoadedList.indexOf(slideIndexWrapped) < 0) {
        onDemandSlides.push(slideIndexWrapped);
      }
    }
  }
  return onDemandSlides;
}
