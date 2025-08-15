// OregonFlora extensions for using SOLR search on collections/map/index

function getCollectionParams(formData) {
  const dbs = formData.getAll('db[]');
  let c = false;
  let all = false;
  let collid = '';
  for (const db of dbs) {
    if (db === 'all') {
      all = true;
    } else if (!isNaN(db)) {
      if (c == true) collid = collid + ' ';
      collid = collid + db;
      c = true;
    }
  }
  if (all == false && c == true) {
    if (collid.substring(collid.length - 1, collid.length) == ',') {
      collid = collid.substring(0, collid.length - 1);
    }
    return {
      cql: ['(collid IN(' + collid + '))'],
      solrq: ['(collid:(' + collid + '))'],
    };
  } else if (all == false && c == false) {
    alert('Please choose at least one collection');
    return { error: true };
  } else {
    return { cql: [], solrq: [] };
  }
}

async function prepareTaxaDataAsync(taxaArr, taxontype, thes) {
  const url = '../../spatial/rpc/gettaxalinks.php';
  const taxaArrStr = JSON.stringify(taxaArr);
  const params =
    'taxajson=' + taxaArrStr + '&type=' + taxontype + '&thes=' + thes;
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-type': 'application/x-www-form-urlencoded' },
    body: params,
  });
  return await response.json();
}

function isFamilyName(taxonString) {
  // if a taxon string ends with 'aceae' or 'idae' and is a single word, assume it's a family name
  // the check for a single word is here to avoid false positives such as Corydalis aquae-gelidae
  return (
    (taxonString.substring(i.length - 5) == 'aceae' ||
      taxonString.substring(i.length - 4) == 'idae') &&
    !taxonString.trim().includes(' ')
  );
}

async function prepareTaxaParamsAsync(formData) {
  const taxaval = formData.get('taxa').trim();
  if (taxaval) {
    const taxavals = taxaval.split(',');
    let taxaCqlString = '';
    let taxaSolrqString = '';
    let taxonNames = [];
    const taxontype = formData.get('taxontype');
    const thes = formData.get('usethes') === '1';
    for (i in taxavals) {
      let name = taxavals[i].trim();
      if (taxontype === '1') {
        // remove search type tag from autofill
        const splitArr = name.split(': ');
        name = splitArr[splitArr.length - 1];
      }
      taxonNames.push(name);
    }
    const taxaArr = await prepareTaxaDataAsync(taxonNames, taxontype, thes);
    if (taxaArr) {
      for (i in taxaArr) {
        if (taxontype == 4) {
          taxaCqlString = ' OR parenttid = ' + i;
          taxaSolrqString = ' OR (parenttid:' + i + ')';
        } else {
          if (taxontype == 5) {
            let famArr = [];
            let scinameArr = [];
            if (taxaArr[i]['families']) {
              famArr = taxaArr[i]['families'];
            }
            if (famArr.length > 0) {
              taxaSolrqString += ' OR (family:(' + famArr.join() + '))';
              for (f in famArr) {
                taxaCqlString += " OR family = '" + famArr[f] + "'";
              }
            }
            if (taxaArr[i]['scinames']) {
              scinameArr = taxaArr[i]['scinames'];
              if (scinameArr.length > 0) {
                for (s in scinameArr) {
                  taxaSolrqString +=
                    ' OR ((sciname:' +
                    scinameArr[s].replace(/ /g, '\\ ') +
                    ') OR (sciname:' +
                    scinameArr[s].replace(/ /g, '\\ ') +
                    '\\ *))';
                  taxaCqlString += " OR sciname LIKE '" + scinameArr[s] + "%'";
                }
              }
            }
          } else {
            // taxontype 1, 2, 3
            if (taxontype == 3 || isFamilyName(i)) {
              taxaSolrqString += ' OR (family:' + i + ')';
              taxaCqlString += " OR family = '" + i + "'";
            }
            if (taxontype == 3 || !isFamilyName(i)) {
              taxaSolrqString +=
                ' OR ((sciname:' +
                i.replace(/ /g, '\\ ') +
                ') OR (sciname:' +
                i.replace(/ /g, '\\ ') +
                '\\ *))';
              taxaCqlString += " OR sciname LIKE '" + i + "%'";
            }
          }
          if (taxaArr[i]['synonyms']) {
            let synArr = taxaArr[i]['synonyms'];
            let tidArr = [];
            if (taxontype == 1 || taxontype == 2 || taxontype == 5) {
              for (syn in synArr) {
                if (isFamilyName(synArr[syn])) {
                  taxaSolrqString += ' OR (family:' + synArr[syn] + ')';
                  taxaCqlString += " OR family = '" + synArr[syn] + "'";
                }
              }
            }
            for (syn in synArr) {
              tidArr.push(syn);
            }
            taxaSolrqString +=
              ' OR (tidinterpreted:(' + tidArr.join(' ') + '))';
            taxaCqlString += ' OR tidinterpreted IN(' + tidArr.join(' ') + ')';
          }
        }
      }
      taxaCqlString = taxaCqlString.substring(4, taxaCqlString.length);
      taxaSolrqString = taxaSolrqString.substring(4, taxaSolrqString.length);
      return {
        cql: ['((' + taxaCqlString + '))'],
        solrq: ['(' + taxaSolrqString + ')'],
      };
    }
  }
  return { cql: [], solrq: [] };
}

