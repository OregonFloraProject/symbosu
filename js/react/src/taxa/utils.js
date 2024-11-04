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
