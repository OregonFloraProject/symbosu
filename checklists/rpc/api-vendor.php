<?php
include_once("../../config/symbini.php");
if (empty($CLIENT_ROOT)) {#I don't know why this is empty
	$CLIENT_ROOT = '/';
}
include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/classes/ExploreManager.php");
include_once("$SERVER_ROOT/classes/InventoryManager.php");
include_once("$SERVER_ROOT/classes/TaxaManager.php");
include_once("$SERVER_ROOT/classes/IdentManager.php");

require_once($SERVER_ROOT . '/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\IOFactory;

// Status code constants for previewSPP classification
define('CODE_ACCEPTED', 'Accepted');
define('CODE_SYNONYM', 'Synonym');
define('CODE_AMBIGUOUS', 'Ambiguous');
define('CODE_UNRECOGNIZED', 'Unrecognized');
define('CODE_DUPLICATE', 'Duplicate');
define('CODE_NON_NATIVE', 'Non-native');

$result = [];


function updateInfo($model) {
	$em = SymbosuEntityManager::getEntityManager();
	$result = [];
	$fields = array(
		'name'=>'setName',
		'authors'=>'setAuthors',
		'locality'=>'setLocality',
		'publication'=>'setPublication',
		'abstract'=>'setAbstract',
		'notes'=>'setNotes',
		'latcentroid'=>'setLatcentroid',
		'longcentroid'=>'setLongcentroid',
		'pointradiusmeters'=>'setPointradiusmeters'
	);
	$success = 0;
	$error = 0;
	foreach ($fields as $field => $function) {
		if (isset($_REQUEST[$field]) && method_exists($model,$function)) {
			$model->$function($_REQUEST[$field]);
			$success++;
		}
	}
	$em->persist($model);
	$em->flush();
	$result = [
		"success" => $success
	];
	
	return $result;
}
function rewriteSPP() {
	$result = [];
	$success = 0;
	
	if ($_REQUEST['upload']) {
		$arr = json_decode($_REQUEST['upload']);
		#var_dump($arr);
		$tids = json_decode($_REQUEST['tids']);
		
		#exit;
		
		#delete existing
		foreach ($tids as $tid) {
			$em = SymbosuEntityManager::getEntityManager();
			$repo = $em->getRepository("Fmchklsttaxalink");
			$link = $repo->find([
				'tid' => $tid,
				'clid' => $_REQUEST['clid'],
				'morphospecies' => ''
			]);
			$em->remove($link);
			$em->flush();
			#$success++;
		}		
		
		#add new
		foreach ($arr as $obj) {
			if (in_array($obj->code,['Accepted','Synonym'])) {
				$em = SymbosuEntityManager::getEntityManager();
				$repo = new Fmchklsttaxalink();
				$repo->setTid($obj->tidaccepted);
				$repo->setClid($_REQUEST['clid']);
				if ($obj->notes) {
					$repo->setNotes(join('; ',$obj->notes));
				}
				$repo->setInitialtimestamp(new \DateTime());
				$em->merge($repo);#persist
				$em->flush();
				$success++;
			}
		}
	}
	
	$result = [
		"status" => $success
	];
	return $result;
}

/*
function SPPtoCSV($results) {
	global $CLIENT_ROOT, $SERVER_ROOT;
	
	$filename = '';
	$url = '';
	if (sizeof($results)) {
		$path = '/temp/downloads/vendor/';
		$dir = $SERVER_ROOT  . $path;
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		$file = uniqid() . '.csv';
		$filename =  $dir . $file;
		$url = $CLIENT_ROOT . $path   . $file;
		$fp = fopen($filename, 'w');
		if ($fp) {
			fputcsv($fp,["Your sciname","Your notes","Result","OF sciname","Feedback"]);
			foreach ($results as $result) {
				//var_dump($result);
				$notes = [];//($result['notes']? $result['notes'] : []);
				$temp = [$result['searchSciname'],join("; ",$notes),$result['code'],(isset($result['OFsciname'])? $result['OFsciname'] :''),join("; ",$result['feedback'])];
				fputcsv($fp, $temp);
			}
			fclose($fp);
		}
	}
	return $url;
}*/

function addOneSPP() {
	$result = [];
	$success = 0;
	$error = 0;
	foreach ($_REQUEST['spp'] as $tid) {
		try {
			/*
				merge will be deprecated in future versions.  
				Using persist can cause a duplicate key error, 
				which should be handled by try/catch, but isn't.
				So for now, we stick with merge.
			*/
			$em = SymbosuEntityManager::getEntityManager();
			$repo = new Fmchklsttaxalink();
			$repo->setTid($tid);
			$repo->setClid($_REQUEST['clid']);
			$repo->setInitialtimestamp(new \DateTime());
			$em->merge($repo);#persist
			$em->flush();
			$success++;
		}
		catch (UniqueConstraintViolationException $e) {
			#SymbosuEntityManager::resetManager();
		}
	}
	$result = [
		"status" => $success
	];

	return $result;
}
function deleteSPP() {
	$result = [];
	$success = 0;
	$error = 0;
	foreach ($_REQUEST['spp'] as $tid) {
		$em = SymbosuEntityManager::getEntityManager();
		$repo = $em->getRepository("Fmchklsttaxalink");
		$link = $repo->find([
			'tid' => $tid,
			'clid' => $_REQUEST['clid'],
			'morphospecies' => ''
		]);
		$em->remove($link);
		$em->flush();
		$success++;
	}
	$result = [
		"status" => $success
	];

	return $result;
}
function editSPP() {
	$result = [];
	$success = 0;
	$error = 0;
	if (!empty($_REQUEST['spp']) && !empty($_REQUEST['notes'])) {
		$em = SymbosuEntityManager::getEntityManager();
		$repo = $em->getRepository("Fmchklsttaxalink");
		$link = $repo->find([
			'tid' => $_REQUEST['spp'],
			'clid' => $_REQUEST['clid'],
			'morphospecies' => ''
		]);
		$link->setNotes(str_replace(';',',',$_REQUEST['notes']));//semicolons are used to concatenate notes, so disallow semicolons within a note
		$em->merge($link);#persist
		$em->flush();
		$success++;
	}
	$result = [
		"status" => $success
	];

	return $result;
}
/*
//vetting in vendorUploadModal.jsx so as to avoid another api call
function handleColumnNames($obj,$target) {
	$acceptable = [];
	switch ($target) {
		case 'sciname':
			$acceptable = ['sciname','scientificname','sci name','scientific name','sci_name','scientific_name','sci-name','scientific-name'];
			break;
		case 'notes':
			$acceptable = ['notes','mynotes','my-notes','my_notes'];
			break	;
	}
	foreach ($obj as $key => $col) {
		if (in_array(strtolower($key),$acceptable)) {
			return $col;
		}
	}
}
*/

/**
 * Run a batched LIKE query for multiple scientific names in chunks of 50.
 * Returns an associative array: searchName => [matched DB rows], with exact matches sorted first.
 */
function batchScinameQuery($em, $searchNames, $RANK_GENUS, $batchSize) {
	$results = [];
	foreach ($searchNames as $name) {
		$results[$name] = [];
	}

	// Split searchNames into batch for quicker
	$chunks = array_chunk($searchNames, $batchSize);
	$expr = $em->getExpressionBuilder();

	foreach ($chunks as $chunk) {
		$qb = $em->createQueryBuilder()
			->select("t.sciname as taxaname", "t.tid", "ts.tidaccepted", "tl.nativity")
			->from("Taxa", "t")
			->innerJoin("Taxstatus", "ts", "WITH", "t.tid = ts.tid")
			->innerJoin("Fmchklsttaxalink", "tl", "WITH", "t.tid = tl.tid")
			->where("tl.clid = 1")
			->andWhere("t.rankid > $RANK_GENUS");

		$orConditions = $expr->orX();
		// Build OR conditions of multiple names from the excel file
		foreach ($chunk as $i => $name) {
			$paramName = "s$i";
			$orConditions->add($expr->eq("t.sciname", ":$paramName"));
			$qb->setParameter($paramName, $name);
		}
		$qb->andWhere($orConditions);

		$dbRows = $qb->getQuery();
		$dbRows = $dbRows->getArrayResult();

		foreach ($chunk as $name) {
			foreach ($dbRows as $dbRow) {
				// Find each name from the excel file and match its record on database
				if (strpos($dbRow['taxaname'], $name) !== false) {
					$results[$name][] = $dbRow;
				}
			}
		}
	}

	return $results;
}

/**
 * Classify a single row based on its DB query results.
 * Sets code to Accepted, Synonym, Ambiguous, or Unrecognized.
 * If $snr['tidaccepted'] === $snr['tid'], it is a normal taxon.
 * If $snr['tidaccepted'] !== $snr['tid'] and there is only one $snr['tidaccepted']
 * => Synonym
 * If multiple $snr['tidaccepted'] => Ambiguous
 */
function classifyRow(&$row, $sciNameResults) {
	if (empty($sciNameResults)) {
		$row['code'] = CODE_UNRECOGNIZED;
		$row['feedback'][] = 'This name is not found in our database of Oregon plants. Please check the spelling.';
		return;
	}

	$row['results'] = $sciNameResults;
	$tidaccepteds = [];

	foreach ($sciNameResults as $snr) {
		$row['tid'] = $snr['tid'];
		
		// Match taxon name of the excel file and the name on record
		// If they match tid === tidaccepted, normal taxon
		if ($row['searchSciname'] === $snr['taxaname'] &&
		  $snr['tidaccepted'] === $snr['tid']) {
			$row['code'] = CODE_ACCEPTED;
			$row['tidaccepted'] = $snr['tidaccepted'];
			$row['nativity'] = $snr['nativity'];
		} else {
			$tidaccepteds[$snr['tidaccepted']] = null;
		}
	}

	if ($row['code'] === null) {
		if (count($tidaccepteds) > 1) {
			$row['code'] = CODE_AMBIGUOUS;
			$row['feedback'][] = 'This is a synonym for more than one species and cannot be automatically translated. Look up the name in the Search all plants box for possible translations.';
		} else {
			$row['code'] = CODE_SYNONYM;
			$row['tidaccepted'] = $sciNameResults[0]['tidaccepted'];
			$row['feedback'][] = 'This is a synonym for another species and will be translated (see OF sciname column).';
			$row['nativity'] = $snr['nativity'];
		}
	}
}

/**
 * Mark synonym duplicates and non-native species.
 * Batch-fetches taxa scinames instead of one query per row.
 */
function markNonNativeAndSynonymDupes(&$rows, $em, $acceptedNativities) {
	// Batch fetch scinames for all tidaccepted values
	$tidacceptedValues = [];
	foreach ($rows as $entry) {
		if ($entry['code'] === CODE_ACCEPTED || $entry['code'] === CODE_SYNONYM) {
			$tidacceptedValues[$entry['tidaccepted']] = true;
		}
	}

	// Extract all tids
	$tids = [];
	foreach ($rows as $key => $entry) {
		if ($entry['tid'] !== null) {
			$tids[$key] = $entry['tid'];
		}
	}

	$taxaScinames = [];
	if (!empty($tidacceptedValues)) {
		$sciResults = $em->createQueryBuilder()
			->select("t.tid", "t.sciname")
			->from("Taxa", "t")
			->where("t.tid IN (:tids)")
			->setParameter("tids", array_keys($tidacceptedValues))
			->getQuery()
			->getArrayResult();
		foreach ($sciResults as $t) {
			$taxaScinames[$t['tid']] = $t['sciname'];
		}
	}

	foreach ($rows as $key => &$entry) {
		// Sometimes the actual taxon and its synonym exist in the same excel table
		// So we treat the synonym as the duplicate
		if ($entry['code'] === CODE_SYNONYM) {
			$this_key = array_search($entry['tidaccepted'], $tids);
			if ($this_key !== false) {
				if (!empty($entry['notes'])) {
					$rows[$this_key]['notes'] = array_merge($rows[$this_key]['notes'], $entry['notes']);
				}
				$entry['feedback'][] = 'This is a duplicate entry for ' . $rows[$this_key]['sciname'] . ' and will be removed';
				$entry['code'] = CODE_DUPLICATE;
			}
		}

		if ($entry['code'] === CODE_SYNONYM || $entry['code'] === CODE_ACCEPTED) {
			$entry['OFsciname'] = $taxaScinames[$entry['tidaccepted']] ?? '';

			if (!in_array($entry['nativity'], $acceptedNativities)) {
				$entry['feedback'][] = 'This is not a native Oregon plant species and will not be included.';
				$entry['code'] = CODE_NON_NATIVE;
			} elseif ($entry['nativity'] === 'native and exotic') {
				$entry['feedback'][] = 'This taxon has both native and exotic populations in Oregon.';
			}
		}
	}
	unset($entry);
}

function parseUploadedFile($tmpPath) {
	$spreadsheet = IOFactory::load($tmpPath);
	$sheet = $spreadsheet->getActiveSheet();
	$rows = $sheet->toArray(null, true, true, false);

	if (empty($rows)) {
		return null;
	}

	$headers = $rows[0];
	$normalizedHeaders = [];
	$sciNameKey = null;

	// Getting the header of the datasheet
	foreach ($headers as $key => $header) {
		$lower = strtolower($header);
		if (in_array($lower, ['sciname', 'scientificname', 'sci name', 'scientific name', 'sci_name', 'scientific_name', 'sci-name', 'scientific-name'])) {
			$normalizedHeaders[$key] = 'sciname';
			$sciNameKey = $key;
		} elseif (in_array($lower, ['notes', 'mynotes', 'my-notes', 'my_notes'])) {
			$normalizedHeaders[$key] = 'notes';
		} else {
			$normalizedHeaders[$key] = $header;
		}
	}

	// Require row ScientificName
	if ($sciNameKey === null) {
		return null;
	}

	// Getting plant from each row, ignoring blank rows
	$result = [];
	for ($i = 1; $i < count($rows); $i++) {
		$row = $rows[$i];
		$isBlank = true;
		foreach ($row as $cell) {
			if ($cell !== null && $cell !== '') {
				$isBlank = false;
				break;
			}
		}
		if ($isBlank) {
			continue;
		}

		$assocRow = [];
		foreach ($row as $key => $value) {
			if (isset($normalizedHeaders[$key])) {
				$assocRow[$normalizedHeaders[$key]] = trim($value ?? '');
			}
		}
		$result[] = $assocRow;
	}

	return $result;
}

function previewSPP() {
	$RANK_GENUS = 180;
	$acceptedNativities = ["endemic to Oregon", "native", "native and exotic", "native?"];

	$input_array = null;

	if (isset($_FILES['upload']) && $_FILES['upload']['error'] === UPLOAD_ERR_OK) {
		$input_array = parseUploadedFile($_FILES['upload']['tmp_name']);
		if ($input_array === null) {
			return [
				'status' => 'error',
				'message' => 'Your file must contain a header row with "ScientificName" (required) and "Notes" (optional).'
			];
		}
	} elseif (!empty($_REQUEST['upload'])) {
		$input_array = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $_REQUEST['upload']), true);
	}

	if (empty($input_array)) {
		return ['status' => 'error', 'message' => 'No data received.'];
	}

	$em = SymbosuEntityManager::getEntityManager();

	// Normalize all names and collect unique search terms
	$rows = [];
	$searchNames = [];
	foreach ($input_array as $key => $input) {
		$sciname = trim($input['sciname']);
		$searchSciname = str_replace("subsp.", "ssp.", $sciname);
		$notes = [];
		if (isset($input['notes'])) {
			$notes = is_array($input['notes']) ? $input['notes'] : [$input['notes']];
		}
		$rows[$key] = [
			'sciname' => $sciname,
			'searchSciname' => $searchSciname,
			'notes' => $notes,
			'tid' => null,
			'tidaccepted' => null,
			'code' => null,
			'nativity' => null,
			'feedback' => [],
			'results' => [],
		];
		if ($sciname !== $searchSciname) {
			$rows[$key]['feedback'][] = 'OregonFlora uses ssp. instead of subsp.';
		}
		if (!in_array($searchSciname, $searchNames)) {
			$searchNames[] = $searchSciname;
		}
	}

	// Batch query m names at once — O(N/m) queries instead of O(N)
	$batch_size = 300;
	$allDbResults = batchScinameQuery($em, $searchNames, $RANK_GENUS, $batch_size);

	// Classify each row based on query results
	foreach ($rows as &$row) {
		classifyRow($row, $allDbResults[$row['searchSciname']] ?? []);
	}
	unset($row);

	// Check synonym dupes + nativity (batched taxa fetch)
	markNonNativeAndSynonymDupes($rows, $em, $acceptedNativities);

	return [
		"status" => (!empty($rows) ? "success" : "notfound"),
		"results" => $rows,
		"csvURL" => ""
	];
}