function getTextParams(formData) {
  let cqlArr = [];
  let solrqArr = [];
  const countryval = formData.get('country').trim();
  const stateval = formData.get('state').trim();
  const countyval = formData.get('county').trim();
  const localityval = formData.get('local').trim();
  const collectorval = formData.get('collector').trim();
  const collnumval = formData.get('collnum').trim();
  let colldate1 = formData.get('eventdate1').trim();
  let colldate2 = formData.get('eventdate2').trim();
  const catnumval = formData.get('catnum').trim();
  const includeothercatnum = formData.get('includeothercatnum') === '1';
  const typestatus = formData.get('typestatus') === '1';
  const hasimages = formData.get('hasimages') === '1';
  const hasgenetic = formData.get('hasgenetic') === '1';
  const includecult = formData.get('includecult') === '1';
  const excludeinat = formData.get('excludeinat') === '1';

  if (countryval) {
    const countryvals = countryval.split(',');
    let countryCqlString = '';
    let countrySolrqString = '';
    for (i = 0; i < countryvals.length; i++) {
      if (countryCqlString) countryCqlString += ' OR ';
      if (countrySolrqString) countrySolrqString += ' OR ';
      countryCqlString += "(country = '" + countryvals[i] + "')";
      countrySolrqString += '(country:"' + countryvals[i] + '")';
    }
    cqlArr.push('(' + countryCqlString + ')');
    solrqArr.push('(' + countrySolrqString + ')');
  }
  if (stateval) {
    const statevals = stateval.split(',');
    let stateCqlString = '';
    let stateSolrqString = '';
    for (i = 0; i < statevals.length; i++) {
      if (stateCqlString) stateCqlString += ' OR ';
      if (stateSolrqString) stateSolrqString += ' OR ';
      stateCqlString += "(StateProvince = '" + statevals[i] + "')";
      stateSolrqString += '(StateProvince:"' + statevals[i] + '")';
    }
    cqlArr.push('(' + stateCqlString + ')');
    solrqArr.push('(' + stateSolrqString + ')');
  }
  if (countyval) {
    const countyvals = countyval.split(',');
    let countyCqlString = '';
    let countySolrqString = '';
    for (i = 0; i < countyvals.length; i++) {
      if (countyCqlString) countyCqlString += ' OR ';
      if (countySolrqString) countySolrqString += ' OR ';
      countyCqlString += "(county LIKE '" + countyvals[i] + "%')";
      countySolrqString +=
        '(county:' + countyvals[i].replace(' ', '\\ ') + '*)';
    }
    cqlArr.push('(' + countyCqlString + ')');
    solrqArr.push('(' + countySolrqString + ')');
  }
  if (localityval) {
    const localityvals = localityval.split(',');
    let localityCqlString = '';
    let localitySolrqString = '';
    for (i = 0; i < localityvals.length; i++) {
      if (localityCqlString) localityCqlString += ' OR ';
      if (localitySolrqString) localitySolrqString += ' OR ';
      localityCqlString += '(';
      localitySolrqString += '(';
      if (localityvals[i].indexOf(' ') !== -1) {
        let templocalityCqlString = '';
        let templocalitySolrqString = '';
        const vals = localityvals[i].split(' ');
        for (i = 0; i < vals.length; i++) {
          if (templocalityCqlString) templocalityCqlString += ' AND ';
          if (templocalitySolrqString) templocalitySolrqString += ' AND ';
          templocalityCqlString += "locality LIKE '%" + vals[i] + "%'";
          templocalitySolrqString +=
            '((municipality:' + vals[i] + '*) OR (locality:*' + vals[i] + '*))';
        }
        localityCqlString += templocalityCqlString;
        localitySolrqString += templocalitySolrqString;
      } else {
        localityCqlString += "locality LIKE '%" + localityvals[i] + "%'";
        localitySolrqString += '(locality:*' + localityvals[i] + '*)';
      }
      localityCqlString += ')';
      localitySolrqString += ')';
    }
    cqlArr.push('(' + localityCqlString + ')');
    solrqArr.push('(' + localitySolrqString + ')');
  }
  if (collectorval) {
    const collectorvals = collectorval.split(',');
    let collectorCqlString = '';
    let collectorSolrqString = '';
    if (collectorvals.length == 1) {
      collectorCqlString = "(recordedBy LIKE '%" + collectorvals[0] + "%')";
      collectorSolrqString =
        '(recordedBy:*' + collectorvals[0].replace(' ', '\\ ') + '*)';
    } else if (collectorvals.length > 1) {
      for (i in collectorvals) {
        collectorCqlString +=
          " OR (recordedBy LIKE '%" + collectorvals[i] + "%')";
        collectorSolrqString +=
          ' OR (recordedBy:*' + collectorvals[i].replace(' ', '\\ ') + '*)';
      }
      collectorCqlString = collectorCqlString.substring(
        4,
        collectorCqlString.length
      );
      collectorSolrqString = collectorSolrqString.substring(
        4,
        collectorSolrqString.length
      );
    }
    cqlArr.push('(' + collectorCqlString + ')');
    solrqArr.push('(' + collectorSolrqString + ')');
  }
  if (collnumval) {
    const collnumvals = collnumval.split(',');
    let collnumCqlString = '';
    let collnumSolrqString = '';
    for (i in collnumvals) {
      if (collnumvals[i].indexOf(' - ') !== -1) {
        const pos = collnumvals[i].indexOf(' - ');
        const t1 = collnumvals[i].substring(0, pos).trim();
        const t2 = collnumvals[i]
          .substring(pos + 3, collnumvals[i].length)
          .trim();
        if (!isNaN(t1) && !isNaN(t2)) {
          collnumCqlString +=
            ' OR (recordNumber BETWEEN ' + t1 + ' AND ' + t2 + ')';
          collnumSolrqString += ' OR (recordNumber:[' + t1 + ' TO ' + t2 + '])';
        } else {
          collnumCqlString +=
            " OR (recordNumber BETWEEN '" + t1 + "' AND '" + t2 + "')";
          collnumSolrqString +=
            " OR (recordNumber:['" + t1 + "' TO '" + t2 + "'])";
        }
      } else {
        collnumCqlString += " OR (recordNumber = '" + collnumvals[i] + "')";
        collnumSolrqString += ' OR (recordNumber:"' + collnumvals[i] + '")';
      }
    }
    collnumCqlString = collnumCqlString.substring(4, collnumCqlString.length);
    collnumSolrqString = collnumSolrqString.substring(
      4,
      collnumSolrqString.length
    );
    cqlArr.push('(' + collnumCqlString + ')');
    solrqArr.push('(' + collnumSolrqString + ')');
  }
  if (colldate1 || colldate2) {
    let colldateCqlString = '';
    let colldateSolrqString = '';
    if (!colldate1 && colldate2) {
      colldate1 = colldate2;
      colldate2 = '';
    }
    colldate1 = formatCheckDate(colldate1);
    if (colldate2) {
      colldate2 = formatCheckDate(colldate2);
    }
    if (colldate2) {
      colldateCqlString +=
        "(eventDate BETWEEN '" + colldate1 + "' AND '" + colldate2 + "')";
      colldateSolrqString +=
        '(eventDate:[' +
        colldate1 +
        'T00:00:00Z TO ' +
        colldate2 +
        'T23:59:59.999Z])';
    } else {
      if (
        colldate1.substring(colldate1.length - 5, colldate1.length) == '00-00'
      ) {
        colldateCqlString += '(coll_year = ' + colldate1.substring(0, 4) + ')';
        colldateSolrqString += '(coll_year:' + colldate1.substring(0, 4) + ')';
      } else if (
        colldate1.substring(colldate1.length - 2, colldate1.length) == '00'
      ) {
        colldateCqlString +=
          '((coll_year = ' +
          colldate1.substring(0, 4) +
          ') AND (coll_month = ' +
          colldate1.substring(5, 7) +
          '))';
        colldateSolrqString +=
          '((coll_year:' +
          colldate1.substring(0, 4) +
          ') AND (coll_month:' +
          colldate1.substring(5, 7) +
          '))';
      } else {
        colldateCqlString += "(eventDate = '" + colldate1 + "')";
        colldateSolrqString +=
          '(eventDate:[' +
          colldate1 +
          'T00:00:00Z TO ' +
          colldate1 +
          'T23:59:59.999Z])';
      }
    }
    cqlArr.push(colldateCqlString);
    solrqArr.push('(' + colldateSolrqString + ')');
  }
  if (catnumval) {
    const catnumvals = catnumval.split(',');
    let catnumCqlString = '';
    let catnumSolrqString = '';
    for (i = 0; i < catnumvals.length; i++) {
      if (catnumCqlString) catnumCqlString += ' OR ';
      if (catnumSolrqString) catnumSolrqString += ' OR ';
      catnumCqlString += "(catalogNumber = '" + catnumvals[i] + "')";
      catnumSolrqString += '(catalogNumber:"' + catnumvals[i] + '")';
      if (includeothercatnum) {
        catnumCqlString +=
          " OR (otherCatalogNumbers = '" + catnumvals[i] + "')";
        catnumSolrqString +=
          ' OR (otherCatalogNumbers:"' + catnumvals[i] + '")';
      }
    }
    cqlArr.push('(' + catnumCqlString + ')');
    solrqArr.push('(' + catnumSolrqString + ')');
  }
  if (typestatus) {
    cqlArr.push("(typeStatus LIKE '_%')");
    solrqArr.push('((typeStatus:[* TO *]))');
  }
  if (hasimages) {
    cqlArr.push("(imgid LIKE '_%')");
    solrqArr.push('((imgid:[* TO *]))');
  }
  if (hasgenetic) {
    cqlArr.push("(resourcename LIKE '_%')");
    solrqArr.push('((resourcename:[* TO *]))');
  }
  if (!includecult) {
    cultivationStatus:0
    cqlArr.push("(cultivationStatus != 1)");
    solrqArr.push('NOT (cultivationStatus:1)');
  }
  if (excludeinat) {
    cqlArr.push("(relationship IS NULL)");
    solrqArr.push('NOT ((relationship:"iNaturalistObservation") AND NOT (CollType:"Preserved Specimens"))');
  }
  return { cql: cqlArr, solrq: solrqArr };
}

