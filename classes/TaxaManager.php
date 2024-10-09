<?php

include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");

class TaxaManager {

  private static $RANK_GENUS = 180;
  # sortSequence cutoff for images to show on taxon profile page
  private static $IMAGE_SORT_SEQUENCE_CUTOFF = 100;

  # Basic characteristics
  private static $CID_SUNLIGHT = 680;
  private static $CID_MOISTURE = 683;
  private static $CID_SUMMER_MOISTURE = 682;
  private static $CID_WIDTH = 738;
  private static $CID_HEIGHT = 140;
  private static $CID_SPREADS = 739;
  #private static $CID_OTHER_CULT_PREFS = 767;
  
  # Plant features
  private static $CID_FLOWER_COLOR = 612;
  private static $CID_BLOOM_MONTHS = 165;
  private static $CID_WILDLIFE_SUPPORT = 685;
  private static $CID_LIFESPAN = 136;
  private static $CID_FOLIAGE_TYPE = 100;
  private static $CID_PLANT_TYPE = 137;
  
  # Growth & maintenance
  private static $CID_LANDSCAPE_USES = 679;
  private static $CID_CULTIVATION_PREFS = 767;
  private static $CID_BEHAVIOR = 688;
  private static $CID_PROPAGATION = 740;
  private static $CID_EASE_GROWTH = 684;
  
  # Beyond the garden
  private static $CID_HABITAT = 163;
  private static $CID_ECOREGION = 19;

  # Survey & manage (rare plant guide)
  private static $CID_BEST_SURVEY_MONTHS = 633;
  private static $CID_BEST_SURVEY_STATUS = 822;
  private static $CID_THREATS = 823;
  private static $CID_MANAGEMENT_ACTIONS = 824;

  # Context (rare plant guide)
  private static $CID_ELEVATION = 820;

  # Conservation status (rare plant guide)
  private static $CID_CONSERVATION_FED = 242;
  private static $CID_CONSERVATION_STATE = 243;
  private static $CID_CONSERVATION_HERITAGE = 244;

	# from TaxonProfileManager
	private $langArr = array();
	
  // ORM Model
  protected $model;

  protected $basename;
  protected $vernacularNames;
  protected $images;
  protected $characteristics;
  protected $specialChecklists;
  protected $descriptions;
  protected $gardenId;
  protected $gardenDescription;
  protected $synonyms;
  protected $origin;
  protected $family;
  protected $parentTid;
  protected $taxalinks;
  protected $rarePlantFactSheet;
  protected $accessRestricted;
  protected $rankId;
  protected $spp;
  #protected $ambSyn;
  protected $acceptedSynonyms;
  protected $associations;

  public function __construct($tid=-1) {
    if ($tid !== -1) {
      $em = SymbosuEntityManager::getEntityManager();
      $taxaRepo = $em->getRepository("Taxa");
      $this->model = $taxaRepo->find($tid);
      $this->basename = $this->populateBasename();
      //$this->images = TaxaManager::populateImages($this->getTid());
      //$this->checklists = TaxaManager::populateChecklists($this->getTid());
    } else {
      $this->model = null;
      $this->basename = '';
      $this->images = [];
      $this->characteristics = [];
      $this->specialChecklists = [];
      $this->descriptions = [];
      $this->gardenId = -1;
      $this->gardenDescription = '';
      $this->acceptedSynonyms = [];
    }
  }

  public static function fromModel($model) {
    $newTaxa = new TaxaManager();
    $newTaxa->model = $model;
    $newTaxa->basename = $newTaxa->populateBasename();
    //$newTaxa->images = TaxaManager::populateImages($model->getTid());
   // $newTaxa->checklists = TaxaManager::populateChecklists($model->getTid());
    return $newTaxa;
  }
  
	public function setLanguage($lang){
		$lang = strtolower($lang);
		if($lang == 'en' || $lang == 'english') $this->langArr = array('en','english');
		elseif($lang == 'es' || $lang == 'spanish') $this->langArr = array('es','spanish','espanol');
		elseif($lang == 'fr' || $lang == 'french') $this->langArr =  array('fr','french');
	}

  public function getTid() {
    return $this->model->getTid();
  }
  public function getSciname() {
    return $this->model->getSciname();
  }
  public function getAuthor() {
    return $this->model->getAuthor();
  }
  public function getRankId() {
    return $this->model->getRankid();
  }
  public function getVernacularNames() {
    if (!isset($this->vernacularNames)) {
      $vern = $this->model->getVernacularNames()
        ->filter(function($vn) { return strtolower($vn->getLanguage()) === "english"; })
        ->toArray();

      // re-sort to move names with null sortsequence to the end of the list
      // since MySQL sorts NULL ahead of integers
      $firstNullSortSeqKey = 0;
      $nulls = [];
      foreach ($vern as $key => $vn) {
        if ($vn->getSortsequence() !== null) {
          $firstNullSortSeqKey = $key;
          break;
        } else {
          array_push($nulls, $vn);
        }
      }
      if ($firstNullSortSeqKey !== 0) {
        array_splice($vern, 0, $firstNullSortSeqKey);
        $vern = array_merge($vern, $nulls);
      }

      $this->vernacularNames = array_map(function($vn) { return $vn->getVernacularName(); }, $vern);
    }
    return $this->vernacularNames;
  }

