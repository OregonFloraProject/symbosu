<?php 

// This is a local serverside API wrapper for Index Herbariorum to get around cross-site API access over non-https connections

// Check for variables passed
$code = array_key_exists("code",$_REQUEST)?$_REQUEST["code"]:"";
$correspondent = array_key_exists("correspondent",$_REQUEST)?$_REQUEST["correspondent"]:"";

// URL for institutional information
$institutionURL = "http://sweetgum.nybg.org/science/api/v1/institutions/";

// URL for staff/correspondant information
$correspondentURL = "http://sweetgum.nybg.org/science/api/v1/staff/search?correspondent=yes&code=";

if ($code){

	// Return the staff/correspondant API
	if ($correspondent == "yes") {
		$json = file_get_contents($correspondentURL.$code);

	// Return the institution API
	} else {
		$json = file_get_contents($institutionURL.$code);
	}
	
	// Output API results
	header("Content-Type: application/json; charset=utf-8");
	echo $json;
}
