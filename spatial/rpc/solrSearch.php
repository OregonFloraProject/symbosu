<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/spatial/shared/solrSearchHelpers.php');
include_once('../../config/dbconnection.php');
include_once($SERVER_ROOT.'/classes/DynamicChecklistManager.php');

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
$pointlat = isset($_POST['pointlat']) ? trim($_POST['pointlat']) : '';
$pointlong = isset($_POST['pointlong']) ? trim($_POST['pointlong']) : '';
$radius = isset($_POST['radius']) ? trim($_POST['radius']) : '';
$pointunits = isset($_POST['pointunits']) ? $_POST['pointunits'] : '';
$upperlat = isset($_POST['upperlat']) ? trim($_POST['upperlat']) : '';
$rightlong = isset($_POST['rightlong']) ? trim($_POST['rightlong']) : '';
$bottomlat = isset($_POST['bottomlat']) ? trim($_POST['bottomlat']) : '';
$leftlong = isset($_POST['leftlong']) ? trim($_POST['leftlong']) : '';

# convert footprintGeoJson to geoJson
$footprintGeoJson = isset($_POST['footprintGeoJson']) ? $_POST['footprintGeoJson'] : '';
$geoJson = json_decode($footprintGeoJson);

$download = $_POST['download'] ?? $_GET['download'] ?? null;

try {
	$searchParams = array(
		'db' => $db,
		'taxa' => $taxa,
		'taxontype' => $taxontype,
		'usethes' => $usethes,
		'country' => $country,
		'state' => $state,
		'county' => $county,
		'local' => $local,
		'collector' => $collector,
		'collnum' => $collnum,
		'eventdate1' => $eventdate1,
		'eventdate2' => $eventdate2,
		'catnum' => $catnum,
		'includeothercatnum' => $includeothercatnum,
		'typestatus' => $typestatus,
		'hasimages' => $hasimages,
		'hasgenetic' => $hasgenetic,
		'includecult' => $includecult,
		'excludeinat' => $excludeinat,
		'pointlat' => $pointlat,
		'pointlong' => $pointlong,
		'radius' => $radius,
		'pointunits' => $pointunits,
		'upperlat' => $upperlat,
		'rightlong' => $rightlong,
		'bottomlat' => $bottomlat,
		'leftlong' => $leftlong,
		'geoJson' => $geoJson
	);

	if ($download) {
		if (!in_array($download, ['csv', 'docx'])) {
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode(['error' => true, 'message' => 'Invalid download format. Please use csv or docx.']);
			exit;
		}

		$tids = fetchDistinctTidInterpreted($searchParams);
		if (empty($tids)) {
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode(['error' => true, 'message' => 'No taxa found for current search']);
			exit;
		}

		$dclManager = new DynamicChecklistManager();
		$dynclid = $dclManager->createDynamicChecklistFromTids($tids);
		if ($dynclid === 0) {
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode(['error' => true, 'message' => 'Failed to create dynamic checklist']);
			exit;
		}

		include_once($SERVER_ROOT.'/ident/shared/checklistApi.php');
		$result = get_data(['dynclid' => $dynclid]);
		array_walk_recursive($result,'cleanWindowsRecursive');

		if ($download === 'csv') {
			include_once($SERVER_ROOT.'/checklists/checklistexport.php');
			exportChecklistToCSV($result);
			exit;
		} elseif ($download === 'docx') {
			include_once($SERVER_ROOT.'/checklists/checklistexport.php');
			exportChecklistToWord($result);
			exit;
		}
	} else {
		header("Content-Type: application/json; charset=utf-8");
		$geojson = executeSolrSearch($searchParams);
		echo json_encode($geojson);
	}
} catch (\Throwable $th) {
	header("Content-Type: application/json; charset=utf-8");
	$errorBody['error'] = true;
	$errorBody['message'] = $th->getMessage();
	echo json_encode($errorBody);
}

?>

