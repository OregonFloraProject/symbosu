<?php
include_once('../../config/symbini.php');
include_once('../shared/getTaxaData.php');
include_once('../../config/dbconnection.php');
include_once($SERVER_ROOT.'/classes/SOLRManager.php');
include_once($SERVER_ROOT.'/classes/ProfileManager.php');
include_once($SERVER_ROOT.'/classes/SpatialModuleManager.php');

header("Content-Type: application/json; charset=utf-8");

$solrManager = new SOLRManager();
ProfileManager::refreshUserRights();
$canReadRareSpp = $solrManager->getCanReadRareSpp();

// Declare error flag and message to transmit to frontend
$errorBody = array(
	'error' => false,
	'message' => ''
);

// Parse form input
$db = isset($_POST['db']) ? $_POST['db'] : array();
$taxa = isset($_POST['taxa']) ? trim($_POST['taxa']) : '';
$taxontype = isset($_POST['taxontype']) ? $_POST['taxontype'] : '';
$usethes = isset($_POST['usethes']) ? ($_POST['usethes'] === '1') : false;
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$state = isset($_POST['state']) ? trim($_POST['state']) : '';
$county = isset($_POST['county']) ? trim($_POST['county']) : '';
$local = isset($_POST['local']) ? trim($_POST['local']) : '';
$collector = isset($_POST['collector']) ? trim($_POST['collector']) : '';
$collnum = isset($_POST['collnum']) ? trim($_POST['collnum']) : '';
$eventdate1 = isset($_POST['eventdate1']) ? trim($_POST['eventdate1']) : '';
$eventdate2 = isset($_POST['eventdate2']) ? trim($_POST['eventdate2']) : '';
$catnum = isset($_POST['catnum']) ? trim($_POST['catnum']) : '';
$includeothercatnum = isset($_POST['includeothercatnum']) ? ($_POST['includeothercatnum'] === '1') : false;
$typestatus = isset($_POST['typestatus']) ? ($_POST['typestatus'] === '1') : false;
$hasimages = isset($_POST['hasimages']) ? ($_POST['hasimages'] === '1') : false;
$hasgenetic = isset($_POST['hasgenetic']) ? ($_POST['hasgenetic'] === '1') : false;
$includecult = isset($_POST['includecult']) ? ($_POST['includecult'] === '1') : false;
$excludeinat = isset($_POST['excludeinat']) ? ($_POST['excludeinat'] === '1') : false;
$polycoords = isset($_POST['polycoords']) ? $_POST['polycoords'] : '';
$pointlat = isset($_POST['pointlat']) ? trim($_POST['pointlat']) : '';
$pointlong = isset($_POST['pointlong']) ? trim($_POST['pointlong']) : '';
$radius = isset($_POST['radius']) ? trim($_POST['radius']) : '';
$pointunits = isset($_POST['pointunits']) ? $_POST['pointunits'] : '';
$upperlat = isset($_POST['upperlat']) ? trim($_POST['upperlat']) : '';
$rightlong = isset($_POST['rightlong']) ? trim($_POST['rightlong']) : '';
$bottomlat = isset($_POST['bottomlat']) ? trim($_POST['bottomlat']) : '';
$leftlong = isset($_POST['leftlong']) ? trim($_POST['leftlong']) : '';

function isFamilyName($taxonString){
	// if a taxon string ends with 'aceae' or 'idae' and is a single word, assume it's a family name
  	// the check for a single word is here to avoid false positives such as Corydalis aquae-gelidae
	$len = strlen($taxonString);
	$ends_aceae = $len >= 5 && substr($taxonString, -5) === 'aceae';
	$ends_idae = $len >= 4 && substr($taxonString, -4) === 'idae';
	$has_space = strpos($taxonString, ' ') !== false;
	return ($ends_aceae || $ends_idae) && !$has_space;
}

