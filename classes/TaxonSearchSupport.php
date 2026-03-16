<?php
include_once($SERVER_ROOT.'/config/dbconnection.php');
include_once($SERVER_ROOT.'/content/lang/collections/harvestparams.'.$LANG_TAG.'.php');
include_once($SERVER_ROOT.'/classes/OccurrenceTaxaManager.php');

class TaxonSearchSupport{

	private $conn;
	private $queryString;
	private $taxonType;
	private $rankLow = 0;
	private $rankHigh;
	private $oregonTaxa;

 	public function __construct(){
		$this->conn = MySQLiConnectionFactory::getCon('readonly');
 	}

	public function __destruct(){
 		if(!($this->conn === false)) $this->conn->close();
	}

	public function getTaxaSuggest(){
		if($this->rankLow || $this->rankHigh) return $this->getTaxaSuggestByRank();
		else return $this->getTaxaSuggestByType();
	}

	private function getTaxaSuggestByType(){
		$retArr = Array();
		if($this->queryString){
			// Inspired by classes/RpcTaxonomy.php from Taxon Tree Viewer
			// Split queryString into multiple words for wildcard search
			$term = $this->cleanInStr(str: $this->queryString);
			$termArr = explode(' ',$term);
			// Remove query elements that are hybrid/graft indicators (×, †, or "x")
			foreach($termArr as $k => $v){
				// Return the Unicode code point after removing surrounding whitespace
				$ord = mb_ord(trim($v));
				if($ord === 215 || $ord === 8224 || strtolower(trim($v)) === 'x') {
					unset($termArr[$k]);
				}
			}

			// Generate sciname query
			$sciNameQuery = '(t.sciname LIKE "'.$this->queryString.'%" ';
			$sqlFrag = '';
			$termArrLen = count($termArr);
			// Strategy 1: unit1 = "term[0] term[1]", unit2 = <the rest of termArr>  (for two-word unit name)
			if ($termArrLen >= 3) {
				$unit1Combined = $termArr[0] . ' ' . $termArr[1];
				$sqlFrag = 't.unitname1 LIKE "' . $unit1Combined . '%"';
				// Assemble the rest of the contents from termArr
				$unit2Combined = implode(' ', array_slice($termArr, 2));
    			$sqlFrag .= ' AND t.unitname2 LIKE "' . $unit2Combined . '%"';
			}
			// Strategy 2: unit1 = term[0], unit2 = term[1]  (works for simple names)
			else {
				if($unit1 = array_shift($termArr)) $sqlFrag =  't.unitname1 LIKE "'.$unit1.'%" ';
				if($unit2 = array_shift($termArr)) $sqlFrag .=  'AND t.unitname2 LIKE "'.$unit2.'%" ';
			}
			
			if($sqlFrag) $sciNameQuery .= 'OR ('.$sqlFrag.')';
			$sciNameQuery .= ') ';

			$sql = "";
			if($this->taxonType == TaxaSearchType::ANY_NAME){
			    global $LANG;
			    $sql =
			    "SELECT DISTINCT v.tid, CONCAT('".$LANG['SELECT_1-5'].": ',v.vernacularname) AS sciname ".
			    "FROM taxavernaculars v ".
			    // Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON v.tid = cl.tid ' : '').
			    "WHERE v.vernacularname LIKE '%".$this->queryString."%' ".

			    "UNION ".

			    "SELECT DISTINCT t.tid, CONCAT('".$LANG['SELECT_1-2'].": ', t.sciname) AS sciname ".
			    "FROM taxa t ".
			    // Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ' : '').
			    "WHERE ". $sciNameQuery." AND t.rankid > 179 ".
			    ($this->oregonTaxa ? 'AND (cl.clid = 1) ' : '').

			    "UNION ".

			    "SELECT DISTINCT t.tid, CONCAT('".$LANG['SELECT_1-3'].": ', t.sciname) AS sciname ".
			    "FROM taxa t ".
			    // Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ' : '').
			    "WHERE ". $sciNameQuery." AND t.rankid = 140 ".
			    ($this->oregonTaxa ? 'AND (cl.clid = 1) ' : '').

			    "UNION ".

			    "SELECT t.tid, CONCAT('".$LANG['SELECT_1-4'].": ',t.sciname) AS sciname ".
			    "FROM taxa t ".
			    // Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ' : '').
			    "WHERE ". $sciNameQuery. " AND t.rankid > 20 AND t.rankid < 180 AND t.rankid != 140 ".
			    ($this->oregonTaxa ? 'AND (cl.clid = 1) ' : '');

			}
			elseif($this->taxonType == TaxaSearchType::SCIENTIFIC_NAME){
				$sql = 'SELECT t.tid, t.sciname FROM taxa t '.

				// Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ' : '').
				'WHERE ' . $sciNameQuery.
				($this->oregonTaxa ? 'AND (cl.clid = 1) ' : '').
				'LIMIT 30';
			}
			elseif($this->taxonType == TaxaSearchType::FAMILY_ONLY){
				$sql = 'SELECT t.tid, t.sciname FROM taxa t '.

				// Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ' : '').
				'WHERE t.rankid = 140 AND '. $sciNameQuery.
				($this->oregonTaxa ? 'AND (cl.clid = 1) ' : '').
				'LIMIT 30';
			}
			elseif($this->taxonType == TaxaSearchType::TAXONOMIC_GROUP){
				$sql = 'SELECT t.tid, t.sciname FROM taxa t '.

				// Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ' : '').
				'WHERE t.rankid > 20 AND t.rankid < 180 AND '. $sciNameQuery.
				($this->oregonTaxa ? 'AND (cl.clid = 1) ' : '').
				'LIMIT 30';
			}
			elseif($this->taxonType == TaxaSearchType::COMMON_NAME){
				$sql = 'SELECT DISTINCT v.tid, CONCAT(v.vernacularname, " (", t.sciname, ")") AS sciname
					FROM taxavernaculars v INNER JOIN taxa t ON v.tid = t.tid'.

				// Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ' : '').
				'WHERE v.vernacularname LIKE "%'.$this->queryString.'%" '.
				($this->oregonTaxa ? 'AND (cl.clid = 1) ' : '').
				'LIMIT 50';
				//$sql = 'SELECT DISTINCT tid, vernacularname AS sciname FROM taxavernaculars WHERE vernacularname LIKE "%'.$this->queryString.'%" LIMIT 50 ';
			}
			else{
				$sql = 'SELECT t.tid, t.sciname FROM taxa t '. 

				// Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			    ($this->oregonTaxa ? 'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ' : '').
				'WHERE '. $sciNameQuery.
				($this->oregonTaxa ? 'AND (cl.clid = 1) ' : '').
				'LIMIT 20';
			}
			
			$rs = $this->conn->query($sql);
			while ($r = $rs->fetch_object()) {
				$retArr[] = array('id' => $r->tid, 'value' => $r->sciname);
			}
			$rs->free();
		}
		return $retArr;
	}

