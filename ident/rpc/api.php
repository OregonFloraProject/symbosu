<?php

include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/classes/IdentManager.php");
include_once("$SERVER_ROOT/classes/ExploreManager.php");
include_once("$SERVER_ROOT/classes/TaxaManager.php");
include_once("$SERVER_ROOT/classes/InventoryManager.php");

function getEmpty() {
  return [
    "clid" => -1,
    "pid" => -1,
    "dynclid" => -1,
    "projName" => '',
    "title" => '',
    "intro" => '',
    "iconUrl" => '',
    "authors" => '',
    "abstract" => '',
    "taxa" => [],
    "characteristics" => [],
    "lat" => 0,
    "lng" => 0,
    "locality" => '',
    "type" => ''
  ];
}


/**
 * Returns all unique taxa 
 * @params $_GET
 */
function get_data($params) {

	$search = null;
	$results = getEmpty();
	
	if (isset($params["clid"]) && $params["clid"] > -1) {
		$em = SymbosuEntityManager::getEntityManager();
		$repo = $em->getRepository("Fmchecklists");
		$model = $repo->find($params["clid"]);
		$checklist = ExploreManager::fromModel($model);
    if (array_key_exists("pid", $params)) {
      $checklist->setPid($params["pid"]);
      $projRepo = SymbosuEntityManager::getEntityManager()->getRepository("Fmprojects");
      $model = $projRepo->find($params["pid"]);
      $project = InventoryManager::fromModel($model);
      $results["projName"] = $project->getProjname();
    }
		$results["clid"] = $checklist->getClid();
		$results["pid"] = $checklist->getPid();
		$results["title"] = $checklist->getTitle();
		$results["intro"] = ($checklist->getIntro()? $checklist->getIntro() :'') ;
		$results["iconUrl"] = ($checklist->getIconUrl()? $checklist->getIconUrl() :'') ;
		$results["authors"] = ($checklist->getAuthors()? $checklist->getAuthors() :'') ;
		$results["abstract"] = ($checklist->getAbstract()? $checklist->getAbstract() :'') ;
    $results["lat"] = ($checklist->getLatcentroid()? $checklist->getLatcentroid() :'') ;
    $results["lng"] = ($checklist->getLongcentroid()? $checklist->getLongcentroid() :'') ;
    $results["locality"] = ($checklist->getLocality()? $checklist->getLocality() :'') ;
    $results["type"] = ($checklist->getType()? $checklist->getType() :'') ;

	}elseif(isset($params['dynclid']) && $params['dynclid'] > -1) {

		$em = SymbosuEntityManager::getEntityManager();
		$repo = $em->getRepository("Fmdynamicchecklists");
		$model = $repo->find($params["dynclid"]);
		if ($model) {
			$dynamic_checklist = ExploreManager::fromModel($model);
			$results["title"] = $dynamic_checklist->getTitle();
		}
	}
  	
	$identManager = new IdentManager();
	if (isset($params['clid']) && $params['clid'] > -1) $identManager->setClid($params['clid']);
	if (isset($params['dynclid']) && $params['dynclid'] > -1) $identManager->setDynClid($params['dynclid']);
	if (isset($params['taxon'])) $identManager->setTaxonFilter($params['taxon']);
	if (isset($params['rv'])) $identManager->setRelevanceValue($params['rv']);
	$identManager->setAttrsFromParams($params);
		
	if ( 	 ( array_key_exists("search", $params) && !empty($params["search"]) )
			&& ( array_key_exists("name", $params) && in_array($params['name'],array('sciname','commonname')) )
	) {
		$identManager->setSearchTerm($params["search"]);
		$identManager->setSearchName($params['name']);
	}

	$identManager->setThumbnails(true);
	$identManager->setTaxa();
	$results['taxa'] = $identManager->getTaxa();
	$results['totals'] = TaxaManager::getTaxaCounts($results['taxa']);
	$characteristics = $identManager->getCharacteristics();
	#var_dump($characteristics);
	/* for slider chars, create an additional numeric value for charstatenames e.g. 11+ becomes 11
			because slider widgets don't like non-numeric values
	 */
	 #var_dump($characteristics);
	if ($characteristics) {
		foreach ($characteristics as $key => $group) {
			foreach ($group['characters'] as $gkey => $char) {
				if ($char['display'] == 'slider') {
					foreach ($char['states'] as $ckey => $state) {
						$characteristics[$key]['characters'][$gkey]['states'][$ckey]['numval'] = floatval(preg_replace("/[^0-9\.]/","",$state['charstatename']));
					}
				}
			}
		}
		$results['characteristics'] = $characteristics;
	}else{
		$results['characteristics'] = [];
	}

	#ini_set("memory_limit", $memory_limit);
	#set_time_limit(30);
	return $results;
}

$result = [];
#$result = get_data($_GET);


if (
  (
    array_key_exists("clid", $_GET) &&
    is_numeric($_GET["clid"]) &&
    (!array_key_exists("pid", $_GET) || is_numeric($_GET["pid"]))
  )
	|| (array_key_exists("dynclid", $_GET) && is_numeric($_GET["dynclid"]))
) {
	$result = get_data($_GET);
} else {
	#todo: generate error or redirect
}

array_walk_recursive($result,'cleanWindowsRecursive');#replace Windows characters

if (array_key_exists("export", $_GET) && in_array($_GET["export"],array('word','csv','vendorcsv'))) {
	include_once($SERVER_ROOT . "/checklists/checklistexport.php");
	switch($_GET['export']) {
		case 'word':
			exportChecklistToWord($result);
			break;
		case 'csv':
			exportChecklistToCSV($result);
			break;
		case 'vendorcsv':
			exportChecklistToVendorCSV($result);
			break;
	
	}
}else{
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
}

?>