  public function getSynonyms() {
  	$this->synonyms = $this->populateSynonyms($this->getTid());
    return $this->synonyms;
  }
  public function getAcceptedSynonyms() {
    $this->acceptedSynonyms = $this->populateAcceptedSynonyms($this->getTid());
    return $this->acceptedSynonyms;
  }
  public function getOrigin() {
  	$this->origin = $this->populateOrigin($this->getTid());
  	return $this->origin;
  }
  public function getFamily() {
  	$this->family = $this->populateFamily($this->getTid());
  	return $this->family;
  }
  public function getParentTid() {
    if (is_null($this->parentTid)) {
      $this->parentTid = $this->populateParentTid($this->getTid());
    }
    return $this->parentTid;
  }
	public function getGardenId() {
  	$this->gardenId = $this->populateGardenId($this->getTid());
		return $this->gardenId;
	}
  public function getTaxalinks() {
    $this->taxalinks = $this->populateTaxalinks($this->getTid());
  	return $this->taxalinks;
  }
  public function getRarePlantFactSheet() {
    $this->taxalinks = $this->populateTaxalinks($this->getTid());
  	return $this->rarePlantFactSheet;
  }
  public function getAccessRestricted() {
    if (is_null($this->accessRestricted)) {
      $this->accessRestricted = $this->populateAccessRestricted($this->getTid());
    }
    return $this->accessRestricted;
  }
  public function getAssociations() {
    if (is_null($this->associations)) {
      $this->associations = $this->populateAssociations($this->getTid());
    }
    return $this->associations;
  }
  public function getBasename() {
    return $this->basename;
  }
  public function getImages() {
    return $this->images;
  }
  public function getImage() {
    return array(array_shift($this->images));
  }
	public function getImagesByBasisOfRecord() {
		$return = array();
		foreach ($this->images as $image) {
			$return[$image['basisofrecord']][] = $image;
		}
		return $return;
	}
  public function getThumbnail() {

    // JGM: 2024-04-19
    // The line below gets the first image populated by populateImages()
    // That in turn does multiple joins and pulls all the images for a taxon
    // This is way overkill for just a thumbnail. Code below is at least more efficient
    // It could be even better, though. 

    //return isset($this->images[0]) ? $this->images[0]["thumbnailurl"] : null;

    // More efficient code
    // Use the images without doing a query, if populated
    if(isset($this->images[0])) {
      return $this->images[0]["thumbnailurl"];
    } else {

      $tids = $this->getImageTids();
      // Get the thumbnail of the first image by sort sequence for a taxon
      $em = SymbosuEntityManager::getEntityManager();
      $thumb = $em->createQueryBuilder()
        ->select(["i.thumbnailurl"])
        ->from("images", "i")
        ->where("i.tid IN (:tids)")
        ->setParameter("tids", $tids)
        ->orderBy("i.sortsequence")
        ->setFirstResult(0)
        ->setMaxResults(1)
        ->getQuery()
        ->execute();

      // Return the thumbnail, if one exists
      if(isset($thumb[0]['thumbnailurl'])) {
        return $thumb[0]['thumbnailurl'];
      } else {
        return null;
      }
    }

  }

  public function getCharacteristics($queryType = 'default') {
    $this->characteristics = self::populateCharacteristics($this->getTid(), $queryType);
    return $this->characteristics;
  }

  public function getSpecialChecklists() {
    return $this->specialChecklists;
  }

  public function getDescriptions() {
  	$this->descriptions = $this->populateDescriptions($this->getTid());
    return $this->descriptions;
  }

  public function getGardenDescription() {
  	$this->gardenDescription = $this->populateGardenDescription($this->getTid());
    return $this->gardenDescription;
  }
  public function getSpp() {
    if (!isset($this->spp)) {
      $this->spp = $this->populateSpp($this->getTid());
    }
    return $this->spp;
  }
  
  public function isGardenTaxa() {
    return array_search(Fmchecklists::$CLID_GARDEN_ALL, $this->specialChecklists) !== false;
  }