	private function getTaxaSuggestByRank(){
		$retArr = Array();
		if($this->queryString){
			$sql = 'SELECT sciname FROM taxa ';
			// Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			if ($this->oregonTaxa) $sql .= 'LEFT JOIN `fmchklsttaxalink` as cl ON taxa.tid = cl.tid ';
			$sql .= ' WHERE (sciname LIKE "'.$this->queryString.'%") ';
			if(is_numeric($this->rankLow)){
				if($this->rankHigh) $sql .= 'AND (rankid BETWEEN '.$this->rankLow.' AND '.$this->rankHigh.') ';
				else $sql .= 'AND (rankid = '.$this->rankLow.') ';
			}
			// Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
			if ($this->oregonTaxa) $sql .= 'AND (cl.clid = 1) ';
			$sql .= 'LIMIT 30';
			$rs = $this->conn->query($sql);
			while ($r = $rs->fetch_object()) {
				$retArr[] = $r->sciname;
			}
			$rs->free();
		}
		return $retArr;
	}

	//Setters and getters
	public function setQueryString($queryString){
		//$queryString = $this->cleanInStr($queryString);
		$queryString = preg_replace('/[\+\=@$%]+/i', '', $queryString);
		if(strpos($queryString, ' ')){
			$queryString = str_ireplace(array('"', "'"), '_', $queryString);
			$queryString = preg_replace('/\s{1}x{1}$/i', ' _', $queryString);
			$queryString = preg_replace('/\s{1}x{1}\s{1}/i', ' _ ', $queryString);
			$queryString = str_ireplace(' x ', ' _ ', $queryString);
			$queryString = str_ireplace(' x', ' _', $queryString);
		}
		$this->queryString = $queryString;
	}

	public function setTaxonType($t){
		if(is_numeric($t)) $this->taxonType = $t;
	}

	public function setRankLow($rank){
		if(is_numeric($rank)) $this->rankLow = $rank;
	}

	public function setRankHigh($rank){
		if(is_numeric($rank)) $this->rankHigh = $rank;
	}

	// Restrict to Oregon vascular plant taxa
	public function setOregonTaxa($or){
		if(is_numeric($or)) $this->oregonTaxa = $or;
	}

	//Misc functions
	private function cleanInStr($str){
		$newStr = trim($str);
		$newStr = preg_replace('/\s\s+/', ' ',$newStr);
		$newStr = $this->conn->real_escape_string($newStr);
		return $newStr;
	}
}
?>