function getGeographyParams(formData) {
  let solrgeoqArr = [];
  const polygon = formData.get('polycoords');
  if (polygon) {
    // SOLR expects the coordinates in long-lat format, whereas the rest of the site uses lat-long
    // so we reverse the coordinates here and reconstruct the string
    const polygonLatLngs = polygon.slice(10, -2);
    const polygonLngLats = polygonLatLngs
      .split(',')
      .map((latLng) => latLng.split(' ').reverse().join(' '))
      .join(',');

    solrgeoqArr.push('"Intersects(POLYGON ((' + polygonLngLats + ')))"');
  }
  // circle
  const pointlat = formData.get('pointlat');
  const pointlong = formData.get('pointlong');
  let radius = formData.get('radius');
  const unit = formData.get('pointunits');
  if (unit === 'mi') {
    radius = radius / 0.6214;
  }
  if (pointlat !== '' && pointlong !== '' && radius) {
    solrgeoqArr.push(
      '{!geofilt sfield=geo pt=' +
        pointlat +
        ',' +
        pointlong +
        ' d=' +
        radius +
        '}'
    );
  }
  // rectangle
  const upperlat = formData.get('upperlat');
  const rightlong = formData.get('rightlong');
  const bottomlat = formData.get('bottomlat');
  const leftlong = formData.get('leftlong');
  if (
    upperlat !== '' &&
    rightlong !== '' &&
    bottomlat !== '' &&
    leftlong !== ''
  ) {
    solrgeoqArr.push(
      `"Intersects(POLYGON((${leftlong} ${upperlat},${rightlong} ${upperlat},${rightlong} ${bottomlat},${leftlong} ${bottomlat},${leftlong} ${upperlat})))"`
    );
  }
  return { solrgeoqArr };
}