  private function populateGardenId() {
  /*
  	If this->tid is in Garden checklist, return it
  	else check if parentId is in garden checklist; if so, return it
  */
  	$return = -1;
  	if ($this->isGardenTaxa()) {
  		$return = $this->getTid();
  	}elseif ($this->getRankId() > 220){
  		$parentId = $this->getParentTid();

			$em = SymbosuEntityManager::getEntityManager();
			$clQuery = $em->createQueryBuilder()
				->select(["cl.clid"])
				->from("Fmchklsttaxalink", "tl")
				->innerJoin("Fmchecklists", "cl", "WITH", "tl.clid = cl.clid")
				->where("tl.tid = :tid")
				->andWhere("cl.parentclid = " . Fmchecklists::$CLID_GARDEN_ALL . " OR cl.clid = " . FmChecklists::$CLID_GARDEN_ALL)
				->setParameter("tid", $parentId)
				->getQuery()
				->execute();

				if (sizeof($clQuery)) {
					$return = $parentId;
				}
  	}
  	
  	return $return;
  }
	private function populateSpp($tid = null) {
		$return = array();
  	if ($tid) {
  		if ($this->getRankId() >= 140) {#less complicated than what's in OSUTaxaManager::setSppData() for now
  	
				$em = SymbosuEntityManager::getEntityManager();
  			#$spp = $taxaRepo->createQueryBuilder("t")
				$spp = $em->createQueryBuilder()
					->select(["t.tid"])#, t.sciname, t.securitystatus
					->from("Taxa", "t")
					->innerJoin("Taxaenumtree", "te", "WITH", "t.tid = te.tid")
					->innerJoin("Taxstatus", "ts", "WITH", "t.tid = ts.tidaccepted")
          // Add whether each taxon belongs to any checklists
          ->leftJoin("fmchklsttaxalink", "cl", "WITH", "t.tid = cl.tid")
					#->where("te.taxauthid = :taxauthid")#this line causes an error on live, but not on my machine; all values are 1 anyway so commenting out
					->andWhere("ts.taxauthid = :taxauthid")
					->andWhere("t.rankid >= :rankid")
					->andWhere("te.parenttid = :tid")
          // Restrict to Oregon vascular plant taxa contained in the State of Oregon checklist curated by OregonFlora (clid=1)
          ->andWhere("cl.clid = 1")
					->orderBy("t.sciname")
					->setParameter(":tid", $tid)
					->setParameter(":taxauthid", 1)
					->setParameter(":rankid", 220)
					->distinct()
					->getQuery()
					->execute();
					$return = $spp;
			}
		}
		return $return;
	}

  private function populateGardenDescription($tid = null) {
  	$return = '';
  	$descriptions = $this->getDescriptions();
  	foreach ($descriptions as $key => $block) {
  		if (strcasecmp($block['caption'],'Gardening with Natives') === 0) {
  			$statement = array_shift($block['desc']);		
  			if (!empty($statement)) {
	  			$return = $statement;
	  		}
  		}
  	}
  	return $return;  
  }
  private function populateDescriptions($tid = null) {
  	$retArr = array();
  	$emdash = html_entity_decode('&#x8212;', ENT_COMPAT, 'UTF-8');
  	if ($tid) {
			$em = SymbosuEntityManager::getEntityManager();
			$rsArr = $em->createQueryBuilder()
				->select(["ts.tid, tdb.tdbid, tdb.caption, tdb.language, tdb.source, tdb.sourceurl, tds.tdsid, tds.heading, tds.statement, tds.displayheader, tdb.tdprofileid"])#
				->from("Taxstatus", "ts")
				->innerJoin("Taxadescrblock", "tdb", "WITH", "ts.tid = tdb.tid")
				->innerJoin("Taxadescrstmts", "tds", "WITH", "tds.tdbid = tdb.tdbid")
				->where("ts.tidaccepted = :tid")
				->andWhere("ts.taxauthid = 1")
				->orderBy("tdb.displaylevel,tds.sortsequence")
				->setParameter("tid", $tid)
				->getQuery()
				->execute();
				#var_dump($rsArr);exit;
				foreach ($rsArr as $idx => $rs) {
					#$rsArr[$idx]['statement'] = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $rsArr[$idx]['statement']);#htmlentities($rs[$idx]['statement']);
					##$rsArr[$idx] =  str_replace($emdash, '(mdash)', $rsArr[$idx]);
					$rsArr[$idx]['statement'] = $rsArr[$idx]['statement'];
					
				}
				
				/* copied from TaxonProfileManager */
        //Get descriptions associated with accepted name only
				$usedCaptionArr = array();
				foreach($rsArr as $n => $rowArr){
					if($rowArr['tid'] == $tid){
						$retArr = $this->loadDescriptionArr($rowArr, $retArr);
						$usedCaptionArr[] = $rowArr['caption'];
					}
				}
				//Then add description linked to synonyms ONLY if one doesn't exist with same caption
				reset($rsArr);
				foreach($rsArr as $n => $rowArr){
					if($rowArr['tid'] != $tid && !in_array($rowArr['caption'], $usedCaptionArr)){
						$retArr = $this->loadDescriptionArr($rowArr, $retArr);
					}
				}
        $ret = sizeof($retArr)? array_values($retArr[0]) : [];#I don't know what situation would require the whole array, so this for now
		}
		#var_dump($retArr);
		if (sizeof($ret)) {
    	return $ret;
		}else{
			return [];
		}
   	#return $retArr;
    /*
    $result = "";
    if (count($stmts) > 0) {
      // Somebody must've copied & pasted from Word or something
      $result = mb_convert_encoding($stmts[0]["statement"], "UTF-8", "Windows-1252");
      if (!$result) {
        return $stmts[0]["statement"];
      }
    }*/
  }
  /* copied from TaxonProfileManager */
	private function loadDescriptionArr($rowArr,$retArr){
		$indexKey = 0;
		#if(!in_array(strtolower($rowArr['language']), $this->langArr)){
		#	$indexKey = 1;
		#}
		if(!isset($retArr[$indexKey]) || !array_key_exists($rowArr['tdbid'],$retArr[$indexKey])){
			$retArr[$indexKey][$rowArr['tdbid']]["caption"] = $rowArr['caption'];
			$retArr[$indexKey][$rowArr['tdbid']]["profile"] = $rowArr['tdprofileid'];
			$retArr[$indexKey][$rowArr['tdbid']]["source"] = $rowArr['source'];
			$retArr[$indexKey][$rowArr['tdbid']]["url"] = $rowArr['sourceurl'];
			$retArr[$indexKey][$rowArr['tdbid']]["desc"] = [];
		}
		/**
		 * json_enocde does NOT necessarily preserve array order; using `tdsid` as the key for the
		 * `desc` array will cause it to be sorted by `tdsid`, rather than `tds.sortsequence` as we
		 * intended.
		 *
		 * To keep the correct order, we use array_push instead, which is ok as we're not relying on
		 * the `tdsid` values anywhere in JS.
		 */
		array_push($retArr[$indexKey][$rowArr['tdbid']]["desc"], ($rowArr['displayheader'] && $rowArr['heading']?"<b>".$rowArr['heading']."</b>: ":"").$rowArr['statement']);
		return $retArr;
	}
  