function parseDate($dateStr){
	$y = 0;
	$m = 0;
	$d = 0;

	$validformat1 = '/^\d{4}-\d{1,2}-\d{1,2}$/';
	$validformat2 = '/^\d{1,2}\/\d{1,2}\/\d{2,4}$/';
	$validformat3 = '/^\d{1,2} \D+ \d{2,4}$/';

	if(preg_match($validformat1, $dateStr)){
		$dateTokens = explode('-', $dateStr);
		$y = $dateTokens[0];
		$m = $dateTokens[1];
		$d = $dateTokens[2];
	}
	elseif(preg_match($validformat2, $dateStr)){
		$dateTokens = explode('/', $dateStr);
		$m = $dateTokens[0];
		$d = $dateTokens[1];
		$y = $dateTokens[2];
		if(strlen($y) == 2){
			if($y < 20){
				$y = '20' . $y;
			}
			else{
				$y = '19' . $y;
			}
		}
	}
	elseif(preg_match($validformat3, $dateStr)){
		$dateTokens = explode(' ', $dateStr);
		$d = $dateTokens[0];
		$mText = strtolower(substr($dateTokens[1], 0, 3));
		$y = $dateTokens[2];

		if(strlen($y) == 2){
			if($y < 15){
				$y = '20' . $y;
			}
			else{
				$y = '19' . $y;
			}
		}

		$mNames = array('jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
			'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12);
		$m = isset($mNames[$mText]) ? $mNames[$mText] : 0;
	}

	return array('y' => (string)$y, 'm' => (string)$m, 'd' => (string)$d);
}

function formatCheckDate($dateStr){
	if($dateStr == '') return '';

	$dateArr = parseDate($dateStr);
	if($dateArr['y'] == 0){
		throw new InvalidArgumentException('Please use the following date formats: yyyy-mm-dd, mm/dd/yyyy, or dd mmm yyyy');
	}

	if($dateArr['m'] > 12){
        throw new InvalidArgumentException('Month cannot be greater than 12. Note that the format should be YYYY-MM-DD');
	}

	if($dateArr['d'] > 28){
		if($dateArr['d'] > 31 ||
		   ($dateArr['d'] == 30 && $dateArr['m'] == 2) ||
		   ($dateArr['d'] == 31 && in_array($dateArr['m'], array(4, 6, 9, 11)))){
			throw new InvalidArgumentException('The Day (' + $dateArr['d'] + ') is invalid for that month');
		}
	}

	$mStr = $dateArr['m'];
	if(strlen($mStr) == 1) $mStr = '0' . $mStr;
	$dStr = $dateArr['d'];
	if(strlen($dStr) == 1) $dStr = '0' . $dStr;

	return $dateArr['y'] . '-' . $mStr . '-' . $dStr;
}


function buildTaxaParams($taxa, $taxontype, $usethes, &$solrQArr){
	if($taxa){
		$taxavals = array_map('trim', explode(',', $taxa));
		$taxonNames = array();
		foreach($taxavals as $name){
			if($taxontype === '1'){
				$splitArr = explode(': ', $name);
				$name = end($splitArr);
			}
			$taxonNames[] = $name;
		}

		$taxaArr = getTaxaData($taxonNames, $taxontype, $usethes);

		if($taxaArr){
			$taxaSolrqString = '';
			foreach($taxaArr as $key => $valueArray){
				if($taxontype == 4){
					$taxaSolrqString .= ' OR (parenttid:' . $key . ')';
				}
				else{
					if($taxontype == 5){
						$famArr = array();
						$scinameArr = array();
						if(isset($valueArray['families'])){
							$famArr = $valueArray['families'];
						}
						if(!empty($famArr)){
							$taxaSolrqString .= ' OR (family:(' . implode(' ', $famArr) . '))';
						}
						if(isset($valueArray['scinames'])){
							$scinameArr = $valueArray['scinames'];
							if(!empty($scinameArr)){
								foreach($scinameArr as $s){
									$sciEscaped = str_replace(' ', '\\ ', $s);
									$taxaSolrqString .= ' OR ((sciname:' . $sciEscaped . ') OR (sciname:' . $sciEscaped . '\\ *))';
								}
							}
						}
					}
					else{
						// taxontype 1, 2, 3
						if($taxontype == 3 || isFamilyName($key)){
							$taxaSolrqString .= ' OR (family:' . $key . ')';
						}
						if($taxontype == 3 || !isFamilyName($key)){
							$keyEscaped = str_replace(' ', '\\ ', $key);
							$taxaSolrqString .= ' OR ((sciname:' . $keyEscaped . ') OR (sciname:' . $keyEscaped . '\\ *))';
						}
					}
					if(isset($valueArray['synonyms'])){
						$synArr = $valueArray['synonyms'];
						$tidArr = array();
						if($taxontype == 1 || $taxontype == 2 || $taxontype == 5){
							foreach($synArr as $synTid => $synName){
								if(isFamilyName($synName)){
									$taxaSolrqString .= ' OR (family:' . $synName . ')';
								}
							}
						}
						foreach($synArr as $synTid => $synName){
							$tidArr[] = $synTid;
						}
						if(!empty($tidArr)){
							$taxaSolrqString .= ' OR (tidinterpreted:(' . implode(' ', $tidArr) . '))';
						}
					}
				}
			}
			if(!empty($taxaSolrqString)){
				$taxaSolrqString = substr($taxaSolrqString, 4);
				$solrQArr[] = '(' . $taxaSolrqString . ')';
			}
		}
	}
}

function buildTextParams($country, $state, $county, $local, $collector, $collnum, $eventdate1, 
	$eventdate2, $catnum, $includeothercatnum, $typestatus, $hasimages, $hasgenetic, $includecult, $excludeinat, &$solrQArr){
	if($country){
		$countryvals = array_map('trim', explode(',', $country));
		$countrySolrqString = '';
		foreach($countryvals as $val){
			if($countrySolrqString) $countrySolrqString .= ' OR ';
			$countrySolrqString .= '(country:"'.$val.'")';
		}
		$solrQArr[] = '(' . $countrySolrqString . ')';
	}

	if($state){
		$statevals = array_map('trim', explode(',', $state));
		$stateSolrqString = '';
		foreach($statevals as $val){
			if($stateSolrqString) $stateSolrqString .= ' OR ';
			$stateSolrqString .= '(StateProvince:"'.$val.'")';
		}
		$solrQArr[] = '(' . $stateSolrqString . ')';
	}

	if($county){
		$countyvals = array_map('trim', explode(',', $county));
		$countySolrqString = '';
		foreach($countyvals as $val){
			if($countySolrqString) $countySolrqString .= ' OR ';
			$countySolrqString .= '(county:' . str_replace(' ', '\\ ', $val) . '*)';
		}
		$solrQArr[] = '(' . $countySolrqString . ')';
	}

	if($local){
		$localityvals = array_map('trim', explode(',', $local));
		$localitySolrqString = '';
		foreach($localityvals as $val){
			if($localitySolrqString) $localitySolrqString .= ' OR ';
			$localitySolrqString .= '(';
			if(strpos($val, ' ') !== false){
				$vals = explode(' ', $val);
				$templocalitySolrqString = '';
				foreach($vals as $v){
					if($templocalitySolrqString) $templocalitySolrqString .= ' AND ';
					$templocalitySolrqString .= '((municipality:' . $v . '*) OR (locality:*' . $v . '*))';
				}
				$localitySolrqString .= $templocalitySolrqString;
			}
			else{
				$localitySolrqString .= '(locality:*' . $val . '*)';
			}
			$localitySolrqString .= ')';
		}
		$solrQArr[] = '(' . $localitySolrqString . ')';
	}

	if($collector){
		$collectorvals = array_map('trim', explode(',', $collector));
		$collectorSolrqString = '';
		foreach($collectorvals as $val){
			if($collectorSolrqString) $collectorSolrqString .= ' OR ';
			$collectorSolrqString .= '(recordedBy:*' . str_replace(' ', '\\ ', $val) . '*)';
		}
		$solrQArr[] = '(' . $collectorSolrqString . ')';
	}

	if($collnum){
		$collnumvals = array_map('trim', explode(',', $collnum));
		$collnumSolrqString = '';
		foreach($collnumvals as $val){
			if($collnumSolrqString) $collnumSolrqString .= ' OR ';
			$pos = strpos($val, ' - ');
			if($pos !== false){
				$t1 = trim(substr($val, 0, $pos));
				$t2 = trim(substr($val, $pos + 3));
				if(is_numeric($t1) && is_numeric($t2)){
					$collnumSolrqString .= '(recordNumber:[' . $t1 . ' TO ' . $t2 . '])';
				}
				else{
					$collnumSolrqString .= "(recordNumber:['" . $t1 . "' TO '" . $t2 . "'])";
				}
			}
			else{
				$collnumSolrqString .= '(recordNumber:"' . $val . '")';
			}
		}
		$solrQArr[] = '(' . $collnumSolrqString . ')';
	}

	if($eventdate1 || $eventdate2){
		if(!$eventdate1 && $eventdate2){
			$eventdate1 = $eventdate2;
			$eventdate2 = '';
		}
		$eventdate1 = formatCheckDate($eventdate1);
		if($eventdate2){
			$eventdate2 = formatCheckDate($eventdate2);
		}

		if($eventdate2){
			$colldateSolrqString = '(eventDate:[' . $eventdate1 . 'T00:00:00Z TO ' . $eventdate2 . 'T23:59:59.999Z])';
		}
		else{
			if(substr($eventdate1, -5) === '00-00'){
				$colldateSolrqString = '(coll_year:' . substr($eventdate1, 0, 4) . ')';
			}
			elseif(substr($eventdate1, -2) === '00'){
				$colldateSolrqString = '((coll_year:' . substr($eventdate1, 0, 4) . ') AND (coll_month:' . substr($eventdate1, 5, 2) . '))';
			}
			else{
				$colldateSolrqString = '(eventDate:[' . $eventdate1 . 'T00:00:00Z TO ' . $eventdate1 . 'T23:59:59.999Z])';
			}
		}
		$solrQArr[] = $colldateSolrqString;
	}

	if($catnum){
		$catnumvals = array_map('trim', explode(',', $catnum));
		$catnumSolrqString = '';
		foreach($catnumvals as $val){
			if($catnumSolrqString) $catnumSolrqString .= ' OR ';
			$catnumSolrqString .= '(catalogNumber:"' . $val . '")';
			if($includeothercatnum){
				$catnumSolrqString .= ' OR (otherCatalogNumbers:"' . $val . '")';
			}
		}
		$solrQArr[] = '(' . $catnumSolrqString . ')';
	}

	if($typestatus){
		$solrQArr[] = '((typeStatus:[* TO *]))';
	}

	if($hasimages){
		$solrQArr[] = '((imgid:[* TO *]))';
	}

	if($hasgenetic){
		$solrQArr[] = '((resourcename:[* TO *]))';
	}

	if(!$includecult){
		$solrQArr[] = 'NOT (cultivationStatus:1)';
	}

	if($excludeinat){
		$solrQArr[] = 'NOT ((relationship:"iNaturalistObservation") AND NOT (CollType:"Preserved Specimens"))';
	}
}

function buildGeographyParams($polycoords, $pointlat, $pointlong, $radius, $pointunits,
	$upperlat, $rightlong, $bottomlat, $leftlong, &$solrGeoQArr){

	if($polycoords){
		// SOLR expects the coordinates in long-lat format, whereas the rest of the site uses lat-long so we reverse the coordinates here and reconstruct the string
		$polygonLatLngs = substr($polycoords, 10, -2);
		$coordPairs = explode(',', $polygonLatLngs);
		$reversedPairs = array();
		foreach($coordPairs as $pair){
			$parts = explode(' ', trim($pair));
			if(count($parts) == 2){
				$reversedPairs[] = $parts[1] . ' ' . $parts[0];
			}
		}
		$polygonLngLats = implode(',', $reversedPairs);
		$solrGeoQArr[] = '"Intersects(POLYGON ((' . $polygonLngLats . ')))"';
	}

	// Circle
	if($pointlat !== '' && $pointlong !== '' && $radius !== ''){
		$radiusKm = $radius;
		if($pointunits === 'mi'){
			$radiusKm = $radius / 0.6214;
		}
		$solrGeoQArr[] = '{!geofilt sfield=geo pt=' . $pointlat . ',' . $pointlong . ' d=' . $radiusKm . '}';
	}

	// Rectangle
	if($upperlat !== '' && $rightlong !== '' && $bottomlat !== '' && $leftlong !== ''){
		$rectWKT = '"Intersects(POLYGON((' . $leftlong . ' ' . $upperlat . ',' . $rightlong . ' ' . $upperlat . ',' . $rightlong . ' ' . $bottomlat . ',' . $leftlong . ' ' . $bottomlat . ',' . $leftlong . ' ' . $upperlat . ')))"';
		$solrGeoQArr[] = $rectWKT;
	}
}

try {
	$solrQArr = array();
	$solrGeoQArr = array();

	// Build collection params
	if(is_array($db) && !empty($db)){
		$all = false;
		$collid = '';
		foreach($db as $d){
			if($d === 'all'){
				$all = true;
				break;
			}
		}
		if(!$all){
			$collid = implode(' ', $db);
			if(!empty($collid)){
				$solrQArr[] = '(collid:(' . $collid . '))';
			} else {
				throw new InvalidArgumentException('Please choose at least one collection');
			}
		}
	}
	
	buildTaxaParams($taxa, $taxontype, $usethes, $solrQArr);

	buildTextParams($country, $state, $county, $local, $collector, $collnum, $eventdate1, $eventdate2,
		$catnum, $includeothercatnum, $typestatus, $hasimages, $hasgenetic, $includecult, $excludeinat,
		$solrQArr);

	buildGeographyParams($polycoords, $pointlat, $pointlong, $radius, $pointunits,
		$upperlat, $rightlong, $bottomlat, $leftlong, $solrGeoQArr);

	// Assemble SOLR query
	$solrQ = '';
	if(!empty($solrQArr)){
		$solrQ = implode(' AND ', $solrQArr);
		$solrQ .= ' AND (decimalLatitude:[* TO *] AND decimalLongitude:[* TO *] AND sciname:[* TO *])';
	}
	else{
		$solrQ = '(sciname:[* TO *])';
	}

	$solrGeoQ = '';
	if(!empty($solrGeoQArr)){
		$solrGeoQ = implode(' OR geo:', $solrGeoQArr);
		$solrGeoQ = 'geo:' . $solrGeoQ;
	}

	// Actual communication with SOLR
	// Get record count (before security applied)
	$pArrCount = array(
		'q' => $solrQ,
		'rows' => 0,
		'start' => 0,
		'wt' => 'json',
		'action' => 'getsolrreccnt'
	);
	if(!empty($solrGeoQ)) $pArrCount['fq'] = $solrGeoQ;
	$full = $solrManager->callingSOLR($pArrCount);
	$fullCount = is_numeric($full['response']['numFound'] ?? null) ? (int)$full['response']['numFound'] : 0;
	$recordCount = $fullCount;
	$hiddenFound = 0;

	// Apply security and get filtered count if needed
	$solrQSecure = $solrManager->checkQuerySecurity($solrQ);
	if(!$canReadRareSpp){
		$pArrSecure = array(
			'q' => $solrQSecure,
			'rows' => 0,
			'start' => 0,
			'wt' => 'json',
			'action' => 'getsolrreccnt'
		);
		if(!empty($solrGeoQ)) $pArrSecure['fq'] = $solrGeoQ;
		$partial = $solrManager->callingSOLR($pArrSecure);
		
		$partialCount = is_numeric($partial['response']['numFound'] ?? null) ? (int)$partial['response']['numFound'] : 0;
		if($fullCount > $partialCount){
			$hiddenFound = $fullCount - $partialCount;
		}
		$recordCount = $partialCount;
	}

	// Execute main SOLR query with security
	$MAX_RECORD_COUNT = 20000;
	$SOLR_FIELDS = 'occid,collid,catalogNumber,family,sciname,tidinterpreted,recordedBy,recordNumber,eventDate,geo,CollectionName,CollType';

	$pArr = array(
		'q' => $solrQSecure,
		'rows' => min($recordCount, $MAX_RECORD_COUNT),
		'start' => 0,
		'fl' => $SOLR_FIELDS,
		'wt' => 'geojson',
		'geojson.field' => 'geo',
		'omitHeader' => 'true',
		'action' => 'lazyload'
	);
	if(!empty($solrGeoQ)) $pArr['fq'] = $solrGeoQ;
	$geojson = $solrManager->callingSOLR($pArr);
	$geojson['query'] = 'q=' . $solrQ . (!empty($solrGeoQ) ? '&fq=' . $solrGeoQ : '');
	$geojson['hiddenFound'] = $hiddenFound;
	echo json_encode($geojson);
} catch (\Throwable $th) {
	$errorBody['error'] = true;
	$errorBody['message'] = $th->getMessage();
	echo json_encode($errorBody);
}

?>
