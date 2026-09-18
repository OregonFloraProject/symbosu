import React, { useState } from 'react';
import ImageGallery from '../../common/imageGallery.jsx';
import ImageModal from '../../common/modal.jsx';

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
      <ImageModal
        show={isOpen}
        currImage={currImage}
        images={modalImages}
        altname={modalAltname}
        onClose={toggleImageModal}
        clientRoot={props.clientRoot}
      >
        <h3>
          <span>{modalTitle}</span> images
        </h3>
      </ImageModal>
    </>
  );
}

export default TaxaImageGallery;
