<?php
$DEFAULT_LANG = 'en';			//Default language
$DEFAULT_PROJ_ID = 1;
$DEFAULTCATID = 0;
$DEFAULT_TITLE = '';
$EXTENDED_LANG = 'en';		//Add all languages you want to support separated by commas (e.g. en,es); currently supported languages: en,es
$TID_FOCUS = '';
$ADMIN_EMAIL = '';
$CHARSET = 'UTF-8';					//ISO-8859-1 or UTF-8
$PORTAL_GUID = '';				//Typically a UUID
$SECURITY_KEY = '';				//Typically a UUID used to verify access to certain web service
$IS_DEV = true;         // Is this dev or prod mode?

$CLIENT_ROOT = '';				//URL path to project root folder (relative path w/o domain, e.g. '/seinet')
$SERVER_ROOT = '';				//Full path to Symbiota project root folder
$TEMP_DIR_ROOT = $SERVER_ROOT.'/temp';				//Must be writable by Apache; will use system default if not specified
$LOG_PATH = $SERVER_ROOT.'/content/logs';					//Must be writable by Apache; will use <SYMBIOTA_ROOT>/temp/logs if not specified

//Path to CSS files
$CSS_BASE_PATH = $CLIENT_ROOT.'/css/symb';
$CSS_VERSION_LOCAL = '1';		//Changing this variable will force a refresh of main.css styles within users browser cache for all pages

//the root for the image directory
$IMAGE_DOMAIN = '';				//Domain path to images, if different from portal
$IMAGE_ROOT_URL = '';				//URL path to images
$IMAGE_ROOT_PATH = '';			//Writable path to images, especially needed for downloading images

//Pixel width of web images
$IMG_WEB_WIDTH = 1400;
$IMG_TN_WIDTH = 200;
$IMG_LG_WIDTH = 3200;
$IMG_FILE_SIZE_LIMIT = 300000;		//Files above this size limit and still within pixel width limits will still be resaved w/ some compression
$IPLANT_IMAGE_IMPORT_PATH = '';		//Path used to map/import images uploaded to the iPlant image server (e.g. /home/shared/project-name/--INSTITUTION_CODE--/, the --INSTITUTION_CODE-- text will be replaced with collection's institution code)

//$USE_IMAGE_MAGICK = 0;		//1 = ImageMagick resize images, given that it's installed (faster, less memory intensive)
$TESSERACT_PATH = ''; 			//Needed for OCR function in the occurrence editor page
$NLP_LBCC_ACTIVATED = 0;
$NLP_SALIX_ACTIVATED = 0;

//Module activations
$OCCURRENCE_MOD_IS_ACTIVE = 1;
$FLORA_MOD_IS_ACTIVE = 1;
$KEY_MOD_IS_ACTIVE = 1;

$REQUESTED_TRACKING_IS_ACTIVE = 0;   // Allow users to request actions such as requests for images to be made for specimens

//Configurations for GeoServer integration
$GEOSERVER_URL = '';   // URL for Geoserver instance serving map data for this portal
$GEOSERVER_RECORD_LAYER = '';   // Name of Geoserver layer containing occurrence point data for this portal

//Configurations for Apache SOLR integration
$SOLR_URL = '';   // URL for SOLR instance indexing data for this portal
$SOLR_FULL_IMPORT_INTERVAL = 0;   // Number of hours between full imports of SOLR index.

//Configurations for publishing to GBIF
$GBIF_USERNAME = '';                //GBIF username which portal will use to publish
$GBIF_PASSWORD = '';                //GBIF password which portal will use to publish
$GBIF_ORG_KEY = '';                 //GBIF organization key for organization which is hosting this portal

$FP_ENABLED = 0;					//Enable Filtered-Push modules

//Misc variables
$DEFAULT_TAXON_SEARCH = 2;			//Default taxonomic search type: 1 = Any Name, 2 = Scientific Name, 3 = Family, 4 = Taxonomic Group, 5 = Common Name

$GOOGLE_MAP_KEY = '';				//Needed for Google Map; get from Google
$MAPBOX_API_KEY = '';
$MAP_THUMBNAILS = false;				//Display Static Map thumbnails within taxon profile, checklist, etc

