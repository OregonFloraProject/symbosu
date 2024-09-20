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

  $data = [
    "firstname" => $params["firstName"],
    "lastname" => $params["lastName"],
    "email" => $params["email"],
    "title" => $params["title"],
    "institution" => $params["institution"],
    "department" => $params["department"],
  ];
  $status = $pm->updateProfile($data);
  // TODO(eric): handle error

  if ($status) {
    $status = $pm->requestRarePlantAccess($params["reason"], time());
  }

  return $status;
}

$type = $_SERVER['REQUEST_METHOD'];

$result = [];
if (isset($SYMB_UID) && $SYMB_UID) {
  if ($type === "GET") {
    $result = getProfileDataForRequestingAccess($SYMB_UID);
  } else if ($type === "POST") {
    $result = updateProfileAndRequestAccess($SYMB_UID, $_POST); // TODO: set status etc from result
  }
}

array_walk_recursive($result, 'cleanWindowsRecursive'); #replace Windows characters
header("Content-Type: application/json; charset=utf-8");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE);
?>