function getVendorsByTaxa($tid) {
	//fmchcklsttaxalink, join on tid, join to clid...
	//fmchklstprojlink, join on clid, pid == 4
	//fmchklstchildren, join on clid, clidchild exists
	$em = SymbosuEntityManager::getEntityManager();
	$expr = $em->getExpressionBuilder();
	$q = $em->createQueryBuilder();

	$taxaQuery = $em->createQueryBuilder()
		->select("t.sciname","t.tid","c.clid","c.name","t.rankid")
		->from("Fmchecklists", "c")
		->innerJoin("Fmchklsttaxalink", "ct", "WITH", "ct.clid = c.clid")
		->innerJoin("Taxa", "t", "WITH", "t.tid = ct.tid")
		->innerJoin("Fmchklstprojlink", "cp", "WITH", "c.clid = cp.clid")
		->innerJoin("Fmchklstchildren", "cc", "WITH", "c.clid = cc.clidChild")
		->andWhere("cp.pid = :pid")
		->andWhere(
			$expr->orX(
				$expr->eq("t.tid",":tid"),
				$expr->in('t.tid',						
					$em->createQueryBuilder()
						->select("t2.tid")
						->from("Taxa","t2")
						->innerJoin("Taxaenumtree","te2","WITH","t2.tid = te2.tid")
						->where('te2.parenttid = :tid')
						->getDQL()
					)
				),
			)
		->setParameter(":tid", $tid)
		->setParameter(":pid", getVendorPid())
		->orderBy('t.rankid, t.sciname, c.name')
		->distinct()
		->getQuery();

		$tresults = $taxaQuery->getResult();
		$results = [];
		foreach ($tresults as $t) {
			if (!isset($results[$t['tid']])) {
				$results[$t['tid']] = [
					'tid' => $t['tid'],
					'sciname' => $t['sciname'],
					'vendors' => []
				];
			}
			$results[$t['tid']]['vendors'][] = [
				'clid' => $t['clid'],
				'name' => $t['name']
			];
			
		}
		return $results;
}