  private static function populateSpecialChecklists($tid) {
    global $RPG_FLAG;
    $em = SymbosuEntityManager::getEntityManager();
    $clQuery = $em->createQueryBuilder()
      ->select(["cl.clid"])
      ->from("Fmchklsttaxalink", "tl")
      ->innerJoin("Fmchecklists", "cl", "WITH", "tl.clid = cl.clid")
      ->where("tl.tid = :tid")
      ->andWhere("cl.parentclid = " . Fmchecklists::$CLID_GARDEN_ALL . " OR cl.clid = " . FmChecklists::$CLID_GARDEN_ALL .  ($RPG_FLAG ? " OR cl.clid = " . FmChecklists::$CLID_RARE_ALL : ""))
      ->setParameter("tid", $tid);

    return array_map(
      function ($cl) { return $cl["clid"]; },
      $clQuery->getQuery()->execute()
    );
  }
  

  public function setSpecialChecklists() {
    $this->specialChecklists = self::populateSpecialChecklists($this->getTid());
  }

  private function populateAccessRestricted($tid = null) {
    if ($tid) {
      // restrict access for all species on the OR rare plant checklist ($CLID_RARE_OR)
      // as well as subspecies of those species
      $tidsToCheck = [$tid];
      if ($this->getRankId() > 220) {
        $parentId = $this->getParentTid();
        if ($parentId) {
          $tidsToCheck[] = $parentId;
        }
      }
      $em = SymbosuEntityManager::getEntityManager();
      $matchingRows = $em->createQueryBuilder()
        ->select(["count(tl.tid)"])
        ->from("Fmchklsttaxalink", "tl")
        ->where("tl.tid IN (:tids)")
        ->andWhere("tl.clid = " . Fmchecklists::$CLID_RARE_OR)
        ->setParameter("tids", $tidsToCheck)
        ->getQuery()
				->getSingleScalarResult();

      if ($matchingRows > 0) {
        global $USER_RIGHTS;
        if (!isset($USER_RIGHTS) || (
          !array_key_exists('SuperAdmin', $USER_RIGHTS) &&
          !array_key_exists('CollAdmin', $USER_RIGHTS) &&
          !array_key_exists('RareSppAdmin', $USER_RIGHTS) &&
          !array_key_exists('RareSppReadAll', $USER_RIGHTS)
        )) {
          // TODO: check for specific collection IDs with CollEditor and RareSppReader?
          return 1;
        }
      }
      return 0;
    }
  }

  private function populateAssociations($tid = null) {
    $return = [];
    if ($tid) {
      $em = SymbosuEntityManager::getEntityManager();
      $associations = $em->createQueryBuilder()
        ->select(["ta.relationship", "ta.tidassociate", "COALESCE(t.sciname, ta.verbatimsciname) sciname", "ta.notes"])
        ->from("Taxonassociations", "ta")
        ->leftJoin("Taxa", "t", "WITH", "t.tid = ta.tidassociate")
        ->where("ta.tid = :tid")
        ->orderBy("sciname")
        ->setParameter("tid", $tid)
        ->getQuery()
        ->execute();

      foreach($associations as $row) {
        $type = $row["relationship"];
        unset($row["relationship"]);
        if ($type === "associatedWith") {
          unset($row["notes"]);
        }
        $return[$type][] = $row;
      }
    }
    return $return;
  }

