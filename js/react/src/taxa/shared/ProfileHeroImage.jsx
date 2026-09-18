import React from 'react';

function ProfileHeroImage(props) {
  const { image, alt } = props;

  return (
    <figure>
      <div className="img-main-wrapper">
        <img id="img-main" src={image.url} alt={alt} />
      </div>
      <figcaption>{image.photographer}</figcaption>
    </figure>
  );
}

export default ProfileHeroImage;
