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
				$repo->setNotes(join(', ',$obj->notes));
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

function SPPtoCSV($results) {
	global $CLIENT_ROOT, $SERVER_ROOT;
	
	$url = '';
	if (sizeof($results)) {
		$url = $CLIENT_ROOT . '/temp/downloads/vendor/' . uniqid() . '.csv';
		#var_dump($CLIENT_ROOT);exit;
		$filename = $SERVER_ROOT . $url;
		$fp = fopen($filename, 'w');
		if ($fp) {
			fputcsv($fp,["Your sciname","Result","OF sciname","Feedback"]);
			foreach ($results as $result) {
				$temp = [$result['searchSciname'],$result['code'],$result['OFsciname'],join("; ",$result['feedback'])];
				fputcsv($fp, $temp);
			}
			fclose($fp);
		}
	}
	return $url;
}

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
		$link->setNotes($_REQUEST['notes']);
		$em->merge($link);#persist
		$em->flush();
		$success++;
	}
	$result = [
		"status" => $success
	];

	return $result;
}


function previewSPP() {
	$result = [];
	$success = 0;
	$error = 0;
	$csvURL = '';

	$CLID_GARDEN_ALL = 54;
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
	#join on natives checklist
	$em = SymbosuEntityManager::getEntityManager();
	$expr = $em->getExpressionBuilder();
	$q = $em->createQueryBuilder();
	foreach ($arr as $key => $obj) {
		$temp = [];
		$temp['sciname'] = $obj['sciname'];
		$temp['notes'] = [];
		if (isset($obj['notes'])) {
			$temp['notes'][] = $obj['notes'];
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
		}elseif ($sciNameResults[0]['tidaccepted'] === $sciNameResults[0]['value']) {
			$temp['code'] = 'Accepted';
			$temp['tid'] = $sciNameResults[0]['value'];
			$temp['tidaccepted'] = $sciNameResults[0]['value'];
		}else {
			$temp['code'] = 'Synonym';
			$temp['tid'] = $sciNameResults[0]['value'];
			$temp['tidaccepted'] = $sciNameResults[0]['tidaccepted'];
			$temp['feedback'][] = 'This is a synonym for another species (see Oregon Flora sciname)';
		}
		#check for ambiguous - Mimulus guttatus, Convolvulus sepium
		if ($temp['tid'] != $temp['tidaccepted']) {
			$tidaccepteds = [];
			foreach ($sciNameResults as $res) {
				$tidaccepteds[$res['tidaccepted']] = null;
			}
			if (sizeof($tidaccepteds) > 1) {
				$temp['code'] = 'Ambiguous';
			}
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
					$firstArr[$this_key]['notes'][]  = $entry['notes'];#copy notes to first tid match
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
			$firstArr[$key]['searchSciname'] = join(' ',$parts);
			$secondQuery->setParameter("search", $firstArr[$key]['searchSciname']);
			$secondResults = $secondQuery->getArrayResult();
			if (sizeof($secondResults) > 0) {

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
					$firstArr[$this_key]['notes'][]  = $entry['notes'];#copy notes to first tid match
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
				$firstArr[$key]['feedback'][]  = 'This is not a native Oregon plant species';
				$firstArr[$key]['code'] = 'Non-native';
			}
		}		
	}
	foreach ($firstArr as $key => $entry) {
		unset($firstArr[$key]['query']);#removing for debugging
	}
	
	$csvURL = SPPtoCSV($firstArr);
	
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

$result = [];

if (array_key_exists("clid", $_REQUEST) && is_numeric($_REQUEST["clid"])&& array_key_exists("pid", $_REQUEST) && is_numeric($_REQUEST["pid"])) {
  $em = SymbosuEntityManager::getEntityManager();
  $repo = $em->getRepository("Fmchecklists");
  $model = $repo->find($_REQUEST["clid"]);
  $checklist = ExploreManager::fromModel($model);
  if ($_REQUEST["pid"] > -1) {
	  $checklist->setPid($_REQUEST["pid"]);
	}
  
  if (array_key_exists("update", $_REQUEST)) {
  
		if($IS_ADMIN || (array_key_exists("ClAdmin",$USER_RIGHTS) && in_array($_REQUEST["clid"],$USER_RIGHTS["ClAdmin"]))){
			$isEditor = true;
		}
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
}else{
	#todo: generate error or redirect
}
// Begin View


array_walk_recursive($result,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);





?>