function parseDate(dateStr) {
  let y = 0;
  let m = 0;
  let d = 0;
  try {
    const validformat1 = /^\d{4}-\d{1,2}-\d{1,2}$/; //Format: yyyy-mm-dd
    const validformat2 = /^\d{1,2}\/\d{1,2}\/\d{2,4}$/; //Format: mm/dd/yyyy
    const validformat3 = /^\d{1,2} \D+ \d{2,4}$/; //Format: dd mmm yyyy
    if (validformat1.test(dateStr)) {
      const dateTokens = dateStr.split('-');
      y = dateTokens[0];
      m = dateTokens[1];
      d = dateTokens[2];
    } else if (validformat2.test(dateStr)) {
      const dateTokens = dateStr.split('/');
      m = dateTokens[0];
      d = dateTokens[1];
      y = dateTokens[2];
      if (y.length == 2) {
        if (y < 20) {
          y = '20' + y;
        } else {
          y = '19' + y;
        }
      }
    } else if (validformat3.test(dateStr)) {
      const dateTokens = dateStr.split(' ');
      d = dateTokens[0];
      mText = dateTokens[1];
      y = dateTokens[2];
      if (y.length == 2) {
        if (y < 15) {
          y = '20' + y;
        } else {
          y = '19' + y;
        }
      }
      mText = mText.substring(0, 3);
      mText = mText.toLowerCase();
      const mNames = new Array(
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'dec'
      );
      m = mNames.indexOf(mText) + 1;
    } else if (dateObj instanceof Date && dateObj != 'Invalid Date') {
      const dateObj = new Date(dateStr);
      y = dateObj.getFullYear();
      m = dateObj.getMonth() + 1;
      d = dateObj.getDate();
    }
  } catch (ex) {}
  let retArr = [];
  retArr['y'] = y.toString();
  retArr['m'] = m.toString();
  retArr['d'] = d.toString();
  return retArr;
}

