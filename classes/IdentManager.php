<?php

include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/config/SymbosuEntityManager.php");

class IdentManager extends Manager {
/*
	# from TaxonProfileManager
	private $langArr = array();
	
  // ORM Model
  protected $model;
*/
  protected $clid;
  protected $pid;
  protected $dynClid;
  protected $clType;
	protected $attrs = array();
  protected $displayMode;
  protected $taxonFilter;
  protected $taxa;
  protected $relevanceValue = .9;
  private   $currQuery;
  protected $searchTerm;
  protected $searchName = 'sciname';
  protected $searchSynonyms = false;
  protected $IDsOnly = false;
  protected $showThumbnails = false;
  /*
  protected $basename;
  protected $images;
  protected $characteristics;
  protected $descriptions;
  protected $gardenId;
  protected $gardenDescription;
  protected $synonyms;
  protected $origin;
  protected $family;
  protected $parentTid;
  protected $taxalinks;
  protected $rarePlantFactSheet;
  protected $rankId;
  protected $spp;
*/

	function __construct(){
		parent::__construct(null,'readonly');
	}

	function __destruct(){
		parent::__destruct();
	}
/*
  public static function fromModel($model) {
    $newTaxa = new TaxaManager();
    $newTaxa->model = $model;
    $newTaxa->basename = $newTaxa->populateBasename();
    $newTaxa->images = TaxaManager::populateImages($model->getTid());
    $newTaxa->characteristics = TaxaManager::populateCharacteristics($model->getTid());
    $newTaxa->checklists = TaxaManager::populateChecklists($model->getTid());
    $newTaxa->descriptions = $newTaxa->populateDescriptions($model->getTid());
    $newTaxa->gardenDescription = $newTaxa->populateGardenDescription($model->getTid());
    $newTaxa->spp = $newTaxa->populateSpp($model->getTid());
    return $newTaxa;
  }
  */
  
  public function setPid($pid) {
  	$this->pid = intval($pid);
  }
  public function setClid($clid) {
  	$this->clid = intval($clid);
  }
  public function setDynClid($dynClid) {
  	$this->dynClid = intval($dynClid);
  }
  public function setClType($clType) {
  	$this->clType = $clType;
  }
  public function setAttrs($attrs) {
  	$this->attrs = $attrs;
  }
  public function setTaxonFilter($taxonFilter) {
  	$this->taxonFilter = $taxonFilter;
  }
  public function setRelevanceValue($rv) {
  	$this->relevanceValue = $rv;
  }
  public function setSearchTerm($term) {
  	$this->searchTerm = $term;
  }
  public function setSearchName($name = '') {
  	#if (in_array($name,array('sciname','commonname'))) {
  		$this->searchName = $name;
  	#}
  }
  