  private static function getEmptyCharacteristics($queryType = 'default') {
    if ($queryType === 'rare') {
      return [
        "habitat" => [],
        "ecoregion" => [],
        "elevation" => [],
        "bloom_months" => [],

        # Survey & manage
        "best_survey_months" => [],
        "best_survey_status" => null,
        "threats" => [],
        "management" => [],

        "conservation_status" => [
          "federal" => null,
          "state" => null,
          "heritage" => null
        ]
      ];
    } else if ($queryType === 'garden') {
      return [
        "height" => [],
        "width" => [],
        "sunlight" => [],
        "moisture" => [],
        "summer_moisture" => [],

        # Features
        "flower_color" => [],
        "bloom_months" => [],
        "wildlife_support" => [],
        "lifespan" => [],
        "foliage_type" => [],
        "plant_type" => [],

        # Beyond the garden
        "ecoregion" => [],
        "habitat" => [],

        "growth_maintenance" => [
          "landscape_uses" => [],
          "cultivation_preferences" => [],
          "behavior" => [],
          "propagation" => [],
          "ease_of_growth" => [],
          "spreads_vigorously" => null,
          //"other_cult_prefs" => []
        ]
      ];
    }
    return [];
  }
  private function populateAcceptedSynonyms($tid) {
  	$return = [];
    $em = SymbosuEntityManager::getEntityManager();
    $acceptedSynonyms = $em->createQueryBuilder()
      ->select(["t.sciname", "t.tid", "t2.sciname as synname","ts.tidaccepted"])
      ->from("taxstatus", "ts")
      ->innerJoin("taxa", "t", "WITH", "ts.tid = t.tid")
      ->leftJoin("taxa", "t2", "WITH", "ts.tidaccepted = t2.tid")
      ->andWhere("t.tid = :tid")
      ->andWhere("ts.taxauthid = 1")
      ->setParameter("tid", $tid)
      ->orderBy("synname")->getQuery()->execute();
    foreach ($acceptedSynonyms as $acceptedSynonym) {
      if ($acceptedSynonym['sciname'] != $acceptedSynonym['synname']) {
        $return[$acceptedSynonym['tidaccepted']] = array("sciname" => $acceptedSynonym['synname']);
      }
    } 
    $taxaRepo = SymbosuEntityManager::getEntityManager()->getRepository("Taxa");
    foreach ($return as $tid => $arr) {
      $taxaModel = $taxaRepo->find($tid);
      $taxa = self::fromModel($taxaModel);
      $return[$tid]['vernacular'] = [
        "basename" => $taxa->getBasename(),
        "names" => $taxa->getVernacularNames()
      ];
    }
    return $return;
  
  }
  private function populateSynonyms($tid) {
    $em = SymbosuEntityManager::getEntityManager();
    $synonyms = $em->createQueryBuilder()
      ->select(["t.sciname", "t.author", "t.tid"])
      ->from("taxstatus", "ts")
      ->innerJoin("taxa", "t", "WITH", "ts.tid = t.tid")
      ->innerJoin("fmchklsttaxalink", "f", "WITH", "f.tid = ts.tid")
      ->where("ts.tidaccepted = :tid")
      ->andWhere("t.tid != :tid")
      ->andWhere("ts.taxauthid = 1")
      ->andWhere("f.clid = 1")
      ->andWhere("ts.sortsequence < 90")
      ->setParameter("tid", $tid)
      ->orderBy("ts.sortsequence, t.sciname")->getQuery()->execute();
    /*$synonyms = array_map(
      function ($sy) { return $sy["sciname"] . " " . $sy["author"]; },
      $synonyms->getQuery()->execute()
    );*/
    	#$synonyms = array_map("cleanWindows",$synonyms);
    return $synonyms;
  }
  private function populateOrigin($tid = null){
  	$return = null;
  	if ($tid) {
			$em = SymbosuEntityManager::getEntityManager();
			$origin = $em->createQueryBuilder()
				->select(["ctl.nativity"])
				->from("Fmchklsttaxalink", "ctl")
				->where("ctl.tid = :tid")
				->andWhere("ctl.clid = 1")
				->setParameter("tid", $tid)
      	->getQuery()
      	->execute();
      if (isset($origin[0])) {
				$return = $origin[0]['nativity'];
			}
		}
		return $return;
 	}
 	private function populateFamily($tid = null) {
  	$return = null;
  	if ($tid) {
			$em = SymbosuEntityManager::getEntityManager();
			$status = $em->createQueryBuilder()
				->select(["ts.family"])
				->from("Taxstatus", "ts")
				->where("ts.tidaccepted = :tid")
				->setParameter("tid", $tid)
      	->getQuery()
      	->getArrayResult();
      	#var_dump($status);
      if (sizeof($status)) {
				$return = $status[0]['family'];
			}
		}
		return $return;
 	} 	
 	
 	private function populateParentTid($tid = null) {
  	$return = null;
  	if ($tid) {
			$em = SymbosuEntityManager::getEntityManager();
			$status = $em->createQueryBuilder()
				->select(["ts.parenttid"])
				->from("Taxstatus", "ts")
				->innerJoin("Taxa","t","WITH","ts.tid = t.tid")
				->leftJoin("Taxa","t2","WITH","ts.tidaccepted = t2.tid")
				->where("t.tid = :tid")
				->andWhere("ts.taxauthid = 1")
				->setParameter("tid", $tid)
      	->getQuery()
      	->execute();
      if (sizeof($status)) {
				$return = $status[0]['parenttid'];
			}
		}
		return $return;
 	}
 	
