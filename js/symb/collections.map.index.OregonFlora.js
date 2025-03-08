// OregonFlora extensions for using SOLR search on collections/map/index

let cqlArr = [];
let solrqArr = [];
let solrgeoqArr = [];

function getCollectionParams(formData) {
  var dbs = formData.getAll('db[]');
  var c = false;
  var all = false;
  var collid = '';
  var cqlfrag = '';
  var solrqfrag = '';
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
    if (collid.substr(collid.length - 1, collid.length) == ',') {
      collid = collid.substr(0, collid.length - 1);
    }
    cqlfrag = '(collid IN(' + collid + '))';
    cqlArr.push(cqlfrag);
    solrqfrag = '(collid:(' + collid + '))';
    solrqArr.push(solrqfrag);
    return true;
  } else if (all == false && c == false) {
    alert('Please choose at least one collection');
    return false;
  } else {
    return true;
  }
}

async function prepareTaxaDataAsync(taxaArr) {
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

async function prepareTaxaParamsAsync(formData) {
  var taxaval = formData.get('taxa').trim();
  if (taxaval) {
    var taxavals = taxaval.split(',');
    var taxaCqlString = '';
    var taxaSolrqString = '';
    taxaArr = [];
    taxontype = formData.get('taxontype');
    thes = formData.get('usethes') === '1';
    for (i in taxavals) {
      var name = taxavals[i].trim();
      if (taxontype === '1') {
        // remove search type tag from autofill
        const splitArr = name.split(': ');
        name = splitArr[splitArr.length - 1];
      }
      taxaArr.push(name);
    }
    taxaArr = await prepareTaxaDataAsync(taxaArr);
    if (taxaArr) {
      var taxaCqlString = '';
      var taxaSolrqString = '';
      for (i in taxaArr) {
        if (taxontype == 4) {
          taxaCqlString = ' OR parenttid = ' + i;
          taxaSolrqString = ' OR (parenttid:' + i + ')';
        } else {
          if (taxontype == 5) {
            var famArr = [];
            var scinameArr = [];
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
            if (
              (taxontype == 2 || taxontype == 1) &&
              (i.substr(i.length - 5) == 'aceae' ||
                i.substr(i.length - 4) == 'idae')
            ) {
              taxaSolrqString += ' OR (family:' + i + ')';
              taxaCqlString += " OR family = '" + i + "'";
            }
            if (
              (taxontype == 3 || taxontype == 1) &&
              (i.substr(i.length - 5) != 'aceae' ||
                i.substr(i.length - 4) != 'idae')
            ) {
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
            var synArr = [];
            synArr = taxaArr[i]['synonyms'];
            var tidArr = [];
            if (taxontype == 1 || taxontype == 2 || taxontype == 5) {
              for (syn in synArr) {
                if (
                  synArr[syn].indexOf('aceae') !== -1 ||
                  synArr[syn].indexOf('idae') !== -1
                ) {
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
      taxaCqlString = taxaCqlString.substr(4, taxaCqlString.length);
      taxaSolrqString = taxaSolrqString.substr(4, taxaSolrqString.length);
      cqlfrag = '((' + taxaCqlString + '))';
      cqlArr.push(cqlfrag);
      solrqfrag = '(' + taxaSolrqString + ')';
      solrqArr.push(solrqfrag);
    }
  }
}

function getTextParams(formData) {
  var cqlfrag = '';
  var solrqfrag = '';
  var countryval = formData.get('country').trim();
  var stateval = formData.get('state').trim();
  var countyval = formData.get('county').trim();
  var localityval = formData.get('local').trim();
  var collectorval = formData.get('collector').trim();
  var collnumval = formData.get('collnum').trim();
  var colldate1 = formData.get('eventdate1').trim();
  var colldate2 = formData.get('eventdate2').trim();
  var catnumval = formData.get('catnum').trim();
  // var othercatnumval = formData.get('includeothercatnum') === '1'; // TODO: figure these out
  var typestatus = formData.get('typestatus') === '1';
  var hasimages = formData.get('hasimages') === '1';
  var hasgenetic = formData.get('hasgenetic') === '1';
  // var includecult = formData.get('includecult') === '1'; // TODO: add this capability

  if (countryval) {
    var countryvals = countryval.split(',');
    var countryCqlString = '';
    var countrySolrqString = '';
    for (i = 0; i < countryvals.length; i++) {
      if (countryCqlString) countryCqlString += ' OR ';
      if (countrySolrqString) countrySolrqString += ' OR ';
      countryCqlString += "(country = '" + countryvals[i] + "')";
      countrySolrqString += '(country:"' + countryvals[i] + '")';
    }
    cqlfrag = '(' + countryCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + countrySolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (stateval) {
    var statevals = stateval.split(',');
    var stateCqlString = '';
    var stateSolrqString = '';
    for (i = 0; i < statevals.length; i++) {
      if (stateCqlString) stateCqlString += ' OR ';
      if (stateSolrqString) stateSolrqString += ' OR ';
      stateCqlString += "(StateProvince = '" + statevals[i] + "')";
      stateSolrqString += '(StateProvince:"' + statevals[i] + '")';
    }
    cqlfrag = '(' + stateCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + stateSolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (countyval) {
    var countyvals = countyval.split(',');
    var countyCqlString = '';
    var countySolrqString = '';
    for (i = 0; i < countyvals.length; i++) {
      if (countyCqlString) countyCqlString += ' OR ';
      if (countySolrqString) countySolrqString += ' OR ';
      countyCqlString += "(county LIKE '" + countyvals[i] + "%')";
      countySolrqString +=
        '(county:' + countyvals[i].replace(' ', '\\ ') + '*)';
    }
    cqlfrag = '(' + countyCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + countySolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (localityval) {
    var localityvals = localityval.split(',');
    var localityCqlString = '';
    var localitySolrqString = '';
    for (i = 0; i < localityvals.length; i++) {
      if (localityCqlString) localityCqlString += ' OR ';
      if (localitySolrqString) localitySolrqString += ' OR ';
      localityCqlString += '(';
      localitySolrqString += '(';
      if (localityvals[i].indexOf(' ') !== -1) {
        var templocalityCqlString = '';
        var templocalitySolrqString = '';
        var vals = localityvals[i].split(' ');
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
    cqlfrag = '(' + localityCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + localitySolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (collectorval) {
    var collectorvals = collectorval.split(',');
    var collectorCqlString = '';
    var collectorSolrqString = '';
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
      collectorCqlString = collectorCqlString.substr(
        4,
        collectorCqlString.length
      );
      collectorSolrqString = collectorSolrqString.substr(
        4,
        collectorSolrqString.length
      );
    }
    cqlfrag = '(' + collectorCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + collectorSolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (collnumval) {
    var collnumvals = collnumval.split(',');
    var collnumCqlString = '';
    var collnumSolrqString = '';
    for (i in collnumvals) {
      if (collnumvals[i].indexOf(' - ') !== -1) {
        var pos = collnumvals[i].indexOf(' - ');
        var t1 = collnumvals[i].substr(0, pos).trim();
        var t2 = collnumvals[i].substr(pos + 3, collnumvals[i].length).trim();
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
    collnumCqlString = collnumCqlString.substr(4, collnumCqlString.length);
    collnumSolrqString = collnumSolrqString.substr(
      4,
      collnumSolrqString.length
    );
    cqlfrag = '(' + collnumCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + collnumSolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (colldate1 || colldate2) {
    var colldateCqlString = '';
    var colldateSolrqString = '';
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
      if (colldate1.substr(colldate1.length - 5, colldate1.length) == '00-00') {
        colldateCqlString += '(coll_year = ' + colldate1.substr(0, 4) + ')';
        colldateSolrqString += '(coll_year:' + colldate1.substr(0, 4) + ')';
      } else if (
        colldate1.substr(colldate1.length - 2, colldate1.length) == '00'
      ) {
        colldateCqlString +=
          '((coll_year = ' +
          colldate1.substr(0, 4) +
          ') AND (coll_month = ' +
          colldate1.substr(5, 7) +
          '))';
        colldateSolrqString +=
          '((coll_year:' +
          colldate1.substr(0, 4) +
          ') AND (coll_month:' +
          colldate1.substr(5, 7) +
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
    cqlfrag = colldateCqlString;
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + colldateSolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (catnumval) {
    var catnumvals = catnumval.split(',');
    var catnumCqlString = '';
    var catnumSolrqString = '';
    for (i = 0; i < catnumvals.length; i++) {
      if (catnumCqlString) catnumCqlString += ' OR ';
      if (catnumSolrqString) catnumSolrqString += ' OR ';
      catnumCqlString += "(catalogNumber = '" + catnumvals[i] + "')";
      catnumSolrqString += '(catalogNumber:"' + catnumvals[i] + '")';
    }
    cqlfrag = '(' + catnumCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + catnumSolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  // if (othercatnumval) {
  //   var othercatnumvals = othercatnumval.split(',');
  //   var othercatnumCqlString = '';
  //   var othercatnumSolrqString = '';
  //   for (i = 0; i < othercatnumvals.length; i++) {
  //     if (othercatnumCqlString) othercatnumCqlString += ' OR ';
  //     if (othercatnumSolrqString) othercatnumSolrqString += ' OR ';
  //     othercatnumCqlString +=
  //       "(otherCatalogNumbers = '" + othercatnumvals[i] + "')";
  //     othercatnumSolrqString +=
  //       '(otherCatalogNumbers:"' + othercatnumvals[i] + '")';
  //   }
  //   cqlfrag = '(' + othercatnumCqlString + ')';
  //   cqlArr.push(cqlfrag);
  //   solrqfrag = '(' + othercatnumSolrqString + ')';
  //   solrqArr.push(solrqfrag);
  // }
  if (typestatus) {
    var typestatusCqlString = "typeStatus LIKE '_%'";
    var typestatusSolrqString = '(typeStatus:[* TO *])';
    cqlfrag = '(' + typestatusCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + typestatusSolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (hasimages) {
    var hasimagesCqlString = "imgid LIKE '_%'";
    var hasimagesSolrqString = '(imgid:[* TO *])';
    cqlfrag = '(' + hasimagesCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + hasimagesSolrqString + ')';
    solrqArr.push(solrqfrag);
  }
  if (hasgenetic) {
    var hasgeneticCqlString = "resourcename LIKE '_%'";
    var hasgeneticSolrqString = '(resourcename:[* TO *])';
    cqlfrag = '(' + hasgeneticCqlString + ')';
    cqlArr.push(cqlfrag);
    solrqfrag = '(' + hasgeneticSolrqString + ')';
    solrqArr.push(solrqfrag);
  }
}

function getGeographyParams(formData) {
  const polygon = formData.get('polycoords');
  if (polygon) {
    solrgeoqArr.push('"Intersects(' + polygon + ')"');
  }
  // circle
  const pointlat = formData.get('pointlat');
  const pointlong = formData.get('pointlong');
  const radius = formData.get('radius');
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
}

function buildSOLRQString() {
  var newsolrqString = 'q=';
  var tempqStr = '';
  var tempfqStr = '';
  if (solrqArr.length > 0) {
    for (i in solrqArr) {
      tempqStr += ' AND ' + solrqArr[i];
    }
    tempqStr = tempqStr.substr(5, tempqStr.length);
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
    tempfqStr = tempfqStr.substr(4, tempfqStr.length);
    newsolrqString += '&fq=' + tempfqStr;
  }
  return newsolrqString;
}

const lazyLoadCount = 20000;
const SOLRFields =
  'occid,collid,catalogNumber,otherCatalogNumbers,family,sciname,tidinterpreted,scientificNameAuthorship,identifiedBy,' +
  'dateIdentified,typeStatus,recordedBy,recordNumber,eventDate,displayDate,coll_year,coll_month,coll_day,habitat,associatedTaxa,' +
  'cultivationStatus,country,StateProvince,county,municipality,locality,localitySecurity,localitySecurityReason,geo,minimumElevationInMeters,' +
  'maximumElevationInMeters,labelProject,InstitutionCode,CollectionCode,CollectionName,CollType,thumbnailurl,accFamily';
async function lazyLoadPoints(solrqString, index) {
  var startindex = 0;
  loadingComplete = true;
  if (index > 1) startindex = (index - 1) * lazyLoadCount;
  var url = '../../spatial/rpc/SOLRConnector.php';
  var params =
    solrqString +
    '&rows=' +
    lazyLoadCount +
    '&start=' +
    startindex +
    '&fl=' +
    SOLRFields +
    '&wt=geojson&action=lazyload';
  console.log('lazy ' + url + ' ' + params);
  const promise = fetch(url, {
    method: 'POST',
    headers: { 'Content-type': 'application/x-www-form-urlencoded' },
    body: params,
  });
  // loadingComplete = false;
  // setTimeout(checkLoading, loadingTimer);
  const response = await promise;
  return await response.json();
}

function convertSOLRResponse(res) {
  const { features } = res;
  const taxaArr = {};
  const collArr = {};
  const recordArr = features.map(({ geometry, properties }) => {
    if (!(properties.tidinterpreted in taxaArr)) {
      taxaArr[properties.tidinterpreted] = {
        sn: properties.sciname,
        tid: properties.tidinterpreted,
        family: properties.family.toUpperCase(), // TODO: accFamily?
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
      family: properties.family.toUpperCase(), // TODO: accFamily?
      host: '', // TODO: get
      id: `${properties.recordedBy} ${properties.recordNumber}`,
      lat: geometry.coordinates[1],
      lng: geometry.coordinates[0],
      occid: properties.occid,
      tid: `${properties.tidinterpreted}`,
      type: properties.CollType, // not exactly right
      // TODO: these are for table, is there another way to keep them?
      catnum: properties.catalogNumber,
      eventdate: properties.eventDate,
      sciname: properties.sciname,
    };
  });

  return {
    taxaArr,
    collArr,
    recordArr,
    origin: '', // TODO: get
    query: '', // TODO: get
  };
}

function renderOccurrenceRows(html, searchData) {
  // TODO: need some way to determining which rows to render
  let rowsHtml = '';
  console.log(searchData);
  for (let i = 0; i < Math.min(100, searchData.recordArr.length); i++) {
    const { occid, host, sciname, eventdate, catnum, id } =
      searchData.recordArr[i];
    // TODO: find cat number
    rowsHtml += `<tr ${i % 2 ? 'class="alt"' : ''} id="tr${occid}">`;
    rowsHtml += `<td id="cat${occid}" >${catnum ?? ''}</td>`;
    rowsHtml += `<td id="label${occid}" >`;
    rowsHtml += `<a href="#" onclick="openRecord({occid:${occid}, host:\'${host}\'}); return false;">${
      id ? id : 'Not available'
    }</a>`;
    rowsHtml += `</td>`;
    rowsHtml += `<td id="e${occid}" >${eventdate}</td>`;
    rowsHtml += `<td id="s${occid}" >${sciname}</td>`;
    rowsHtml += `<td id="li${occid}" ><a href="#occid=${occid}" onclick="emit_occurrence_click(${occid})">See map point</a></td>`;
    rowsHtml += `</tr>`;
  }
  return html.replace('{{ROWS_PLACEHOLDER}}', rowsHtml);
}