  public function setThumbnails($bool = false) {
  	$this->showThumbnails = ($bool == true? true: false);
  }
  public function getThumbnails() {
  	return $this->showThumbnails;
  }
  public function setIDsOnly($bool = false) {
  	$this->IDsOnly = ($bool == true? true: false);
  }
  public function getIDsOnly() {
  	return $this->IDsOnly;
  }
  public function setTaxa() {
  	$leftJoins = array();
  	$innerJoins = array();
  	$wheres = array(); 
  	$params = array();
  	$orderBy = array();
  	$results = null;
		$newResults = array();
		$em = SymbosuEntityManager::getEntityManager();
  	
  	if ($this->clid || $this->dynClid) {
			$qb = $em->createQueryBuilder();
			$selects = ["t.tid"];
			if ($this->IDsOnly == false) {
				$selects = array_merge($selects,["ts.family","t.sciname","ts.parenttid","v.vernacularname","v.language","t.author"]);
			}
			$leftJoins[] = array("Taxavernaculars","v","WITH","t.tid = v.tid");
			$innerJoins[] = array("Taxstatus","ts","WITH","t.tid = ts.tid");
			$wheres[] = $qb->expr()->orX(
											$qb->expr()->eq('v.language',"'English'"),
											$qb->expr()->eq('v.language',"'Basename'")
										);
			
			#$wheres[] = "v.sortsequence = 1";#causes basename to disappear
			$wheres[] = "ts.taxauthid = 1";
			$groupBy = [
				"v.vernacularname",
			];
			$orderBy[] = "ts.family";
			$orderBy[] = "t.sciname";
			$orderBy[] = "v.sortsequence";
			
			if ($this->searchTerm != '' && $this->searchName != '') {
				switch($this->searchName) {
					case 'commonname':
						$innerJoins[] = array("Taxavernaculars", "tv", "WITH", "t.tid = tv.tid");
						$params[] = array(":search",'%' . $this->searchTerm . '%');
						if ($this->searchSynonyms) {
							$wheres[] = $qb->expr()->orX(
									$qb->expr()->like('tv.vernacularname',':search'),
									$qb->expr()->in(
																"ts.tidaccepted",#array(1,2,3))
																$em->createQueryBuilder()
																	->select("ts2.tidaccepted")
																	->from("Taxavernaculars","v2")
																	->innerJoin("Taxstatus","ts2","WITH","v2.tid = ts2.tid")
																	->where('v2.vernacularname LIKE :search')
																	->getDQL()
																)
							);
						}else{
							$wheres[] = "v.vernacularname LIKE :search";
						}
						break;
					case 'sciname':
						$params[] = array(":search",'%' . $this->searchTerm . '%');
						if ($this->searchSynonyms) {
							$wheres[] = $qb->expr()->orX(
									$qb->expr()->like('t.sciname',':search'),
									$qb->expr()->in(
																"ts.tidaccepted",#array(1,2,3))
																$em->createQueryBuilder()
																	->select("ts2.tidaccepted")
																	->from("Taxa","t2")
																	->innerJoin("Taxstatus","ts2","WITH","t2.tid = ts2.tid")
																	->where('t2.sciname LIKE :search')
																	->getDQL()
																)
							);
						}else{
							$wheres[] = "t.sciname LIKE :search";
						}
						break;
				}
			}elseif($this->searchTerm != '') {#sciname or commonname is unspecified - used on Natives
				
				$innerJoins[] = array("Taxavernaculars", "tv", "WITH", "t.tid = tv.tid");
				$wheres[] = $qb->expr()->orX(
											$qb->expr()->like('t.sciname',':search'),
											$qb->expr()->like('tv.vernacularname',':search')
										);
				$params[] = array(":search",'%' . $this->searchTerm . '%');
			}
			
			#ATTRs including vendors						
			$lookups = self::getVendorLookups();
			$clidLookup = $lookups->clidLookup;
			$childLookup = $lookups->childLookup;
			$vendorClids = [];
			#var_dump($clidLookup);
			if (sizeof($this->attrs)) {
        $count = 0;
				foreach ($this->attrs as $cid => $states) {
					$count++;
					$alias = 'D' . $count;#create a unique alias for each join
					if ($cid == getNurseryCid()) {#vendors to be added below with clids
						#var_dump($states);
						foreach ($states as $state) {
							$vendorClids[] = $clidLookup[$state];
						}
						#var_dump($sclid);
					}else{
						$innerJoins[] = array("Kmdescr","{$alias}","WITH","t.tid = {$alias}.tid");
						$wheres[] = "{$alias}.cid = :{$alias}cid";
						$wheres[] = "{$alias}.cs IN (" . join(",",$states) . ")";
						$params[] = array(":{$alias}cid",$cid);
					}
				}
			}
			
			#var_dump($this->attrs);
			#var_dump($vendorClids);
			
			if ($this->dynClid) {
				$innerJoins[] = array("Fmdyncltaxalink","clk","WITH","t.tid = clk.tid");
				$wheres[] = "clk.dynclid = :dynclid";
				$params[] = array("dynclid",$this->dynClid);

			}else{
				if ($this->clType == 'dynamic') {#not finished/not in use?
					$innerJoins[] = array("Omoccurrences","o","WITH","t.tid = o.TidInterpreted");
					#wheres[] = $this->dynamicSQL;
				}else{
					/*
						Vendor checklists can have trinomials, but the Natives checklist (54) doesn't, so we want to catch and display those trinomials' parent binomials
						Leaving this open to any request that provides nursery attrs for now, which theoretically could include other clids besides 54
					*/
					$innerJoins[] = array("Fmchklsttaxalink","clk","WITH","t.tid = clk.tid");
					$params[] = array("clid",$this->clid);
					if ($vendorClids) {//check both 54 and vendor checklist, and also handle trinomials
						$innerJoins[] = array("Fmchklsttaxalink","clk2","WITH","t.tid = clk2.tid");
						
						$wheres[] = $qb->expr()->orX(
													$qb->expr()->andX(//binomials
														$qb->expr()->eq('t.rankid',"220"),
				 										$qb->expr()->in('clk2.clid',$vendorClids),
														$qb->expr()->eq('clk.clid',":clid"),
													),
													$qb->expr()->in('t.tid',//trinomials
														$em->createQueryBuilder()
															->select('subt.tid')
															->from("Taxa","subt")
															->innerJoin("Taxaenumtree","subte1","WITH","subt.tid = subte1.parenttid")
															->innerJoin("Fmchklsttaxalink","subclk3","WITH","subt.tid = subclk3.tid")
															->innerJoin("Fmchklsttaxalink","subclk4","WITH","subte1.tid = subclk4.tid")
															->andWhere('subclk3.clid = :clid')//binomial is in 54
															->andWhere('subclk4.clid IN (' . join(",",$vendorClids) . ')')//trinomial is in vendor checklist
															->setParameter(":clid",$this->clid)//NOTE: not literally 54, but the clid value, which right now is only 54
															->getDQL()													
													)
												);
						
					}else{//default
						//$wheres[] = "t.rankid = 220";
						$wheres[] = "clk.clid = :clid";
					}
				}
			}
			if (!empty($this->taxonFilter) && $this->taxonFilter != "All Species") {
				$wheres[] = $qb->expr()->orX(
											$qb->expr()->eq('ts.family',':taxon'),
											$qb->expr()->eq('t.unitname1',':taxon')
										);
				$params[] = array("taxon",$this->taxonFilter);
			}

			#var_dump($innerJoins);
			#var_dump($wheres);
			#var_dump($params);
			#exit;
			#set EM
			$taxa = $em->createQueryBuilder()
				->select($selects)
				->from("Taxa","t")
			;
			foreach ($leftJoins as $leftJoin) {
				$taxa->leftJoin(...$leftJoin);
			}
			foreach ($innerJoins as $innerJoin) {
				$taxa->innerJoin(...$innerJoin);
			}
			if (sizeof($wheres)) {
				foreach ($wheres as $where) {
					$taxa->andWhere($where);
				}
			}
			foreach ($params as $param) {
				$taxa->setParameter(...$param);
			}
			$taxa->distinct();
			#$taxa->groupBy(join(", ",$groupBy));
			$taxa->orderBy(join(",",$orderBy));
			$tquery = $taxa->getQuery();
			#var_dump($this->searchName);
			#var_dump($tquery->getSQL());
			#var_dump($tquery->getParameters());exit;
			$this->currQuery = $tquery;
			$results = $tquery->getResult();

			$currSciName = '';
			$currIdx = null;
			#var_dump($results);exit;

			foreach ($results as $idx => $result) {

				if (isset($result['sciname']) && $result['sciname'] == $currSciName) {
					if (strtolower($result['language']) == 'basename') {
						$newResults[$currIdx]['vernacular']['basename'] = $result['vernacularname'];
					}elseif(strtolower($result['language']) == 'english' && !in_array($result['vernacularname'],$newResults[$currIdx]['vernacular']['names'])) {
						$newResults[$currIdx]['vernacular']['names'][] = $result['vernacularname'];
					}
				}else{
					$newResults[$idx] = $result;
					if ($this->IDsOnly == false) {
						$newResults[$idx]['author'] = $result['author'];
						$newResults[$idx]['vernacular']['basename'] = '';
						$newResults[$idx]['vernacular']['names'] = [];
						if (strtolower($result['language']) == 'basename') {
							$newResults[$idx]['vernacular']['basename'] = $result['vernacularname'];
						}elseif(strtolower($result['language']) == 'english' && !in_array($result['vernacularname'],$newResults[$idx]['vernacular']['names'])) {
							$newResults[$idx]['vernacular']['names'][] = $result['vernacularname'];
						}
						unset($newResults[$idx]['vernacularname']);
						unset($newResults[$idx]['language']);
						$currSciName = $result['sciname'];
						$currIdx = $idx;
					}
				}
			}#end foreach $results	
		}
		$this->taxa = array_values($newResults);
		if ($this->getThumbnails()) {
			$this->setThumbnailUrls();
		}
		$em->flush();
  }

