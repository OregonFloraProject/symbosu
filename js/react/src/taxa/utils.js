/**
 * Since cs values are stored as strings in the database, they do not always come properly sorted.
 * For chars where the storing matters (numeric values and months), the API will give us an object
 * with cs as keys and charstatename as values. Here we convert this into a correctly ordered array.
 */
export function sortKeyedCharObject(obj) {
  // sort entries by key (cs), then map to an array of just the values (charstatename)
  return Object.entries(obj)
    .sort((a, b) => a[0] - b[0])
    .map((entry) => entry[1]);
}

/**
 * Turns an array/object of char state names into a single string containing just the extremities
 * e.g. ['dry', 'moist', 'wet'] => 'dry to wet'
 */
export function csRangeToString(obj, separator = 'to') {
  // sort entries by key (cs), then map to an array of just the values (charstatename)
  const arr = sortKeyedCharObject(obj);
  if (arr.length < 1) {
    return '';
  } else if (arr.length === 1) {
    return arr[0];
  }
  return `${arr[0]} ${separator} ${arr[arr.length - 1]}`;
}

export function checkNullThumbnailUrl(imageArray, noThumbnailJpgUrl) {
  const imageCount = imageArray.length;
  for (let i = 0; i < imageCount; i++) {
    const element = imageArray[i];
    if (!element.thumbnailurl) element.thumbnailurl = noThumbnailJpgUrl;
  }
}