function formatCheckDate(dateStr) {
  if (dateStr != '') {
    let dateArr = parseDate(dateStr);
    if (dateArr['y'] == 0) {
      alert(
        'Please use the following date formats: yyyy-mm-dd, mm/dd/yyyy, or dd mmm yyyy'
      );
      return false;
    } else {
      //Invalid format is month > 12
      if (dateArr['m'] > 12) {
        alert(
          'Month cannot be greater than 12. Note that the format should be YYYY-MM-DD'
        );
        return false;
      }

      //Check to see if day is valid
      if (dateArr['d'] > 28) {
        if (
          dateArr['d'] > 31 ||
          (dateArr['d'] == 30 && dateArr['m'] == 2) ||
          (dateArr['d'] == 31 &&
            (dateArr['m'] == 4 ||
              dateArr['m'] == 6 ||
              dateArr['m'] == 9 ||
              dateArr['m'] == 11))
        ) {
          alert('The Day (' + dateArr['d'] + ') is invalid for that month');
          return false;
        }
      }

      //Enter date into date fields
      let mStr = dateArr['m'];
      if (mStr.length == 1) {
        mStr = '0' + mStr;
      }
      let dStr = dateArr['d'];
      if (dStr.length == 1) {
        dStr = '0' + dStr;
      }
      return dateArr['y'] + '-' + mStr + '-' + dStr;
    }
  }
}

async function buildSOLRQString(formData) {
  const collParams = getCollectionParams(formData);
  const taxaParams = await prepareTaxaParamsAsync(formData);
  const textParams = getTextParams(formData);
  const geoParams = getGeographyParams(formData);

  if (
    collParams.error ||
    taxaParams.error ||
    textParams.error ||
    geoParams.error
  ) {
    return; // alert already shown
  }

  const solrqArr = collParams.solrq.concat(taxaParams.solrq, textParams.solrq);
  const { solrgeoqArr } = geoParams;

  if (!solrqArr.length && !solrgeoqArr.length) {
    return; // alert already shown
  }

  let newsolrqString = 'q=';
  let tempqStr = '';
  let tempfqStr = '';
  if (solrqArr.length > 0) {
    for (i in solrqArr) {
      tempqStr += ' AND ' + solrqArr[i];
    }
    tempqStr = tempqStr.substring(5, tempqStr.length);
    tempqStr +=
      ' AND (decimalLatitude:[* TO *] AND decimalLongitude:[* TO *] AND sciname:[* TO *])';
    newsolrqString += tempqStr;
  } else {
    newsolrqString += '(sciname:[* TO *])';
  }

  if (solrgeoqArr.length > 0) {
    for (i in solrgeoqArr) {
      tempfqStr += ' OR geo:' + solrgeoqArr[i];
    }
    tempfqStr = tempfqStr.substring(4, tempfqStr.length);
    newsolrqString += '&fq=' + tempfqStr;
  }
  return newsolrqString;
}