$isEditor = false;
$result = [];

if (array_key_exists("clid", $_REQUEST) && is_numeric($_REQUEST["clid"]) && array_key_exists("pid", $_REQUEST) && is_numeric($_REQUEST["pid"])) {
  $em = SymbosuEntityManager::getEntityManager();
  $repo = $em->getRepository("Fmchecklists");
  $model = $repo->find($_REQUEST["clid"]);
  $checklist = ExploreManager::fromModel($model);
  if ($_REQUEST["pid"] > -1) {
	  $checklist->setPid($_REQUEST["pid"]);
	}
	if($IS_ADMIN || (array_key_exists("ClAdmin",$USER_RIGHTS) && in_array($_REQUEST["clid"],$USER_RIGHTS["ClAdmin"]))){
		$isEditor = true;
	}
  if (array_key_exists("update", $_REQUEST)) {
		if ($isEditor) {
			switch ($_REQUEST['update']) {
				case 'info':
					$result = updateInfo($model);
					break;
				case 'spp':
					switch($_REQUEST['action']) {#if (array_key_exists("spp", $_REQUEST) && array_key_exists("action", $_REQUEST) ) {
						case 'add':
							$result = addOneSPP();
							break;
						case 'delete':
							$result = deleteSPP();
							break;
						case 'edit':
							$result = editSPP();
							break;
						case 'preview':
							$result = previewSPP();
							break;
						case 'rewrite':
							$result = rewriteSPP();
							break;
					}
					break;
			}				
		}
	}
}elseif(array_key_exists("tid", $_REQUEST) && is_numeric($_REQUEST["tid"]) && array_key_exists("action", $_REQUEST) && $_REQUEST["action"] === 'taxa_garden') {
	$result = getVendorsByTaxa($_REQUEST["tid"]);
}else{
	#todo: generate error or redirect
}
// Begin View


array_walk_recursive($result,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);





?>

