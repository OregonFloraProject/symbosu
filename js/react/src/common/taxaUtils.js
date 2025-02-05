export function getTaxaPage(clientRoot, tid) {
  return `${clientRoot}/taxa/index.php?taxon=${tid}`;
}

export function getRareTaxaPage(clientRoot, tid) {
  return `${clientRoot}/taxa/rare.php?taxon=${tid}`;
}

export function getGardenTaxaPage(clientRoot, tid) {
  return `${clientRoot}/taxa/garden.php?taxon=${tid}`;
}

export function getGardenPage(clientRoot, clid) {
  return `${clientRoot}/garden/index.php?clid=${clid}`;
}

export function getImageDetailPage(clientRoot, occid) {
  return `${clientRoot}/collections/individual/index.php?occid=${occid}`;
}

export function getCommonNameStr(item) {
  const basename = item.vernacular.basename;
  const names = item.vernacular.names;

  let cname = basename;
  if (names.length > 0) {
    cname = names[0];
  }

  if (basename && cname.includes(basename) && basename !== cname) {
    return `${basename}, ${cname.replace(basename, '')}`;
  }

  return cname;
}

export function sortByTaxon(taxa, sortBy) {
  if (sortBy === 'sciName') {
    return taxa.sort((a, b) => {
      return a['sciname'] > b['sciname'] ? 1 : -1;
    });
  }
  if (sortBy === 'vernacularName') {
    return taxa.sort((a, b) => {
      return getCommonNameStr(a).toLowerCase() > getCommonNameStr(b).toLowerCase() ? 1 : -1;
    });
  } else {
    throw new Error(`sortByTaxon: invalid sortBy value '${sortBy}'`);
  }
}

export function getChecklistPage(clientRoot, clid, pid, dynclid) {
  if (clid < 0 && dynclid > -1) {
    return `${clientRoot}/checklists/checklist.php?dynclid=${dynclid}`;
  }
  return `${clientRoot}/checklists/checklist.php?cl=${clid}&pid=${pid}`;
}

export function getIdentifyPage(clientRoot, clid, pid, dynclid) {
  if (clid < 0 && dynclid > -1) {
    return `${clientRoot}/ident/key.php?dynclid=${dynclid}&taxon=All+Species`;
  }
  return `${clientRoot}/ident/key.php?cl=${clid}&proj=${pid}&taxon=All+Species`;
}