async function getRecordCountFromSOLR(solrqString) {
  const response = await fetch('../../spatial/rpc/SOLRConnector.php', {
    method: 'POST',
    headers: { 'Content-type': 'application/x-www-form-urlencoded' },
    body: solrqString + '&rows=0&start=0&wt=json&action=getsolrreccnt',
  });
  const data = await response.json();
  return {
    recordCount: data.response.numFound,
    hiddenFound: data.response.hiddenFound,
  };
}

const SOLR_FIELDS =
  'occid,collid,catalogNumber,family,sciname,tidinterpreted,recordedBy,recordNumber,eventDate,' +
  'geo,CollectionName,CollType';
const MAX_RECORD_COUNT = 20000;

async function loadPointsFromSOLR(solrqString, recordCount, host) {
  const response = await fetch('../../spatial/rpc/SOLRConnector.php', {
    method: 'POST',
    headers: { 'Content-type': 'application/x-www-form-urlencoded' },
    body: `${solrqString}&rows=${Math.min(
      recordCount,
      MAX_RECORD_COUNT
    )}&start=0&fl=${SOLR_FIELDS}&wt=geojson&action=lazyload`,
  });
  const data = await response.json();
  return convertSOLRResponse(data, host);
}

const SOLR_TYPE_TO_SYMBIOTA_TYPE = {
  ['Observations']: 'observation',
  ['Preserved Specimens']: 'specimen',
};

function convertSOLRResponse(res, host) {
  const { features } = res;
  const taxaArr = {};
  const collArr = {};
  const recordArr = features.map(({ geometry, properties }) => {
    if (!(properties.tidinterpreted in taxaArr)) {
      taxaArr[properties.tidinterpreted] = {
        sn: properties.sciname,
        tid: properties.tidinterpreted,
        family: properties.family?.toUpperCase(),
        color: 'e69e67',
      };
    }
    if (!(properties.collid in collArr)) {
      collArr[properties.collid] = {
        name: properties.CollectionName,
        collid: properties.collid,
        color: 'e69e67',
      };
    }

    return {
      collid: properties.collid,
      collname: properties.CollectionName,
      family: properties.family?.toUpperCase(),
      host,
      id: `${properties.recordedBy ?? ''}${
        properties.recordedBy && properties.recordNumber ? ' ' : ''
      }${properties.recordNumber ?? ''}`,
      lat: geometry.coordinates[1],
      lng: geometry.coordinates[0],
      occid: properties.occid,
      tid: `${properties.tidinterpreted}`,
      type: SOLR_TYPE_TO_SYMBIOTA_TYPE[properties.CollType],
      catnum: properties.catalogNumber,
      eventdate: properties.eventDate?.substring(
        0,
        properties.eventDate.indexOf('T')
      ),
      sciname: properties.sciname,
    };
  });

  return {
    taxaArr,
    collArr,
    recordArr,
    origin: host,
  };
}

function renderOccurrenceRows(html, searchData, params) {
  const page = params.get('page') ?? 1;
  const countPerPage = params.get('cntperpage') ?? 100;
  const firstIndex = (page - 1) * countPerPage;

  let rowsHtml = '';
  for (let i = 0; i < countPerPage; i++) {
    const recordIndex = firstIndex + i;
    if (!(recordIndex in searchData.recordArr)) {
      break;
    }
    const { occid, host, sciname, eventdate, catnum, id } =
      searchData.recordArr[recordIndex];
    rowsHtml += `<tr ${i % 2 ? 'class="alt"' : ''} id="tr${occid}">`;
    rowsHtml += `<td id="cat${occid}" >${catnum ?? ''}</td>`;
    rowsHtml += `<td id="label${occid}" >`;
    rowsHtml += `<a href="#" onclick="openRecord({occid:${occid}, host:\'${host}\'}); return false;">${
      id ? id : 'Not available'
    }</a>`;
    rowsHtml += `</td>`;
    rowsHtml += `<td id="e${occid}" >${eventdate ?? ''}</td>`;
    rowsHtml += `<td id="s${occid}" >${sciname}</td>`;
    rowsHtml += `<td id="li${occid}" ><a href="#occid=${occid}" onclick="emit_occurrence_click(${occid})">See map point</a></td>`;
    rowsHtml += `</tr>`;
  }
  return html.replace('{{ROWS_PLACEHOLDER}}', rowsHtml);
}