$MAPPING_BOUNDARIES = '';			//Project bounding box; default map centering; (e.g. 42.3;-100.5;18.0;-127)
$SPATIAL_INITIAL_CENTER = '';	    //Initial map center for Spatial Module. Default: '[-110.90713, 32.21976]'
$SPATIAL_INITIAL_ZOOM = '';			//Initial zoom for Spatial Module. Default: 7
$TAXON_PROFILE_MAP_CENTER = '';			//Center for taxon profile maps
$TAXON_PROFILE_MAP_ZOOM = '';			//Zoom for taxon profile maps
$ACTIVATE_GEOLOCATION = false;		//Activates HTML5 geolocation services in Map Search
$GOOGLE_ANALYTICS_KEY = '';			//Needed for setting up Google Analytics
$GOOGLE_ANALYTICS_TAG_ID = '';		//Needed for setting up Google Analytics 4 Tag ID
$RECAPTCHA_PUBLIC_KEY = '';			//Now called site key
$RECAPTCHA_PRIVATE_KEY = '';		//Now called secret key
$EOL_KEY = '';						//Not required, but good to add a key if you plan to do a lot of EOL mapping
$TAXONOMIC_AUTHORITIES = array('COL'=>'','WoRMS'=>'');		//List of taxonomic authority APIs to use in data cleaning and thesaurus building tools, concatenated with commas and order by preference; E.g.: array('COL'=>'','WoRMS'=>'','TROPICOS'=>'','EOL'=>'')
$QUICK_HOST_ENTRY_IS_ACTIVE = 0;   	//Allows quick entry for host taxa in occurrence editor
$GLOSSARY_EXPORT_BANNER = '';		//Banner image for glossary exports. Place in images/layout folder.
$DYN_CHECKLIST_RADIUS = 10;			//Controls size of concentric rings that are sampled when building Dynamic Checklist
$DISPLAY_COMMON_NAMES = 1;			//Display common names in species profile page and checklists displays
$ACTIVATE_DUPLICATES = 1;			//Activates Specimen Duplicate listings and support features. Mainly relavent for herabrium collections
$ACTIVATE_EXSICCATI = 0;			//Activates exsiccati fields within data entry pages; adding link to exsiccati search tools to portal menu is recommended
$ACTIVATE_CHECKLIST_FG_EXPORT = 0;			//Activates checklist fieldguide export tool
$ACTIVATE_FIELDGUIDE = 0;	//Activates FieldGuide Batch Processing module
$FIELDGUIDE_API_KEY = '';	//API Key for FieldGuide Batch Processing module
$GENBANK_SUB_TOOL_PATH = '';	//Path to GenBank Submission tool installation
$ACTIVATE_GEOLOCATE_TOOLKIT = 0;	//Activates GeoLocate Toolkit located within the Processing Toolkit menu items
$OCCUR_SECURITY_OPTION = 1;			//Occurrence security options supported: value 1-7; 1 = Locality security, 2 = Taxon security, 4 = Full security, 3 = L & T, 5 = L & F, 6 = T & F, 7 = all
$SEARCH_BY_TRAITS = '0';			//Activates search fields for searching by traits (if trait data have been encoded): 0 = trait search off; any number of non-zeros separated by commas (e.g., '1,6') = trait search on for the traits with these id numbers in table tmtraits.

$IGSN_ACTIVATION = 0;

//$SMTP_ARR = array('host'=>'','port'=>587,'username'=>'','password'=>'','timeout'=>60);  //Host is requiered, others are optional and can be removed

$RIGHTS_TERMS = array(
    'CC0 1.0 (Public-domain)' => 'http://creativecommons.org/publicdomain/zero/1.0/',
    'CC BY (Attribution)' => 'http://creativecommons.org/licenses/by/4.0/',
    'CC BY-NC (Attribution-Non-Commercial)' => 'http://creativecommons.org/licenses/by-nc/4.0/',
    'CC BY-NC-ND 4.0 (Attribution-NonCommercial-NoDerivatives 4.0 International)' => 'https://creativecommons.org/licenses/by-nc-nd/4.0/'
);

/*
//Default editor properties; properties defined in collection will override these values
$EDITOR_PROPERTIES = array(
	'modules-panel' => array(
		'paleo' => array('status'=>0,'titleOverride'=>'Paleonotology Terms')
	),
	'features' => array('catalogDupeCheck'=>1,'otherCatNumDupeCheck'=>0,'dupeSearch'=>1),
	'labelOverrides' => array(),
	'cssTerms' => array(
		'#recordNumberDiv'=>array('float'=>'left','margin-right'=>'2px'),
		'#recordNumberDiv input'=>array('width'=>'60px'),
		'#eventDateDiv'=>array('float'=>'left'),
		'#eventDateDiv input'=>array('width'=>'110px')
	),
	'customCSS' => array(),
	'customLookups' => array(
		'processingStatus' => array('Unprocessed','Stage 1','Stage 2','Pending Review','Expert Required','Reviewed','Closed')
	)
);
// json: {"editorProps":{"modules-panel":{"paleo":{"status":1}}}}
*/

//Individual page menu and navigation crumbs
//Menu variables turn on and off the display of left menu 
//Crumb variables allow the customization of the bread crumbs. A crumb variable with an empty value will cause crumbs to disappear
//Variable name should include path to file separated by underscores and then the file name ending with "Menu" or "Crumbs"
//checklists/
	$checklists_checklistMenu = 0;
	//$checklists_checklistCrumbs = "<a href='../index.php'>Home</a> &gt;&gt; <a href='index.php'>Checklists</a> &gt;&gt; ";	
//collections/
	$collections_indexMenu = 0;
	$collections_harvestparamsMenu = 0;
	//$collections_harvestparamsCrumbs = "<a href='index.php'>Collections</a> &gt;&gt; ";
	$collections_listMenu = 0;
	$collections_checklistMenu = 0;
	$collections_download_downloadMenu = 0;
	$collections_maps_indexMenu = 0;
	
//ident/
	$ident_keyMenu = 0;
	$ident_tools_chardeficitMenu = 0;
	$ident_tools_massupdateMenu = 0;
	$ident_tools_editorMenu = 0;
	
//taxa/
	$taxa_indexMenu = 0;
	$taxa_admin_tpeditorMenu = 0;
	
//glossary/
	$glossary_indexBanner = 0;
	
//loans/
	$collections_loans_indexMenu = 0;

//agents/
    $agents_indexMenu = TRUE;
    $agent_indexCrumbs = array();
    array_push($agent_indexCrumbs,"<a href='$CLIENT_ROOT/index.php'>Home</a>");
    array_push($agent_indexCrumbs,"<a href='$CLIENT_ROOT/agents/index.php'>Agents</a>");

$COOKIE_SECURE = false;
if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443){
	header("strict-transport-security: max-age=600");
	$COOKIE_SECURE = true;
}

//Base code shared by all pages; leave as is
include_once("symbbase.php");
/* --DO NOT ADD ANY EXTRA SPACES BELOW THIS LINE-- */?>
