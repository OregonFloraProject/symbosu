<?php
include_once("../../config/symbini.php");
include_once("$SERVER_ROOT/classes/Functional.php");
include_once("$SERVER_ROOT/classes/ProfileManager.php");

function getProfileDataForRequestingAccess($uid) {
  $pm = new ProfileManager();
  $pm->setUid($uid);

  if ($pm->hasRequestedRarePlantAccess()) {
    return ["accessRequested" => true];
  }

  $person = $pm->getPerson();
  return [
    "firstName" => $person->getFirstName(),
    "lastName" => $person->getLastName(),
    "email" => $person->getEmail(),
    "title" => $person->getTitle(),
    "institution" => $person->getInstitution(),
    "department" => $person->getDepartment(),
  ];
}

function updateProfileAndRequestAccess($uid, $params) {
  $pm = new ProfileManager();
  $pm->setUid($uid);

  /**
   * 2024-09-20(eric): for testing only, remove before deploying
   */
  if (array_key_exists("delete", $params)) {
    $pm->deleteRarePlantAccessRequest();
    return [];
  }

  if (
    !array_key_exists("firstName", $params) ||
    !array_key_exists("lastName", $params) ||
    !array_key_exists("email", $params) ||
    !array_key_exists("title", $params) ||
    !array_key_exists("institution", $params)
  ) {
    return ["error" => "missing required field"];
  }

  $data = [
    "firstname" => $params["firstName"],
    "lastname" => $params["lastName"],
    "email" => $params["email"],
    "title" => $params["title"],
    "institution" => $params["institution"],
    "department" => array_key_exists("department", $params) ? $params["department"] : null,
  ];
  $status = $pm->updateProfile($data);
  if (!$status) {
    return ["error" => "error updating profile"];
  }

  $status = $pm->requestRarePlantAccess($params["reason"], time());
  if (!$status) {
    return ["error" => "error requesting access"];
  }

  return ["accessRequested" => true];
}

$type = $_SERVER['REQUEST_METHOD'];

$result = [];
if (isset($SYMB_UID) && $SYMB_UID) {
  if ($type === "GET") {
    $result = getProfileDataForRequestingAccess($SYMB_UID);
  } else if ($type === "POST") {
    $result = updateProfileAndRequestAccess($SYMB_UID, $_POST);
  }
}

array_walk_recursive($result, 'cleanWindowsRecursive'); #replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
?>