  private function populateTaxalinks($tid = null){
  	$return = null;
  	$this->rarePlantFactSheet = '';
  	if ($tid) {
			$em = SymbosuEntityManager::getEntityManager();
			$expr = $em->getExpressionBuilder();
			$links = $em->createQueryBuilder()
				->select(["tl.url","tl.title"])
				->from("Taxalinks", "tl")
				->where(
								$expr->orX(
									$expr->eq("tl.tid",":tid"),
									$expr->in(
										'tl.tid',
										$em->createQueryBuilder()
											->select("te.parenttid")
											->from("Taxaenumtree","te")
											->where("te.tid = :tid")
											->andWhere("te.taxauthid = :taxauthid")
											->getDQL()
									)
								)
				)
				->setParameter("tid", $tid)
				->setParameter("taxauthid", 1)
      	->orderBy("tl.sortsequence,tl.title")
      	->getQuery()
      	->execute();
		}

  	foreach ($links as $idx => $arr) {
  		if (strcasecmp($arr['title'],"Rare Plant Fact Sheet") === 0) {
  			$this->rarePlantFactSheet = $arr['url'];
  			unset($links[$idx]);
  		}
  	}
  	$links = array_values($links);
  	return $links;
 	}
 	
  private static function populateCharacteristics($tid, $queryType) {
    $cids = TaxaManager::getAllCids($queryType);
    $em = SymbosuEntityManager::getEntityManager();
    $attributeQuery = $em->createQueryBuilder()
      ->select(["d.cid", "d.cs", "s.charstatename"])
      ->from("Kmdescr", "d")
      ->innerJoin("Kmcs", "s", "WITH", "(d.cid = s.cid AND d.cs = s.cs)")
      ->innerJoin("Kmcharacters", "c", "WITH", "d.cid = c.cid")
      ->where("d.tid = :tid");
    $attributeQuery = $attributeQuery
      ->andWhere($attributeQuery->expr()->in("d.cid", $cids))
      ->setParameter("tid", $tid);

    $attribs = $attributeQuery->getQuery()->execute();
    $attr_array = TaxaManager::getEmptyCharacteristics($queryType);
    foreach ($attribs as $attrib) {
      $attr_key = $attrib["cid"];
      $attr_val = $attrib["charstatename"];

      // Since cs values are stored as strings rather than numbers in mysql, they sometimes arrive
      // sorted incorrectly (e.g. 10, 11, 7, 8, 9 instead of 7, 8, 9, 10, 11). For characteristics
      // where the order matters (generally numeric values and months), we include the cs in
      // $attr_array so that JS can sort the states correctly.
      $attr_cs = $attrib["cs"];

      switch ($attr_key) {
        case TaxaManager::$CID_HEIGHT:
          $attr_array["height"][$attr_cs] = $attr_val;
          break;
        case TaxaManager::$CID_WIDTH:
          $attr_array["width"][$attr_cs] = $attr_val;
          break;
        case TaxaManager::$CID_SUNLIGHT:
          array_push($attr_array["sunlight"], $attr_val);
          break;
        case TaxaManager::$CID_MOISTURE:
          array_push($attr_array["moisture"], $attr_val);
          break;
        case TaxaManager::$CID_SUMMER_MOISTURE:
          array_push($attr_array["summer_moisture"], $attr_val);
          break;
        case TaxaManager::$CID_FLOWER_COLOR:
          array_push($attr_array["flower_color"], $attr_val);
          break;
        case TaxaManager::$CID_BLOOM_MONTHS:
          $attr_array["bloom_months"][$attr_cs] = $attr_val;
          break;
        case TaxaManager::$CID_WILDLIFE_SUPPORT:
          array_push($attr_array["wildlife_support"], $attr_val);
          break;
        case TaxaManager::$CID_LIFESPAN:
          array_push($attr_array["lifespan"], $attr_val);
          break;
        case TaxaManager::$CID_FOLIAGE_TYPE:
          array_push($attr_array["foliage_type"], $attr_val);
          break;
        case TaxaManager::$CID_PLANT_TYPE:
          array_push($attr_array["plant_type"], $attr_val);
          break;
        case TaxaManager::$CID_LANDSCAPE_USES:
          array_push($attr_array["growth_maintenance"]["landscape_uses"], $attr_val);
          break;
        case TaxaManager::$CID_CULTIVATION_PREFS:
          array_push($attr_array["growth_maintenance"]["cultivation_preferences"], $attr_val);
          break;
        case TaxaManager::$CID_BEHAVIOR:
          array_push($attr_array["growth_maintenance"]["behavior"], $attr_val);
          break;
        case TaxaManager::$CID_PROPAGATION:
          array_push($attr_array["growth_maintenance"]["propagation"], $attr_val);
          break;
        case TaxaManager::$CID_EASE_GROWTH:
          array_push($attr_array["growth_maintenance"]["ease_of_growth"], $attr_val);
          break;
        case TaxaManager::$CID_SPREADS:
          $attr_array["growth_maintenance"]["spreads_vigorously"] = $attr_val;
          break;
        #case TaxaManager::$CID_OTHER_CULT_PREFS:
        #  array_push($attr_array["growth_maintenance"]["other_cult_prefs"], $attr_val);
        #  break;
        case TaxaManager::$CID_ECOREGION:
          array_push($attr_array["ecoregion"], $attr_val);
          break;
        case TaxaManager::$CID_HABITAT:
          array_push($attr_array["habitat"], $attr_val);
          break;
        case TaxaManager::$CID_BEST_SURVEY_MONTHS:
          $attr_array["best_survey_months"][$attr_cs] = $attr_val;
          break;
        case TaxaManager::$CID_BEST_SURVEY_STATUS:
          $attr_array["best_survey_status"] = $attr_val;
          break;
        case TaxaManager::$CID_THREATS:
          array_push($attr_array["threats"], $attr_val);
          break;
        case TaxaManager::$CID_MANAGEMENT_ACTIONS:
          array_push($attr_array["management"], $attr_val);
          break;
        case TaxaManager::$CID_ELEVATION:
          $attr_array["elevation"][$attr_cs] = $attr_val;
          break;
        case TaxaManager::$CID_CONSERVATION_FED:
          $attr_array["conservation_status"]["federal"] = $attr_val;
          break;
        case TaxaManager::$CID_CONSERVATION_STATE:
          $attr_array["conservation_status"]["state"] = $attr_val;
          break;
        case TaxaManager::$CID_CONSERVATION_HERITAGE:
          $attr_array["conservation_status"]["heritage"] = $attr_val;
          break;
        default:
          break;
      }
    }

    foreach (["width", "height"] as $k) {
      if (in_array($k, $cids) && count($attr_array[$k]) > 1) {
        $tmp = [min($attr_array[$k]), max($attr_array[$k])];
        $attr_array[$k] = $tmp;
      }
    }

    return $attr_array;
  }