	/**
	 * Get the URL of the thumbnail to display for each taxon in $this->taxa. This is done per-taxon
	 * rather than as a single query in order to take advantage of LIMIT 1 and avoid having to pull
	 * all images for each taxon. Thumbnail URLs are inserted under key 'image' in $this->taxa.
	 */
	private function setThumbnailUrls() {
		if (!empty($this->taxa)) {
			$em = SymbosuEntityManager::getEntityManager();
			foreach ($this->taxa as $idx => $taxa) {
				$qb = $em->createQueryBuilder();
				$thumb = $qb->select(['i.thumbnailurl'])
					->from('images', 'i')
					->where('i.tid = :tid')
					->setParameter('tid', $taxa['tid'])
					->andWhere($qb->expr()->isNotNull('i.thumbnailurl'))
					->orderBy('i.sortsequence')
					->setFirstResult(0)
					->setMaxResults(1)
					->getQuery()
					->execute();

				if (isset($thumb[0]['thumbnailurl'])) {
					$this->taxa[$idx]['image'] = resolve_img_path($thumb[0]['thumbnailurl']);
				}
			}
		}
	}
  
	public function getCharacteristics() {
		if (!empty($this->taxa)) {
			$taxa_tids = array_column($this->taxa,"tid");
			$charList = array();
			$em = SymbosuEntityManager::getEntityManager();
			$qb = $em->createQueryBuilder();
			#$em3 = $em->createQueryBuilder();
			$chars = $em->createQueryBuilder()
				->select(['t.tid, descr.cid'])
				->from("Taxa","t")
				->innerJoin("Kmdescr","descr","WITH","t.tid = descr.tid")
				->where("descr.cs <> '-'")
				->andWhere($qb->expr()->in('t.tid',$taxa_tids))
				->distinct()
			;
			$cquery = $chars->getQuery();
			$results = $cquery->execute();
			
			#grouping and checking against relevanceValue in PHP b/c I can't find a way to do it in Doctrine
			$counts = array();
			foreach ($results as $result) {
				if (isset($counts[$result['cid']])){
					$counts[$result['cid']]++;
				}else{
					$counts[$result['cid']] = 1;
				}
			}
			$countMin = sizeof($this->taxa) * $this->relevanceValue;
			$cids = array();
			$loopCount = 0;
			while (empty($cids) && $loopCount++ < 10) {
				foreach ($counts as $cid => $count) {
					if ($count > $countMin) {
						$cids[] = $cid;
					}
				}
				$countMin = $countMin*0.9;
			}
			$cids = array_unique(array_merge($cids,array_keys($this->attrs)));
			
			$cresults = self::getCharQuery($taxa_tids,$cids);
			$results = [];

			foreach ($cresults as $cres) {
			#var_dump($cres);
				if (	($key = array_search($cres['hid'],array_column($results,'hid'))) === false) {
					$tmp = [
						'headingname' => $cres['headingname'],
						'hid' => $cres['hid'],
						'characters' => []
					];
					$results[] = $tmp;
				}
				$key = array_search($cres['hid'],array_column($results,'hid'));
				if (	($skey = array_search($cres['cid'],array_column($results[$key]['characters'],'cid'))) === false) {
					$tmp = [
						'charname' => $cres['charname'],
						'cid' => $cres['cid'],
						'display' => $cres['display'],
						'units' => $cres['units'],
						'states' => []
					];
					$results[$key]['characters'][] = $tmp;
				}
		
				$skey = array_search($cres['cid'],array_column($results[$key]['characters'],'cid'));				

				$tmp = [
					'cid' => $cres['cid'],
					'cs' => $cres['cs'],
					'charstatename' => $cres['charstatename']
				];
				$results[$key]['characters'][$skey]['states'][] = $tmp;
		
			}
			#var_dump($results);
			#exit;
			return $results;
			#IN(4835,5242,5665,6117)
		}
	}
	/**
	 * Analogous to getCharacteristics, but returns char data structured in a custom way (defined by
	 * $charStructure), ignoring the heading fields and structure defined by mysql. Used by the garden
	 * and rare APIs, which define their own filter structure.
	 */
	public static function getCharacteristicsForStructure($charStructure,$tids) {
		$cids = [];
		foreach ($charStructure as $idx => $group) {
			foreach ($group['characters'] as $gidx => $char) {
				if (empty($charStructure[$idx]['characters'][$gidx]['states'])) {
					$cids[] = $char['cid'];
				}
			}
		}
		$cresults = self::getCharQuery($tids,$cids);

		// On the garden page, extra keys must be set on the vendor characteristics. Prepare this data
		// if either relevant cid is part of the requested structure (i.e. we are on the garden page).
		$clidLookup = [];
		$childLookup = [];
		if (in_array(getRegionCid(), $cids) || in_array(getNurseryCid(), $cids)) {
			$lookups = self::getVendorLookups();
			$clidLookup = $lookups->clidLookup;
			$childLookup = $lookups->childLookup;
		}

		foreach ($cresults as $cs) {
			foreach ($charStructure as $idx => $group) {
				foreach ($group['characters'] as $gidx => $char) {
					if ($char['cid'] == $cs['cid']) {
						$tmp = [];
						$tmp['cid'] = $char['cid'];
						$tmp['charstatename'] = $cs['charstatename'];
						$tmp['cs'] = $cs['cs'];
						$tmp['numval'] = floatval(preg_replace("/[^0-9\.]/","",$tmp['charstatename']));

						// Custom keys for vendor chars (garden page only)
						if (getRegionCid() == $char['cid']) {
							if ($childLookup[$cs['cs']]) {
								$tmp['children'] = $childLookup[$cs['cs']];
							}
						}
						if (getNurseryCid() == $char['cid']) {
							if (isset($clidLookup[$cs['cs']])) {
								$tmp['clid'] = $clidLookup[$cs['cs']];
								$tmp['pid'] = Fmchecklists::$PID_VENDOR_ALL;
							}
						}

						$charStructure[$idx]['characters'][$gidx]['states'][] = $tmp;
					}
				}
			}
		}

		return $charStructure;
	}
	private static function getCharQuery($tids,$cids) {
		$em = SymbosuEntityManager::getEntityManager();
		$qb = $em->createQueryBuilder();
		$selects = [
			"descr.tid",
			"chars.cid",
			"cs.cs",
			"cs.charstatename",
			"cs.description as csdescr",
			"chars.charname",
			"chars.description as chardescr",
			"chars.helpurl",
			"chars.difficultyrank",
			"chars.display",
			"chars.units",
			"chars.defaultlang",
			"Count(cs.cs) as ct",
			"chead.hid",
			"chead.headingname"
		];
		$groupBy = [
			"chead.language", 
			"cs.cid", 
			"cs.cs", 
			"cs.charstatename", 
			"chars.charname", 
			"chead.headingname", 
			"chars.helpurl",
			"chars.difficultyrank", 
			"chars.defaultlang", 
			"chars.chartype"
		];
		$having = [
			$qb->expr()->andX(
				$qb->expr()->eq('chead.language',':lang'),
				$qb->expr()->in('cs.cid',":cids"),
				$qb->expr()->neq('cs.cs',":cs"), 
				$qb->expr()->orX(
					$qb->expr()->eq('chars.chartype',":UM"),
					$qb->expr()->eq('chars.chartype',":OM")
				),
				$qb->expr()->lt('chars.difficultyrank',":difficultyrank")
			)
		];
		$chars = $em->createQueryBuilder()
			->select($selects)
			->from("Kmdescr","descr")
			->innerJoin("Kmcs","cs","WITH",$qb->expr()->andX(
										$qb->expr()->eq('descr.cs','cs.cs'),
										$qb->expr()->eq('descr.cid','cs.cid')
									))
			->innerJoin("Kmcharacters","chars","WITH","chars.cid = cs.cid")
			->innerJoin("Kmcharheading","chead","WITH","chars.hid = chead.hid")
			->andWhere($qb->expr()->in('descr.tid',':tids'))
			->setParameter(":tids",$tids)
			->setParameter(":cids",$cids)
			->setParameter(":lang","English")
			->setParameter(":difficultyrank",3)
			->setParameter(":cs",'-')
			->setParameter(":UM",'UM')
			->setParameter(":OM",'OM')
			->groupBy(join(", ",$groupBy))
			->having(join(", ",$having))
			->orderBy("chead.sortsequence, chars.sortsequence, chars.charname, cs.sortsequence, cs.charstatename")
			->distinct()
		;
		$cquery = $chars->getQuery();
		#var_dump($cquery->getSQL());
		#var_dump($cquery->getParameters());
		$cresults = $cquery->execute();
		return $cresults;
	}
	public function getAttrs() {
		return $this->attrs;
	}
	public function getTaxa() {
		return $this->taxa;
	}
	/**
	 * Utility function that parses our search param format (used by the ident, garden, and rare API
	 * endpoints) and sets the correct attrs on this instance of IdentManager.
	 */
	public function setAttrsFromParams($params) {
		$em = SymbosuEntityManager::getEntityManager();
		$attrs = array();
		if (isset($params['attr'])) {
			foreach ($params['attr'] as $attr) {
				if (strpos($attr,'-') !== false) {
					$fragments = explode("-",$attr);
					$cid = intval($fragments[0]);
					$cs = intval($fragments[1]);
					if (is_numeric($cid) && is_numeric($cs)) {
						$attrs[$cid][] = $cs;
					}
				}
			}
		}
		/*
			The "range" param doesn't exist in Symbiota, but using attr[] causes unacceptably long URLs;
			So we use range for purposes of building the API URL,
			and here convert it to attrs for the DB call.
			***This relies on kmcs.cs and kmcs.charstatename being in the SAME SORT ORDER (which Katie assures me is always true)***
		*/
		if (isset($params['range'])) {
			$ranges = array();
			foreach ($params['range'] as $range) {
				if (strpos($range,'-') !== false) {
					$fragments = explode("-",$range);
					$cid = intval($fragments[0]);
					$type = $fragments[1];
					$cs = intval($fragments[2]);#cancelled for now: for min/max this is cs, but for i(ncrement), it's the increment val
					if (is_numeric($cid) && !empty($cs) && in_array($type,array("n","x"))) {#,"i"
						$ranges[$cid][$type] = $cs;
					}
				}
			}
			$charStateRepo = $em->getRepository("Kmcs");
			foreach ($ranges as $cid => $range) {
				$csQuery = $charStateRepo->findBy(["cid" => $cid], ["sortsequence" => "ASC"]);
				$csArr = array_map(function($cs) { return intval($cs->getCs()); }, $csQuery);
				foreach ($csArr as $_cs) {
					if ($_cs >= $range['n'] && $_cs <= $range['x']) {
						$attrs[$cid][] = $_cs;
					}
				}
			}
		}
		return $this->setAttrs($attrs);
	}
	/* This could go elsewhere */
	public static function getVendorLookups() {
		$em = SymbosuEntityManager::getEntityManager();
		/**
		 * 2024-10-04(eric): as far as I understand, Fmchklstprojlink.sortSequence is co-opted here to
		 * store the char state value for that particular checklist (used for vendor checklists on the
		 * garden page).
		 */
		$vendor = $em->createQueryBuilder()
			->select(['proj.clid','proj.sortSequence','chil.clidChild'])#
			->from("Fmchklstprojlink","proj")
			->leftJoin("Fmchklstchildren","chil","WITH","proj.clid = chil.clid")	
			->where("proj.pid = :pid")
			->setParameter(":pid",Fmchecklists::$PID_VENDOR_ALL)
		;

		$vquery = $vendor->getQuery();
		$vresults = $vquery->execute();
	
		$cisLookup = [];
		$clidLookup = [];
		foreach ($vresults as $vres) {
			if (!isset($cisLookup[$vres['clid']])) {
				$cisLookup[$vres['clid']] = $vres['sortSequence'];
			}
		}
		foreach ($vresults as $vres) {
			if (isset($vres['clidChild']) && $vres['clidChild'] != NULL && isset($cisLookup[$vres['clidChild']])) {
				$childLookup[$vres['sortSequence']][] = $cisLookup[$vres['clidChild']];
			}elseif($vres['clidChild'] == null){#must not be a parent
				$clidLookup[$vres['sortSequence']] = $vres['clid'];
			}
		}

		$ret = new StdClass();
		$ret->childLookup = $childLookup;
		$ret->clidLookup = $clidLookup;
		return $ret;
	}
}

?>