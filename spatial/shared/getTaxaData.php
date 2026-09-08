<?php
include_once($SERVER_ROOT.'/classes/SpatialModuleManager.php');

function getTaxaData ($taxonNames, $taxontype, $useThes) {
    $con = MySQLiConnectionFactory::getCon("readonly");
    $spatialManager = new SpatialModuleManager();
    $taxaArr = array();

    try {
        foreach ($taxonNames as $name) {
            if (is_numeric($name)) {
                $sql = 'SELECT sciname FROM taxa WHERE (TID = '.(int)$name.')';
                $rs = $con->query($sql);
                if($row = $rs->fetch_object()){
                    $taxaStr = $row->sciname;
                    if($taxaStr) $taxaArr[$taxaStr] = array();
                }
                $rs->close();
            }
            else {
                if($taxontype != 5) $name = ucfirst($name);
                $taxaArr[$name] = array();
            }
        }

        if ($taxontype == 5) {
            $sql = "SELECT DISTINCT v.VernacularName, t.tid, t.sciname, ts.family, t.rankid ".
                "FROM (taxstatus AS ts INNER JOIN taxavernaculars AS v ON ts.TID = v.TID) ".
                "INNER JOIN taxa AS t ON t.TID = ts.tidaccepted ";
            $whereStr = "";
            foreach ($taxaArr as $key => $value) {
                $whereStr .= "OR v.VernacularName = '".$con->real_escape_string($key)."' ";
            }
            $sql .= "WHERE (ts.taxauthid = 1) AND (".substr($whereStr,3).") ORDER BY t.rankid LIMIT 20";
            $result = $con->query($sql);
            if ($result && $result->num_rows) {
                while ($row = $result->fetch_object()) {
                    $vernName = strtolower($row->VernacularName);
                    if ($row->rankid < 140) {
                        if(!isset($taxaArr[$vernName]['tid'])){
                            $taxaArr[$vernName]['tid'] = array();
                        }
                        $taxaArr[$vernName]['tid'][] = $row->tid;
                    }
                    elseif ($row->rankid == 140) {
                        if (!isset($taxaArr[$vernName]['families'])) {
                            $taxaArr[$vernName]['families'] = array();
                        }
                        $taxaArr[$vernName]['families'][] = $row->sciname;
                    }
                    else {
                        if (!isset($taxaArr[$vernName]['scinames'])) {
                            $taxaArr[$vernName]['scinames'] = array();
                        }
                        $taxaArr[$vernName]['scinames'][] = $row->sciname;
                    }
                }
                $result->free();
            }
            else {
                $taxaArr["no records"]["scinames"][] = "no records";
            }
        }
        elseif ($useThes) {
            foreach ($taxaArr as $key => $value) {
                if (array_key_exists("scinames",$value)) {
                    if (!in_array("no records",$value["scinames"])) {
                        $synArr = $spatialManager->getSynonyms($value["scinames"]);
                        if ($synArr) $taxaArr[$key]["synonyms"] = $synArr;
                    }
                }
                else {
                    $synArr = $spatialManager->getSynonyms($key);
                    if ($synArr) $taxaArr[$key]["synonyms"] = $synArr;
                }
            }
        }

        foreach ($taxaArr as $key => $valueArray) {
            if ($taxontype == 4) {
                $rs1 = $con->query("SELECT ts.tidaccepted FROM taxa AS t LEFT JOIN taxstatus AS ts ON t.TID = ts.tid WHERE (t.sciname = '".$con->real_escape_string($key)."')");
                if($r1 = $rs1->fetch_object()){
                    $taxaArr[$r1->tidaccepted] = $taxaArr[$key];
                    unset($taxaArr[$key]);
                }
            }
            elseif ($taxontype == 5) {
                $famArr = Array();
                if (isset($valueArray['families'])) {
                    $famArr = $valueArray['families'];
                }
                if (isset($valueArray['tid'])) {
                    $tidArr = $valueArray['tid'];
                    $sql = 'SELECT DISTINCT t.sciname '.
                        'FROM taxa t INNER JOIN taxaenumtree e ON t.tid = e.tid '.
                        'WHERE t.rankid = 140 AND e.taxauthid = 1 AND e.parenttid IN('.implode(',',$tidArr).')';
                    $rs = $con->query($sql);
                    if ($rs) {
                        while ($r = $rs->fetch_object()) {
                            $famArr[] = $r->sciname;
                        }
                        $rs->close();
                    }
                    if (!empty($famArr)) {
                        $famArr = array_unique($famArr);
                        $taxaArr[$key]['families'] = $famArr;
                    }
                }
            }
        }

        return $taxaArr;
    } finally {
        $con->close();
    }
}

?>