  /**
   * Utility function that returns an array of tids to use when selecting images. For bare species,
   * we want to include all subspecies and order by sortSequence across all images, rather than just
   * those associated with the bare species tid.
   *
   * We avoid doing this for higher taxa since their profile pages don't include tid-associated
   * images anyway.
   */
  private function getImageTids() {
    if ($this->getRankId() > self::$RANK_GENUS) {
      $spp = $this->getSpp();
      return [$this->getTid(), ...$spp];
    }
    return [$this->getTid()];
  }

  private function populateImages($tid) {
    $tids = $this->getImageTids();
    $em = SymbosuEntityManager::getEntityManager();
    $images = $em->createQueryBuilder()
      ->select(["i.imgid, i.thumbnailurl", "i.url", "i.photographer", "i.owner", "i.copyright", "i.notes","o.occid","o.year", "o.month", "o.day","o.country","o.stateprovince","o.county","o.locality","o.recordedby","o.basisofrecord","c.collectionname"])#
      ->from("Images", "i")
      ->innerJoin("omoccurrences","o","WITH","i.occid = o.occid")
      ->innerJoin("omcollections","c","WITH","c.collid = o.collid")
      ->where("i.tid IN (:tids)")
      ->andWhere("i.sortsequence < " . self::$IMAGE_SORT_SEQUENCE_CUTOFF)
      ->setParameter("tids", $tids)
      ->orderBy("i.sortsequence")
      ->getQuery()
      ->execute();
    
    $images = array_map("TaxaManager::processImageData",$images);
    /*
    #getimagesize is too slow here
    foreach ($images as $key => $image) {
    	list($width, $height) = getimagesize($image['url']);
    	echo $width;
    }*/
    $return = $images;
    return $return;
  }
  public function setImages() {
    $this->images = $this->populateImages($this->getTid());
  }

  /**
   * When the application only needs the first image of the set (e.g. for subspecies on a genus
   * page), we can save a lot of time and memory by only selecting a single image from mysql.
   *
   * This is similar to getThumbnail() but selects a fuller set of data about the image.
   */
  private function populateSingleImage($tid) {
    $tids = $this->getImageTids();
    $em = SymbosuEntityManager::getEntityManager();
    $images = $em->createQueryBuilder()
      ->select(["i.imgid, i.thumbnailurl", "i.url", "i.photographer", "i.owner", "i.copyright", "i.notes","o.occid","o.year", "o.month", "o.day","o.country","o.stateprovince","o.county","o.locality","o.recordedby","o.basisofrecord","c.collectionname"])
      ->from("Images", "i")
      ->innerJoin("omoccurrences","o","WITH","i.occid = o.occid")
      ->innerJoin("omcollections","c","WITH","c.collid = o.collid")
      ->where("i.tid IN (:tids)")
      ->setParameter("tids", $tids)
      ->orderBy("i.sortsequence")
      ->setFirstResult(0)
      ->setMaxResults(1)
      ->getQuery()
      ->execute();
    return array_map("TaxaManager::processImageData", $images);
  }
  public function setSingleImage() {
    $this->images = $this->populateSingleImage($this->getTid());
  }
  
