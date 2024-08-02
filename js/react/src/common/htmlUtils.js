import React from 'react';

export function stripHtml(str) {
	/*
  Description includes HTML tags & URL-encoded characters in the db.
  It's dangerous to pull/render arbitrary HTML w/ react, so just render the
  plain text & remove any HTML in it.
  */
  return str.replace(/(<\/?[^>]+>)|(&[^;]+;)/g, "");
}

export function renderBasicHtml(str) {
  const segments = str.split(/(<[^>]+>)/g);
  const elements = [];
  for (let i = 0; i < segments.length; i++) {
    const segment = segments[i];
    if (segment.toLowerCase() === '<i>') {
      let j = i + 1;
      const innerElements = [];
      while (j < segments.length && segments[j].toLowerCase() !== '</i>') {
        innerElements.push(stripHtml(segments[j]));
        j++;
      }
      if (j < segments.length) {
        elements.push(<i>{innerElements}</i>);
        i = j;
      }
    } else if (segment.match(/^<br\s*\/*>$/i)) {
      elements.push(<br />);
    } else {
      elements.push(stripHtml(segment));
    }
  }
  // create the React element manually instead of just returning the array, to avoid needing to put
  // a basically useless (in this case) key on every element in the array
  // https://github.com/facebook/react/issues/12567#issuecomment-414061454
  return React.createElement(React.Fragment, {}, ...elements);
}
