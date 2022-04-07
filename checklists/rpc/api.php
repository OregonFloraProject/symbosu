<?php
include_once("../../config/symbini.php");

include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/classes/ExploreManager.php");
include_once("$SERVER_ROOT/classes/InventoryManager.php");
include_once("$SERVER_ROOT/classes/TaxaManager.php");

$result = [];

function getEmpty() {
  return [
    "clid" => -1,
    "projName" => '',
    "title" => '',
    "intro" => '',
    "iconUrl" => '',
    "authors" => '',
    "abstract" => '',
    "latcentroid" => 0,
    "longcentroid" => 0,
    'locality'=>'',
		'publication'=>'',
		'notes'=>'',
		'pointradiusmeters'=> 0,
    "taxa" => []
  ];
}

function buildResult($checklistObj) {

  $result = getEmpty();

  if ($checklistObj !== null) {
  	if ($checklistObj->getPid() > -1) {
			$projRepo = SymbosuEntityManager::getEntityManager()->getRepository("Fmprojects");					
  		$model = $projRepo->find($checklistObj->getPid());
  		$project = InventoryManager::fromModel($model);
  		$result["projName"] = $project->getProjname();
  	}
  	  	
    $result["clid"] = $checklistObj->getClid();
    $result["title"] = $checklistObj->getTitle();
    $result["intro"] = ($checklistObj->getIntro()? $checklistObj->getIntro() :'') ;
    $result["iconUrl"] = ($checklistObj->getIconUrl()? $checklistObj->getIconUrl() :'') ;
    $result["authors"] = ($checklistObj->getAuthors()? $checklistObj->getAuthors() :'') ;
    $result["abstract"] = ($checklistObj->getAbstract()? $checklistObj->getAbstract() :'') ;
    $result["locality"] = ($checklistObj->getLocality()? $checklistObj->getLocality() :'') ;
    $result["publication"] = ($checklistObj->getPublication()? $checklistObj->getPublication() :'') ;
    $result["notes"] = ($checklistObj->getNotes()? $checklistObj->getNotes() :'') ;
    $result["pointradiusmeters"] = ($checklistObj->getPointRadius()? $checklistObj->getPointRadius() :'') ;
    $result["latcentroid"] = ($checklistObj->getLatcentroid()? $checklistObj->getLatcentroid() :'') ;
    $result["longcentroid"] = ($checklistObj->getLongcentroid()? $checklistObj->getLongcentroid() :'') ;
    $taxa = $checklistObj->getTaxa(); 
    if (sizeof($taxa)) {
			$taxaRepo = SymbosuEntityManager::getEntityManager()->getRepository("Taxa");					
			$vouchers = $checklistObj->getVouchers();
			foreach($taxa as $rowArr){
				$taxaModel = $taxaRepo->find($rowArr['tid']);
				$taxa = TaxaManager::fromModel($taxaModel);
				$tjresult = [];
				$tjresult['tid'] = $taxa->getTid();
				$tjresult['family'] = $taxa->getFamily();
				$tjresult['author'] = $taxa->getAuthor();
				$tjresult['thumbnail'] = $taxa->getThumbnail();
				$tjresult["vernacular"] = [
					"basename" => $taxa->getBasename(),
					"names" => $taxa->getVernacularNames()
				];
				$tjresult['synonyms'] = $taxa->getSynonyms();
				#var_dump($vouchers);
				$tjresult['vouchers'] = $vouchers[$rowArr['tid']];
				$tjresult['sciname'] = $taxa->getSciname();
				/*if (sizeof(explode(" ",$tjresult['sciname'])) == 1) {
					$tjresult['sciname'] .= " sp.";#the old code does this, but Katie says it's unnecessary
				}*/
				$result["taxa"][] = $tjresult;
			}
			
			
		}
		$result['totals'] = TaxaManager::getTaxaCounts($result['taxa']);
  }
  return $result;
}
function updateInfo($em,$model) {
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
		if (isset($_GET[$field]) && method_exists($model,$function)) {
			$model->$function($_GET[$field]);
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
function updateSPP() {
	$result = [];
	$success = 0;
	$error = 0;
	
	if (array_key_exists("spp", $_GET) && array_key_exists("action", $_GET) ) {
		if ($_GET['action'] == 'add') {
			foreach ($_GET['spp'] as $tid) {
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
					$repo->setClid($_GET['clid']);
					$repo->setInitialtimestamp(new \DateTime());
					$em->merge($repo);#persist
					$em->flush();
					$success++;
				}
				catch (UniqueConstraintViolationException $e) {
					#SymbosuEntityManager::resetManager();
				}
			}
		}elseif($_GET['action'] == 'delete') {
			foreach ($_GET['spp'] as $tid) {
  			$em = SymbosuEntityManager::getEntityManager();
  			$repo = $em->getRepository("Fmchklsttaxalink");
				$link = $repo->find([
					'tid' => $tid,
					'clid' => $_GET['clid'],
					'morphospecies' => ''
				]);
				$em->remove($link);
				$em->flush();
				$success++;
			}
		}
	}
	$result = [
		"success" => $success
	];
	#var_dump($result);
	#exit;
	return $result;
}

$result = [];

if (array_key_exists("clid", $_GET) && is_numeric($_GET["clid"])&& array_key_exists("pid", $_GET) && is_numeric($_GET["pid"])) {
  $em = SymbosuEntityManager::getEntityManager();
  $repo = $em->getRepository("Fmchecklists");
  $model = $repo->find($_GET["clid"]);
  $checklist = ExploreManager::fromModel($model);
  if ($_GET["pid"] > -1) {
	  $checklist->setPid($_GET["pid"]);
	}
  
  if (array_key_exists("update", $_GET)) {
  	switch ($_GET['update']) {
  		case 'info':
				$result = updateInfo($em,$model);
				break;
			case 'spp':
				$result = updateSPP($em);
				break;
		}		
	}else{
  
		if ( 	 ( array_key_exists("search", $_GET) && !empty($_GET["search"]) )
				&& ( array_key_exists("name", $_GET) && in_array($_GET['name'],array('sciname','commonname')) )
		) {
			$checklist->setSearchTerm($_GET["search"]);
			$checklist->setSearchName($_GET['name']);
		
			$synonyms = (isset($_GET['synonyms']) && $_GET['synonyms'] == 'on') ? true : false;
			$checklist->setSearchSynonyms($synonyms);
		}
		#$test = $checklist->getPid();
		#var_dump($test);
		$result = buildResult($checklist);
	}
}else{
	#todo: generate error or redirect
}
// Begin View


array_walk_recursive($result,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);





?>