  private static function processImageData($img) {
  		foreach ($img as $field => $value) {
  			if ($field == 'thumbnailurl' || $field == 'url') {
  				$img[$field] = resolve_img_path($value);
  			}elseif( $field == 'year') {
  				$img['fulldate'] = '';
  				$datestamp = null;
  				if ($value == '' && !empty($img['notes'])){#Photographed: Aug 9, 2008 or Photographed: date unknown
						$date = str_replace("Photographed: ",'',$img['notes']);
						$datestamp = strtotime($date);
					}else{
						if (!empty($img['day']) && !empty($img['month'])) {
							$datestamp = strtotime($img['year'] . '-' . $img['month'] . '-' . $img['day']);
						}
					}
					if ($datestamp) {
							$img['fulldate'] = date("F j, Y",$datestamp);#displayed in modal slideshow
					}
  			}
  		}
  		return $img;
  }

  private function populateBasename() {
    $basename = '';
    $baseNameCandidates = $this->model->getVernacularNames()
      ->filter(function($vn) { return strtolower($vn->getLanguage()) === "basename"; });

    if ($baseNameCandidates->count() > 0) {
      $basename = $baseNameCandidates->first()->getVernacularname();
    }
    return $basename;
  }
  
  
#    	global $LANG_TAG;
 #   var_dump($LANG_TAG);
  private static function getAllCids($queryType = 'default') {
    if ($queryType === 'rare') {
      return [
        # Context
        TaxaManager::$CID_HABITAT,
        TaxaManager::$CID_ECOREGION,
        TaxaManager::$CID_ELEVATION,
        TaxaManager::$CID_BLOOM_MONTHS,

        # Survey & manage
        TaxaManager::$CID_BEST_SURVEY_MONTHS,
        TaxaManager::$CID_BEST_SURVEY_STATUS,
        TaxaManager::$CID_THREATS,
        TaxaManager::$CID_MANAGEMENT_ACTIONS,

        # Conservation status
        TaxaManager::$CID_CONSERVATION_FED,
        TaxaManager::$CID_CONSERVATION_STATE,
        TaxaManager::$CID_CONSERVATION_HERITAGE
      ];
    } else if ($queryType === 'garden') {
      return [
        # Basic characteristics
        TaxaManager::$CID_SUNLIGHT,
        TaxaManager::$CID_MOISTURE,
        TaxaManager::$CID_SUMMER_MOISTURE,
        TaxaManager::$CID_WIDTH,
        TaxaManager::$CID_HEIGHT,

        # Plant features
        TaxaManager::$CID_FLOWER_COLOR,
        TaxaManager::$CID_BLOOM_MONTHS,
        TaxaManager::$CID_WILDLIFE_SUPPORT,
        TaxaManager::$CID_LIFESPAN,
        TaxaManager::$CID_FOLIAGE_TYPE,
        TaxaManager::$CID_PLANT_TYPE,

        # Growth & maintenance
        TaxaManager::$CID_LANDSCAPE_USES,
        TaxaManager::$CID_CULTIVATION_PREFS,
        TaxaManager::$CID_BEHAVIOR,
        TaxaManager::$CID_PROPAGATION,
        TaxaManager::$CID_EASE_GROWTH,
        TaxaManager::$CID_SPREADS,
        #TaxaManager::$CID_OTHER_CULT_PREFS,

        # Beyond the garden
        TaxaManager::$CID_HABITAT,
        TaxaManager::$CID_ECOREGION,
      ];
    }
    return [];
  }

	public static function getEmptyTaxon() {
		return [
			"tid" => -1,
			"sciname" => '',
			"author" => '',
			"parentTid" => -1,
			"rankId" => -1,
			"descriptions" => [],
			"gardenDescription" => '',
			"gardenId" => -1,
			#"images" => [],
			"imagesBasis" => [],
			"vernacular" => [
				"basename" => '',
				"names" => []
			],
			"synonyms" => [],
			"acceptedSynonyms" => [],
			"taxalinks" => [],
			"rarePlantFactSheet" => '',
			"origin"	=> '',
			"family" 	=> '',
			"characteristics" => [],
			"spp" => [],
		];
	}
	public static function getTaxaCounts($taxa) {
		$families = array();
		$genera = array();
		$species = array();
		#$taxa = array();
	
		foreach ($taxa as $idx => $taxon) {
			$families[] = $taxon['family'];
			$sciArr = explode(" ",$taxon['sciname']);
			if (isset($sciArr[0])) {
				$genera[] = $sciArr[0];
			}
			if (isset($sciArr[1])) {
				$species[] = $sciArr[0] . " " . $sciArr[1];
			}
		}
		$families = array_unique($families);
		$genera = array_unique($genera);
		$species = array_unique($species);
		return array(
			"families"	=> sizeof($families),
			"genera"		=> sizeof($genera),
			"species"		=> sizeof($species),
			"taxa"			=> sizeof($taxa)
		);
	}

  
}

?>