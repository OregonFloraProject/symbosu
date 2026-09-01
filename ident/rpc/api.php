<?php

include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/ident/shared/checklistApi.php");

$result = [];

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

if (!handleChecklistExport($result)) {
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
}

?>
