<?php
/*
------------------
Language: English
------------------
*/

include_once('sharedterms.en.php');

$LANG['PAGE_TITLE'] = 'Collection Search Parameters';
$LANG['PAGE_HEADER'] = 'Enter Search Parameters';
$LANG['TAXON_HEADER'] = 'Taxonomic Criteria';
$LANG['INCLUDE_SYNONYMS'] = 'Include Synonyms';
$LANG['BUTTON_NEXT_LIST'] = 'List Display';
$LANG['BUTTON_NEXT_TABLE'] = 'Table Display';
$LANG['SELECT_1-1'] = 'Any Name';
$LANG['SELECT_1-2'] = 'Scientific Name';
$LANG['SELECT_1-3'] = 'Family';
$LANG['SELECT_1-4'] = 'Taxonomic Group';
$LANG['SELECT_1-5'] = 'Common Name';
$LANG['SEPARATE_MULTIPLE'] = 'Separate multiple terms w/ commas';
$LANG['LOCALITY_CRITERIA'] = 'Locality Criteria';
$LANG['COUNTRY'] = 'Country';
$LANG['STATE'] = 'State/Province';
$LANG['COUNTY'] = 'County';
$LANG['LOCALITY'] = 'Locality';
$LANG['ELEV_INPUT_1'] = 'Elevation (in meters) low';
$LANG['ELEV_INPUT_2'] = 'Elevation (in meters) high';
$LANG['LAT_LNG_HEADER'] = 'Latitude and Longitude';
$LANG['LL_BOUND_TEXT'] = 'Bounding box';
$LANG['LL_BOUND_NLAT'] = 'Northern Latitude';
$LANG['LL_BOUND_SLAT'] = 'Southern Latitude';
$LANG['LL_BOUND_WLNG'] = 'Western Longitude';
$LANG['LL_BOUND_ELNG'] = 'Eastern Longitude';
$LANG['LL_N_SYMB'] = 'N';
$LANG['LL_S_SYMB'] = 'S';
$LANG['LL_W_SYMB'] = 'W';
$LANG['LL_E_SYMB'] = 'E';
$LANG['LL_POLYGON_TEXT'] = 'Polygon (WKT footprint)';
$LANG['LL_P-RADIUS_TEXT'] = 'Point-Radius';
$LANG['LL_P-RADIUS_LAT'] = 'Latitude';
$LANG['LL_P-RADIUS_LNG'] = 'Longitude';
$LANG['LL_P-RADIUS_RADIUS'] = 'Radius';
$LANG['LL_P-RADIUS_KM'] = 'Kilometers';
$LANG['LL_P-RADIUS_MI'] = 'Miles';
$LANG['MAP_AID'] = 'Mapping Aid';
$LANG['COLLECTOR_HEADER'] = 'Collector Criteria';
$LANG['COLLECTOR_LASTNAME'] = "Collector&#39;s Last Name";
$LANG['TITLE_TEXT_2'] = 'Separate multiple terms by commas and ranges by " - " (space before and after dash required), e.g.: 3542,3602,3700 - 3750';
$LANG['COLLECTOR_NUMBER'] = "Collector&#39;s Number";
$LANG['COLLECTOR_DATE'] = 'Collection Date Start';
$LANG['COLLECTOR_DATE_END'] = 'Collection Date End';
$LANG['TITLE_TEXT_3'] = 'Single date or start date of range';
$LANG['TITLE_TEXT_4'] = 'End date of range; leave blank if searching for single date';
$LANG['SPECIMEN_HEADER'] = 'Specimen Criteria';
$LANG['CATALOG_NUMBER'] = 'Barcode Number';
$LANG['INCLUDE_OTHER_CATNUM'] = 'Include other catalog numbers and GUIDs';
$LANG['MATERIAL_SAMPLE_TYPE'] = 'Limit by Material Sample';
$LANG['ALL_MATERIAL_SAMPLE'] = 'All Records with Material Samples';
$LANG['TYPE'] = 'Limit to Type Specimens';
$LANG['HAS_IMAGE'] = 'Limit to Specimens with Images';
$LANG['HAS_GENETIC'] = 'Limit to Specimens with Genetic Data';
$LANG['HAS_COORDS'] = 'Limit to Specimens with Geocoordinates';
$LANG['INCLUDE_CULTIVATED'] = 'Include cultivated/captive occurrences';
$LANG['TRAIT_HEADER'] = 'Trait Criteria';
$LANG['TRAIT_DESCRIPTION'] = 'Selecting multiple traits will return all records with at least one of those traits';

// For compatibility with OregonFlora
$LANG['GENERAL_TEXT_1'] = 'Fill in one or more of the following query criteria and click &quot;Search&quot; to view your results.';
$LANG['TAXON_INPUT'] = 'Taxa:';
$LANG['GENERAL_TEXT_2'] = 'Include Synonyms from Taxonomic Thesaurus';
$LANG['GENERAL_TEXT_2_MAP'] = $LANG['INCLUDE_SYNONYMS'];
$LANG['TITLE_TEXT_1'] = $LANG['SEPARATE_MULTIPLE'];
$LANG['LOCALITY_HEADER'] = $LANG['LOCALITY_CRITERIA'];
$LANG['COUNTRY_INPUT'] = $LANG['COUNTRY'];
$LANG['STATE_INPUT'] = $LANG['STATE'];
$LANG['COUNTY_INPUT'] = $LANG['COUNTY'];
$LANG['LOCALITY_INPUT'] = $LANG['LOCALITY'];
$LANG['ASSOC_HOST_INPUT'] = 'Host:';
$LANG['LL_P-RADIUS_TITLE_1'] = 'Find Coordinate';
$LANG['OTHER_CATNUM'] = 'Other Catalog Number:';

// For compatibility with OregonFlora
//Following used in mapboundingbox.php
$LANG['MBB_TITLE'] = 'Coordinate Mapper';
$LANG['MBB_INSTRUCTIONS'] = 'Click once to start drawing and again to finish rectangle. Click on the Submit button to transfer Coordinates.';
$LANG['MBB_NORTHERN'] = 'Northern Lat';
$LANG['MBB_EASTERN'] = 'Eastern Long';
$LANG['MBB_SOUTHERN'] = 'Southern Lat';
$LANG['MBB_WESTERN'] = 'Western Long';

// For compatibility with OregonFlora
//Following used in mappointradius.php
$LANG['MPR_TITLE'] = 'Coordinate Mapper';
$LANG['MPR_INSTRUCTIONS'] = 'Click once to capture coordinates. Click on the Submit Coordinate button to transfer Coordinates.';
$LANG['MPR_LAT'] = 'Latitude';
$LANG['MPR_LNG'] = 'Longitude';
$LANG['SUBMIT'] = 'Submit';


?>
