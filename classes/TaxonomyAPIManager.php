<?php
include_once($SERVER_ROOT.'/config/dbconnection.php');
include_once($SERVER_ROOT.'/classes/TaxonomyUtilities.php');

class TaxonomyAPIManager{

	private $conn;
	private $taxAuthId = 0;
    private $rankLimit = 0;
    private $rankLow = 0;
    private $rankHigh = 0;
    private $limit = 0;
    private $hideAuth = false;
    private $hideProtected = false;
    private $oregonVascPlant = false;
	
	function __construct(){
		$this->conn = MySQLiConnectionFactory::getCon("readonly");
	}

 	public function __destruct(){
		if(!($this->conn === null)) $this->conn->close();
	}

    public function generateSciNameList($queryString){
        $retArr = Array();
        $sql = '';

 	    $sql = 'SELECT DISTINCT t.SciName, t.Author, t.TID '.
            'FROM taxa AS t '.
            // Add whether each taxon belongs to any checklists
            'LEFT JOIN `fmchklsttaxalink` as cl ON t.tid = cl.tid ';
 	    if($this->taxAuthId){
            $sql .= 'INNER JOIN taxstatus AS ts ON t.tid = ts.tid ';
        }
        $sql .= 'WHERE t.SciName LIKE "'.$this->cleanInStr($queryString).'%" ';
        if($this->rankLimit){
            $sql .= 'AND t.RankId = '.$this->rankLimit.' ';
        }
        else{
            if($this->rankLow){
                $sql .= 'AND t.RankId >= '.$this->rankLow.' ';
            }
            if($this->rankHigh){
                $sql .= 'AND t.RankId <= '.$this->rankHigh.' ';
            }
        }
        if($this->taxAuthId){
            $sql .= 'AND ts.taxauthid = '.$this->taxAuthId.' ';
        }
        if($this->hideProtected){
            $sql .= 'AND t.SecurityStatus <> 2 ';
        }
        // Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
        if($this->oregonVascPlant){
            $sql .= 'AND (cl.clid = 1) ';
        }
        if($this->limit){
            $sql .= 'LIMIT '.$this->limit.' ';
        }
        $rs = $this->conn->query($sql);
        while ($r = $rs->fetch_object()){
            $sciName = $r->SciName.($this->hideAuth?'':' '.$r->Author);
            $retArr[$sciName]['id'] = $r->TID;
            $retArr[$sciName]['value'] = $sciName;
            $retArr[$sciName]['author'] = $r->Author;
        }

 	    return $retArr;
    }

    public function generateVernacularList($queryString){
        $retArr = Array();
        $sql = '';

        $sql = 'SELECT DISTINCT v.TID, v.VernacularName '.
            'FROM taxavernaculars AS v '.
            // Add whether each taxon belongs to any checklists
            'LEFT JOIN `fmchklsttaxalink` as cl ON v.TID = cl.tid ';
        $sql .= 'WHERE v.VernacularName LIKE "%'.$this->cleanInStr($queryString).'%" ';
        // Restrict to taxa contained in the State of Oregon vascular plant checklist (clid=1)
        if($this->oregonVascPlant){
            $sql .= 'AND (cl.clid = 1) ';
        }
        if($this->limit){
            $sql .= 'LIMIT '.$this->limit.' ';
        }
        $rs = $this->conn->query($sql);
        while ($r = $rs->fetch_object()){
            $retArr[] = $r->VernacularName;
        }

        return $retArr;
    }
 	
	public function setTaxAuthId($val){
        $this->taxAuthId = $this->cleanInStr($val);
    }

    public function setRankLimit($val){
        $this->rankLimit = $this->cleanInStr($val);
    }

    public function setRankLow($val){
        $this->rankLow = $this->cleanInStr($val);
    }

    public function setRankHigh($val){
        $this->rankHigh = $this->cleanInStr($val);
    }

    public function setLimit($val){
        $this->limit = $this->cleanInStr($val);
    }

    public function setHideAuth($val){
        $this->hideAuth = $this->cleanInStr($val);
    }

    public function setHideProtected($val){
        $this->hideProtected = $this->cleanInStr($val);
    }

    // Added to allow for restricting taxonomy to Oregon vascular plants curated by OregonFlora
    public function getOregonVascPlant(){
        return $this->oregonVascPlant;
    }
    
    // Added to allow for restricting taxonomy to Oregon vascular plants curated by OregonFlora
    public function setOregonVascPlant($bool){
        if($bool) $this->oregonVascPlant = $bool;
    }
	
	protected function cleanInStr($str){
        $newStr = trim($str);
        $newStr = preg_replace('/\s\s+/', ' ',$newStr);
        $newStr = $this->conn->real_escape_string($newStr);
        return $newStr;
    }
}
?>