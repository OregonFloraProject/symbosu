<?php
include_once("../../config/symbini.php");

include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/classes/ExploreManager.php");
include_once("$SERVER_ROOT/classes/InventoryManager.php");
include_once("$SERVER_ROOT/classes/TaxaManager.php");
include_once("$SERVER_ROOT/classes/IdentManager.php");

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
		'notes'=>'',
    "lat" => 0,
    "lng" => 0,
    "taxa" => [],
    "tids" => []
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
				$tjresult['checklistNotes'] = $rowArr['checklistNotes'];
				/*if (sizeof(explode(" ",$tjresult['sciname'])) == 1) {
					$tjresult['sciname'] .= " sp.";#the old code does this, but Katie says it's unnecessary
				}*/
				$result["taxa"][] = $tjresult;
			}
			foreach ($result["taxa"] as $taxon) {#flatten tids into an array
				$result['tids'][] = $taxon['tid'];
			}			
		}
		$result['totals'] = TaxaManager::getTaxaCounts($result['taxa']);
  }
  return $result;
}

function buildDynResult($dynclid) {

  $result = getEmpty();

	$em = SymbosuEntityManager::getEntityManager();
	$repo = $em->getRepository("Fmdynamicchecklists");
	$model = $repo->find($dynclid);
	$dynamic_checklist = ExploreManager::fromModel($model);
  if ($dynamic_checklist !== null) {
  	
		$result["title"] = $dynamic_checklist->getTitle();
		$result["abstract"] = '';
		$result["authors"] = '';
		$result["projName"] = '';
		$a = explode(" ",$result['title']);#parse the lat/lng out of the title - why are there no fields for lat/lng?
		$result['lat'] = is_numeric($a[0])? floatval($a[0]) : '';
		$result['lng'] = is_numeric($a[1])? floatval($a[1]) : '';

		$identManager = new IdentManager();
		$identManager->setDynClid($dynclid);
	
		if (	array_key_exists("search", $_GET) && !empty($_GET["search"])	) {
			$identManager->setSearchTerm($_GET["search"]);
			//$identManager->setIDsOnly(true);
			if (	array_key_exists("name", $_GET) && !empty($_GET["name"])	) {
				$identManager->setSearchName($_GET["name"]);			
			}			
		}

		$identManager->setTaxa();
		$result["taxa"] = $identManager->getTaxa(); 
    if (sizeof($result["taxa"])) {
			$taxaRepo = SymbosuEntityManager::getEntityManager()->getRepository("Taxa");					
			#$vouchers = $dynamic_checklist->getVouchers();
			for ($i = 0; $i < sizeof($result["taxa"]); $i++){
				$taxaModel = $taxaRepo->find($result["taxa"][$i]['tid']);
				$taxa = TaxaManager::fromModel($taxaModel);
				$result["taxa"][$i]['thumbnail'] = $taxa->getThumbnail();
				#$result["taxa"][$i]['vouchers'] = $vouchers[$result["taxa"][$i]['tid']];
			}
		}
		foreach ($result["taxa"] as $taxon) {#flatten tids into an array
			$result['tids'][] = $taxon['tid'];
		}
		$result['totals'] = TaxaManager::getTaxaCounts($result['taxa']);

  }
  return $result;
}

$result = [];
if (array_key_exists("clid", $_GET) && $_GET["clid"] > -1 && array_key_exists("pid", $_GET) && $_GET["pid"] > -1) {
  $em = SymbosuEntityManager::getEntityManager();
  $repo = $em->getRepository("Fmchecklists");
  $model = $repo->find($_GET["clid"]);
  $checklist = ExploreManager::fromModel($model);
  if ($_GET["pid"] > -1) {
	  $checklist->setPid($_GET["pid"]);
	}
  
	if ( 	 ( array_key_exists("search", $_GET) && !empty($_GET["search"]) )
			&& ( array_key_exists("name", $_GET) && in_array($_GET['name'],array('sciname','commonname')) )
	) {
		$checklist->setSearchTerm($_GET["search"]);
		$checklist->setSearchName($_GET['name']);
		
		$synonyms = (isset($_GET['synonyms']) && $_GET['synonyms'] == 'on') ? true : false;
		$checklist->setSearchSynonyms($synonyms);
	}
	$result = buildResult($checklist);

}elseif(array_key_exists("dynclid", $_GET) && $_GET["dynclid"] > -1) {
	$dynclid = $_GET["dynclid"];
	$result = buildDynResult($dynclid);

	
}else{
	#todo: generate error or redirect
}
// Begin View


array_walk_recursive($result,'cleanWindowsRecursive');#replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);



