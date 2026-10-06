import React, { useState } from 'react';
import ImageGallery from '../../common/imageGallery.jsx';
import SharedLightbox from '../../common/SharedLightbox.jsx';

function TaxaImageGallery(props) {
  const {
    images,
    herbariumImages,
    slideshowCount,
    title,
    altname,
    herbariumTitle = 'Herbarium specimens',
    herbariumAltname,
    modalTitle,
    modalAltname,
  } = props;

  const isDualBasis = Array.isArray(herbariumImages);
  const [isOpen, setIsOpen] = useState(false);
  const [currImage, setCurrImage] = useState(0);
  const [currImageBasis, setCurrImageBasis] = useState('HumanObservation');

  const toggleImageModal = (image, basis) => {
    setCurrImage(image);
    setIsOpen(!isOpen);
    if (basis) {
      setCurrImageBasis(basis);
    }
  };

  const modalImages = isDualBasis && currImageBasis === 'PreservedSpecimen' ? herbariumImages : images;

  return (
    <>
      {!isDualBasis && (
        <ImageGallery
          title={title}
          images={images}
          altname={altname}
          slideshowCount={slideshowCount}
          onClick={toggleImageModal}
        />
      )}
      {isDualBasis && images.length > 0 && (
        <ImageGallery
          title={title}
          images={images}
          altname={altname}
          slideshowCount={slideshowCount}
          onClick={(index) => toggleImageModal(index, 'HumanObservation')}
        />
      )}
      {isDualBasis && herbariumImages.length > 0 && (
        <ImageGallery
          title={herbariumTitle}
          images={herbariumImages}
          altname={herbariumAltname}
          slideshowCount={slideshowCount}
          onClick={(index) => toggleImageModal(index, 'PreservedSpecimen')}
        />
      )}
      <SharedLightbox
        isOpen={isOpen}
        photoIndex={currImage}
        images={modalImages}
        sciName={modalAltname}
        onClose={() => setIsOpen(false)}
        onNavigate={(index) => setCurrImage(index)}
        clientRoot={props.clientRoot}
      />
    </>
  );
}

export default TaxaImageGallery;
