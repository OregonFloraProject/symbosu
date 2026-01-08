import React from 'react';
import Slider from 'react-slick';

import { getOnDemandLazySlides } from './imageCarouselUtils';

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faChevronRight, faChevronLeft } from '@fortawesome/free-solid-svg-icons';
library.add(faChevronRight, faChevronLeft);

/*
	moving the slideshow loop into this page will mean making the toggle accessible to both this and taxa/main.jsx - I've tried twice
*/
function ImageCarousel(props) {
  /* https://github.com/akiran/react-slick/issues/1195 */
  const SlickButtonFix = ({ currentSlide, slideCount, children, ...props }) => <span {...props}>{children}</span>;

  const slickSettings = {
    autoplay: false,
    autoplaySpeed: 8000,
    dots: false,
    infinite: props.imageCount > 5,
    lazyLoad: true,
    slidesToShow: props.slideshowCount,
    slidesToScroll: props.slideshowCount,
    nextArrow: (
      <SlickButtonFix>
        <FontAwesomeIcon icon="chevron-right" />
      </SlickButtonFix>
    ),
    prevArrow: (
      <SlickButtonFix>
        <FontAwesomeIcon icon="chevron-left" />
      </SlickButtonFix>
    ),
    getOnDemandLazySlides,
  };
  return (
    <div className="mt-4 dashed-border taxa-slideshows">
      <h3 className="text-light-green font-weight-bold mt-2">{props.title}</h3>
      <div className="slider-wrapper">
        <Slider {...slickSettings} className="mx-auto" style={{ maxWidth: '90%' }}>
          {props.images.map((image, index) => {
            return (
              <div key={image.url}>
                <div className="card" style={{ padding: '0.6em' }}>
                  <div style={{ position: 'relative', width: '100%', height: '7em', borderRadius: '0.25em' }}>
                    <img
                      className="d-block"
                      style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                      src={image.thumbnailurl}
                      alt={image.thumbnailurl}
                      onClick={() => props.onClick(index)}
                    />
                  </div>
                </div>
              </div>
            );
          })}
        </Slider>
      </div>
    </div>
  );
}

export default ImageCarousel;
