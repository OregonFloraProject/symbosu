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

function previewSPP() {
	$result = [];
	$success = 0;
	$error = 0;
	$csvURL = '';

	$CLID_GARDEN_ALL = getGardenClid();
	$RANK_GENUS = 180;

	$acceptedNativities = [
		"endemic to Oregon",
		"native",
		"native and exotic",
		"native?"
	];
	$arr = json_decode( preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $_REQUEST['upload']), true );
	#var_dump($arr);exit;
	$firstArr = [];
	#compile verified list to update, then delete existing, then add new
	#more forgiving of empty columns in csv
	#standardize formatting
	$em = SymbosuEntityManager::getEntityManager();
	$expr = $em->getExpressionBuilder();
	$q = $em->createQueryBuilder();
	foreach ($arr as $key => $obj) {
		$temp = [];
		$obj['sciname'] = trim($obj['sciname']);
		$temp['sciname'] = $obj['sciname'];// = handleColumnNames($obj,'sciname');#store orig in $obj['sciname']
		$temp['notes'] = [];//$obj['notes'] = handleColumnNames($obj,'notes');#store orig in $obj['notes'];
		if (isset($obj['notes'])) {
			if (is_array($obj['notes'])) {
				$temp['notes'] = $obj['notes'];
			}else{
				$temp['notes'][] = $obj['notes'];
			}
		}
		$searchSciname = $obj['sciname'];
		$searchSciname = str_replace("subsp.","ssp.",$searchSciname);
		$temp['searchSciname'] = $searchSciname;
		#$searchParts = explode(' ',$searchSciname);
		#$searchTerm = '%' . join('% %',$searchParts) .'%';
		$searchTerm = '%' . $searchSciname .'%';
	
		#echo $obj['sciname'] . ":<br>";
		#echo $searchTerm;
	
		$sciNameQuery = $em->createQueryBuilder()
			->select("t.sciname as text", "t.tid as value","ts.tidaccepted","tl.clid","tl.nativity")
			->from("Taxa", "t")
			->innerJoin("Taxstatus", "ts", "WITH", "t.tid = ts.tid")
			->innerJoin("Fmchklsttaxalink", "tl", "WITH", "t.tid = tl.tid")
			->where("tl.clid = 1")
			#->andWhere("t.sciname LIKE :search")
			->andWhere(
				$expr->orX(
					$expr->eq("t.sciname",":search"),
					$expr->like("t.sciname",":search"),
				)
			)
			->andWhere("t.rankid > $RANK_GENUS")
			->orderBy('LOCATE(:searchTermLocate,t.sciname)')#put exact match first - https://stackoverflow.com/questions/52052712/select-exact-match-first-in-doctrine-query-builder
			#->groupBy("t.tid")
			->setParameter("search",  $searchTerm)
			->setParameter("searchTermLocate",$searchSciname)#without %%
			#->setParameter("omit",$omit)
			#->setMaxResults(3)
			->getQuery();
		
		$sciNameResults = $sciNameQuery->getArrayResult();
		
		#usort($sciNameResults, function ($a, $b) {
		#	return strcmp($a["text"], $b["text"]);
		#});
				
		$temp['results'] = $sciNameResults;
		$temp['query'] = $sciNameQuery;
		$temp['tid'] = null;
		$temp['tidaccepted'] = null;
		$temp['code'] = null;
		$temp['feedback'] = [];
	
		#initially set codes, to be tweaked below
		if(sizeof($sciNameResults) == 0) {
			$temp['code'] = 'Unrecognized';
			$temp['feedback'][] = 'This name is not found in our database of Oregon plants. Please check the spelling.';
		}else {
			$tidaccepteds = [];
			$provisional = [];
			foreach ($sciNameResults as $snr) {
				if ($snr['tidaccepted'] === $snr['value']) {
					$provisional[$snr['text']] = [
						'name' => $snr['text'],
						'code' => 'Accepted',
						'tid' => $snr['value'],
						'tidaccepted' => $snr['value']
					];
				}else{
					$tidaccepteds[$snr['tidaccepted']] = null;
				}
			}
			if ($provisional) {//check best name match of the accepteds
				$bestMatch = null;
				if (sizeof($provisional) == 1) {//only one to choose from
					$bestMatch = array_shift($provisional);
				}else{
					foreach ($provisional as $p) {
						if ($temp['searchSciname'] == $p['name']) {//exact name match
							$bestMatch = $p;
						}
					}
					if (!$bestMatch) {//fallback ?
						$bestMatch = array_shift($provisional);
					}
				}
				if ($bestMatch) {
					$temp['code'] = $bestMatch['code'];
					$temp['tid'] = $bestMatch['tid'];
					$temp['tidaccepted'] = $bestMatch['tidaccepted'];
				}					
			}
			if ($temp['code'] === null) {
				if (sizeof($tidaccepteds) > 1) {
					$temp['code'] = 'Ambiguous';
				}else {
					$temp['code'] = 'Synonym';
				}
				$temp['tid'] = $sciNameResults[0]['value'];//arbitrarily assign the first value, b/c this won't be used
				$temp['tidaccepted'] = $sciNameResults[0]['tidaccepted'];//arbitrarily assign the first value, b/c this won't be used
			}
		}
		#check for ambiguous - Mimulus guttatus, Convolvulus sepium
		/*if ($temp['tid'] != $temp['tidaccepted']) {
			$tidaccepteds = [];
			foreach ($sciNameResults as $res) {
				$tidaccepteds[$res['tidaccepted']] = null;
			}
			if (sizeof($tidaccepteds) > 1) {
				$temp['code'] = 'Ambiguous';
			}
		}	*/			
		switch ($temp['code']) {
			case 'Synonym':
				$temp['feedback'][] = 'This is a synonym for another species and will be translated (see OF sciname column).';
				break;
			case 'Ambiguous':
				$temp['feedback'][] = 'This is a synonym for more than one species and cannot be automatically translated. Look up the name in the Search all plants box for possible translations.';
				break;
		}		
		if ($obj['sciname'] !== $searchSciname) {#we changed it
			$temp['feedback'][] = 'OregonFlora uses ssp. instead of subsp.';
		}
	
	
		$firstArr[] = $temp;
		#echo "<br>";
		#$em->flush();
		#exit;
	}

	#var_dump($firstArr);exit;

	$tids = [];#uses key from $firstArr
	foreach ($firstArr as $key => $entry) {
		if ($entry['code'] == 'Accepted') {#put on list so we can check for dupes
			if (	($this_key = array_search($entry['tid'],$tids)) != false) {#duplicate tid
				if (isset($entry['notes'])) {
					$firstArr[$this_key]['notes'] = array_merge($firstArr[$this_key]['notes'],$entry['notes']);#copy notes to first tid match
				}
				$firstArr[$key]['feedback'][]  = 'This is a duplicate entry for ' . $firstArr[$this_key]['sciname'] . ' and will be removed';
				$firstArr[$key]['code'] = 'Duplicate';
			}else{
				$tids[$key] = $entry['tid'];
			}
		}
	
		if ($entry['code'] == 'Unrecognized') {#do another query to check for x
			$parts = explode(" ",$entry['sciname']);
			array_splice($parts,1,0,'x');
			$secondQuery = $entry['query'];
			$tempSciname = join(' ',$parts);
			$secondQuery->setParameter("search", $tempSciname);
			$secondResults = $secondQuery->getArrayResult();
			if (sizeof($secondResults) > 0) {

				$firstArr[$key]['searchSciname'] = $tempSciname;
				$firstArr[$key]['results'] = $secondResults;
				if ($secondResults[0]['tidaccepted'] === $secondResults[0]['value']) {
					$firstArr[$key]['code'] = 'Accepted';
					$firstArr[$key]['tid'] = $secondResults[0]['value'];
					$firstArr[$key]['tidaccepted'] = $secondResults[0]['value'];
				}else {
					$firstArr[$key]['code'] = 'Synonym';
					$firstArr[$key]['tid'] = $secondResults[0]['value'];
					$firstArr[$key]['tidaccepted'] = $secondResults[0]['tidaccepted'];
				}
			}
		}
	}

	$taxaRepo = $em->getRepository("Taxa");
	#make another pass now that $tids is fully populated
	foreach ($firstArr as $key => $entry) {
		if ($entry['code'] == 'Synonym') {#catch synonym dupes
			if (	($this_key = array_search($entry['tidaccepted'],$tids)) != false) {#synonym is duplicated elsewhere as perfect match
				if (isset($entry['notes'])) {
					$firstArr[$this_key]['notes'] = array_merge($firstArr[$this_key]['notes'],$entry['notes']);#copy notes to first tid match
				}
				$firstArr[$key]['feedback'][]  = 'This is a duplicate entry for ' . $firstArr[$this_key]['sciname'] . ' and will be removed';
				$firstArr[$key]['code'] = 'Duplicate';
			}
		}
		#set vars and check nativity
		if ($firstArr[$key]['code'] == 'Synonym' || $firstArr[$key]['code'] == 'Accepted') {
			#$firstArr[$key]['OFsciname'] = $firstArr[$key]['results'][0]['text'];
			$taxaModel = $taxaRepo->find($entry['tidaccepted']);
			$taxa = TaxaManager::fromModel($taxaModel);
			$firstArr[$key]['OFsciname'] = $taxa->getSciname();
		
			if (!in_array($firstArr[$key]['results'][0]['nativity'],$acceptedNativities)) {#check nativity
				$firstArr[$key]['feedback'][]  = 'This is not a native Oregon plant species and will not be included.';
				$firstArr[$key]['code'] = 'Non-native';
			}else{
				if ($firstArr[$key]['results'][0]['nativity'] == 'native and exotic') {
					$firstArr[$key]['feedback'][]  = 'This taxon has both native and exotic populations in Oregon.';
				}
			}
		}		
	}
	foreach ($firstArr as $key => $entry) {
		unset($firstArr[$key]['query']);#removing for debugging
	}
	#var_dump($firstArr);
	#$csvURL = SPPtoCSV($firstArr);
	
	$result = [
		"status" => (sizeof($firstArr)? "success" : 'notfound'),
		"results" => $firstArr,
		"csvURL"		=> $csvURL
	];
	#$result = $firstArr;
	#var_dump($result);
	#exit;
	return $result;
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

