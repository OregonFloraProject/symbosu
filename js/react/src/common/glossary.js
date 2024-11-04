export function addGlossaryTooltips(text, glossary) {
  // If no glossary is passed in, just ignore
  if (!glossary) return text;

  // Don't try to add glossary entries to numbers
  if (typeof text === 'number') return text;

  // https://stackoverflow.com/questions/57951816/javascript-replace-word-from-string-with-matching-array-key

  // Design a regular expression to search for all glossary words
  // Makes sure that these are full words by searching for word boundaries
  // Includes plurals and -ly ending (e.g. culms matches culm, calluses matches callus, pinnately matches pinnate)
  // Avoids matching words that are within html tags (e.g., style in <p style="">)
  // Negative lookbehind is better for HTML, but doesn't work on IOS yet: (?<!<[^>]*)
  const re = new RegExp(
    '(?:<.*?>)|(\\b(' +
      Object.keys(glossary)
        .map((key) => `${key}`)
        .join('|') +
      ')(es|s|ly)?\\b)',
    'gi',
  );

  // Search the description for glossary matches, and add tooltip html to each one
  // eslint-disable-next-line no-undef
  return DOMPurify.sanitize(text).replace(re, (match, group1, group2) => {
    // If no groups are captured, it's a word in an HTML tag (non-capturing group), so just return it as-is
    if (!group1) return match;

    // Get the glossary term ID from the singular version of the term matched
    const id = glossary[group2.toLowerCase()];

    // if the matched word isn't in the glossary (bad regex match), return it as-is
    if (id === undefined) return match;

    // Make a 3-digit random number, this helps make unique ids for glossary words that are repeated
    const rand = Math.floor(100 + Math.random() * 900);

    // Return the modified html for the glossary word
    return (
      '<span class="glossary" onClick="showTooltip(this, ' +
      id +
      ')" id="glossary' +
      rand +
      id +
      '">' +
      match +
      '</span>'
    );
  });
}
