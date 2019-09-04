<?php
  include_once("../../config/symbini.php");
  include_once($SERVER_ROOT . "/config/dbconnection.php");

/**
 * // TODO: Make this better
 * @param $parts array join the given parts using '/';
 * @return string URL string
 */
  function url_join($parts) {
    $url = "";
    for ($i = 0; $i < count($parts); $i++) {
      $url .= $parts[$i];
      if (!substr($url, -1) !== "/" && $i < count($parts) - 1) {
        $url .= "/";
      }
    }

    return $url;
  }

  /**
   * Runs the given query & returns the results as an array of associative arrays
   */
  function run_query($sql) {
    $conn = MySQLiConnectionFactory::getCon("readonly");
    $outResults = [];

    if ($conn !== null) {
      $res = $conn->query($sql);
      if ($res) {
        while($row = $res->fetch_assoc()) {
          array_push($outResults, $row);
        }
        $res->free();
      }

      $conn->close();
    }

    return $outResults;
  }

  /**
   * Returns the most prominent image for the given taxa ID
   */
  function get_image_for_tid($tid) {
    global $IMAGE_ROOT_URL;
    global $IMAGE_DOMAIN;

    $sql = "SELECT i.thumbnailurl FROM images AS i WHERE tid = $tid ORDER BY i.sortsequence LIMIT 1;";
    $res = run_query($sql);

    if (count($res) > 0 && key_exists("thumbnailurl", $res[0])) {
      $result = $res[0]["thumbnailurl"];

      if (substr($result, 0, 4) !== "http") {
        if (isset($IMAGE_ROOT_URL)) {
          $result = url_join([$IMAGE_ROOT_URL, $result]);
        }
        if (isset($IMAGE_DOMAIN)) {
          $result = url_join([$IMAGE_DOMAIN, $result]);
        }
      }

      return $result;
    }

    return "";
  }

  function get_size_for_tid($tid) {
    $HEIGHT_KEY = 140;
    $WIDTH_KEY = 738;

    $height_sql = "SELECT avg(cs) as avg_height FROM kmdescr WHERE tid = $tid AND cid = $HEIGHT_KEY;";
    $width_sql = "SELECT avg(cs) as avg_width FROM kmdescr WHERE tid = $tid AND cid = $WIDTH_KEY;";

    $height_res = run_query($height_sql);
    $width_res = run_query($width_sql);

    $avg_width = 0;
    $avg_height = 0;

    if (count($height_res) > 0 && key_exists("avg_height", $height_res[0]) && is_numeric($height_res[0]["avg_height"])) {
      $avg_height = floatval($height_res[0]["avg_height"]);
    }

    if (count($width_res) > 0 && key_exists("avg_width", $width_res[0]) && is_numeric($width_res[0]["avg_width"])) {
      $avg_width = floatval($width_res[0]["avg_width"]);
    }

    return [$avg_width, $avg_height];
  }

  /**
   * Returns all unique taxa with thumbnail urls
   */
  function get_taxa($params) {
    if (!key_exists("search", $params) || $params["search"] === "") { $params["search"] = null; }

    # If all args are null, quit here
    if ($params["search"] === null) {
      return [];
    }

    # Select all species & below (t.rankid >= 200) that have some sort of name
    $sql = "SELECT t.tid, t.sciname, v.vernacularname FROM taxa as t ";
    $sql .= "LEFT JOIN taxavernaculars AS v ON t.tid = v.tid ";
    $sql .= "WHERE t.rankid >= 220 AND (t.sciname IS NOT NULL OR v.vernacularname IS NOT NULL) ";

    $search = $params["search"];
    $sql .= "AND (t.sciname LIKE '%$search%' OR v.vernacularname LIKE '%$search%') ";

    $sql .= "GROUP BY t.tid ORDER BY v.vernacularname;";

    $resultsTmp = run_query($sql);
    $results = [];

    // Populate image urls
    foreach ($resultsTmp as $result) {
      $size = get_size_for_tid($result["tid"]);
      $result["avg_width"] = $size[0];
      $result["avg_height"] = $size[1];

      $result["image"] = get_image_for_tid($result["tid"]);
      if ($result["image"] !== "") {
        array_push($results, $result);
      }
    }

    return $results;
  }

  // Begin View
  echo '<script type="application/javascript">const searchResults = ' .
    json_encode(get_taxa($_GET), JSON_NUMERIC_CHECK) ,
  '</script>';
?>

