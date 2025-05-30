<?php 
// OSC Label Collections

//Enter one to many custom cascading style sheet files 
const CSSARR = array('occurVarDefault.css');

//Enter one to many custom java script files 
//const JSARR = array('example1.js','example2.js'); 
const JSARR = array('OregonFloraGenObs.js');

//Custom Processing Status setting
const PROCESSINGSTATUS = array('unprocessed','needs label','label printed','expert required','needs research','pending review','reviewed','sent to herbarium','stage 1','stage 2','stage 3');

//Uncomment to turn catalogNumber duplicate search check on/off (on by default)
//define('CATNUMDUPECHECK',true); 

//Uncomment to turn otherCatalogNumbers duplicate search check on/off (on by default)
//define('OTHERCATNUMDUPECHECK',true);

//Uncomment to turn duplicate specimen search function on/off (on by default)
//define('DUPESEARCH',false);

//Uncomment to turn locality event auto-lookup (locality field autocomplete) function on/off (on by default)
//0 = off, permanently deactivated, 1 = activated by default (Default), 2 = deactivated by default
//define('LOCALITYAUTOLOOKUP',1);

//Uncomment to turn the Associated Taxa entry aid (popup to enter associated taxa) on/off (on by default)
//define('ACTIVATEASSOCTAXAAID',true);

//Uncomment to set custom annotation label header and footer
//define('ANNOTATIONHEADER', '');
define('ANNOTATIONFOOTER', 'Oregon State University Herbarium (OSC)');


// FieldLabel text: uncomment variables and add a value to modify field labels 
define('CATALOGNUMBERLABEL','Barcode Number');
define('OTHERCATALOGNUMBERSLABEL','Accession Number');
define('RECORDEDBYLABEL','Collector');
define('RECORDNUMBERLABEL','Collector Number');
//define('EVENTDATELABEL','');
//define('EVENTDATE2LABEL','');
//define('ASSOCIATEDCOLLECTORSLABEL','');
//define('VERBATIMEVENTDATELABEL','');
//define('EXSICCATITITLELABEL','');
//define('EXSICCATINUMBERLABEL','');
//define('SCIENTIFICNAMELABEL','');
//define('SCIENTIFICNAMEAUTHORSHIPLABEL','');
//define('IDCONFIDENCELABEL','');
//define('IDENTIFICATIONQUALIFIERLABEL','');
//define('FAMILYLABEL','');
//define('IDENTIFIEDBYLABEL','');
//define('DATEIDENTIFIEDLABEL','');
//define('IDENTIFICATIONREFERENCELABEL','');
//define('IDENTIFICATIONREMARKSLABEL','');
//define('TAXONREMARKSLABEL','');
//define('CONTINENTLABEL','');
//define('WATERBODYLABEL','');
//define('ISLANDGROUPLABEL','');
//define('ISLANDLABEL','');
//define('COUNTRYLABEL','');
//define('STATEPROVINCELABEL','');
//define('COUNTYLABEL','');
//define('MUNICIPALITYLABEL','');
//define('LOCATIONIDLABEL','');
//define('LOCALITYLABEL','');
//define('LOCATIONREMARKSLABEL','');
//define('LOCALITYSECURITYLABEL','');
//define('LOCALITYSECURITYREASONLABEL','');
//define('DECIMALLATITUDELABEL','');
//define('DECIMALLONGITUDELABEL','');
//define('COORDINATEUNCERTAINITYINMETERSLABEL','');
//define('GEODETICDATUMLABEL','');
//define('VERBATIMCOORDINATESLABEL','');
//define('ELEVATIONINMETERSLABEL','');
//define('VERBATIMELEVATIONLABEL','');
//define('DEPTHINMETERSLABEL','');
//define('VERBATIMDEPTHLABEL','');
//define('GEOREFERENCEBYLABEL','');
//define('GEOREFERENCESOURCESLABEL','');
//define('GEOREFERENCEREMARKSLABEL','');
//define('GEOREFERENCEPROTOCOLLABEL','');
//define('GEOREFERENCEVERIFICATIONSTATUSLABEL','');
//define('FOOTPRINTWKTLABEL','');
//define('HABITATLABEL','');
//define('SUBSTRATELABEL','');
//define('HOSTLABEL','');
//define('ASSOCIATEDTAXALABEL','');
define('OCCURRENCEREMARKSLABEL','Notes (Occurrence Remarks)');
//define('OCCURRENCEREMARKSLABEL','');
//define('DYNAMICPROPERTIESLABEL','');
//define('LIFESTAGELABEL','');
//define('SEXLABEL','');
//define('INDIVIDUALCOUNTLABEL','');
//define('SAMPLINGPROTOCOLLABEL','');
//define('PREPARATIONSLABEL','');
//define('REPRODUCTIVECONDITIONLABEL','');
//define('BEHAVIORLABEL','');
//define('VITALITYLABEL','');
//define('ESTABLISHMENTMEANSLABEL','');
//define('CULTIVATIONSTATUSLABEL','');
//define('TYPESTATUSLABEL','');
define('DISPOSITIONLABEL','Specimen Location (Disposition)');
//define('OCCURRENCEIDLABEL','');
//define('FIELDNUMBERLABEL','');
//define('BASISOFRECORDLABEL','');
//define('LANGUAGELABEL','');
define('LABELPROJECTLABEL','Label Project');
//define('DUPLICATEQUANTITYLABEL','');
//define('INSTITUTIONCODELABEL','');
//define('COLLECTIONCODELABEL','');
//define('OWNERINSTITUTIONCODELABEL','');
//define('PROCESSINGSTATUSLABEL','');
define('DATAGENERALIZATIONSLABEL','Transcription Notes (Data Generalizations)');
//define('OCRWHOLEIMAGELABEL','');
//define('OCRANALYSISLABEL','');

// Field Tooltip text: uncomment variables and add a value to modify field tooltips that popup on hover

// Collection and Collector info
define('CATALOGNUMBERTIP','Field disabled. Leave this blank. The herbarium will add a barcode number.');
define('OTHERCATALOGNUMBERSTIP','Enter any additional specimen identifiers, along with their name: (e.g., field numbers)');
define('RECORDEDBYTIP','Primary or only collector name. Omit preceding titles (e.g., Dr., Mr., Mrs., Miss) unless Mrs. is followed by husband’s name.');
define('RECORDNUMBERTIP','Collector number. Include any letters or symbols in the collector number (e.g., KP-004b)');
define('EVENTDATETIP','Date of specimen collection. Enter in the format YYYY-MM-DD. If year, month, or day is unknown, replace missing information with zeros (e.g. 2004-00-00).');
define('EVENTDATE2TIP','End date of specimen collection, if multiple dates are specified on the label. Enter in the format YYYY-MM-DD. If year, month, or day is unknown, replace missing information with zeros (e.g. 2004-00-00). Roman numerals always indicate the month. If there is only one date specified, enter as the start date, and leave this blank.');
define('DUPLICATESTIP','Search for duplicate specimen information from other herbaria. Turn this off if you do not expect other herbaria to have your duplicates yet.');
define('ASSOCIATEDCOLLECTORSTIP','Any associated collectors names. Each name should be in order of “First name Last name.”, separated by commas. Exclude other words (e.g., “and”, “with”, “&”). Remove preceding titles (see also Collector).');
define('VERBATIMEVENTDATETIP','Field disabled. Leave blank. Only used for old specimens in the herbarium.');

// Exsiccati
//define('EXSICCATITITLETIP','');
//define('EXSICCATINUMBERTIP','');

// Latest Identification
define('SCIENTIFICNAMETIP','The scientific name on the original label (often not the latest identification!), even if this name is a genus or family. Use “ssp.” for subspecies and “var.” for variety.
  * Select an option from the dropdown list rather than typing out the name when possible. 
  * If there are notes or references, click the pencil beside Date Identified to add them. 
  * If this name is the current determination, and the specimen is filed under a different name than the name on the specimen, this information should be noted in the Dispositions field.
  * If there is no scientific name on the original label, leave it blank.
  * Do not enter authors in the Scientific name field. For example, Poa bulbosa L. var. concinna Beck” should be transcribed as “Poa bulbosa var. concinna”.');
define('SCIENTIFICNAMEAUTHORSHIPTIP','Enter authorities (authors of the scientific name) that appear after the scientific name. This field autofills after entering the scientific name if the name is already in our database.
  * If the name is a variety or subspecies, only enter the intraspecific author(s) that follow the var. or ssp., not those after the species.
');
//define('IDCONFIDENCETIP','');
define('IDENTIFICATIONQUALIFIERTIP','The determiner’s expression of uncertainty in their identification, if listed on the label along with the scientific name. Examples are “?”, “aff.”, “cf.”, “possibly”, “probably”, etc.).');
define('FAMILYTIP','The plant family that this specimen is in, if noted on the label. This field autofills after entering the scientific name if the name is already in our database.');
define('IDENTIFIEDBYTIP','The name of the person who originally identified the specimen on the original label, if other than the collector (the determiner).');
define('DATEIDENTIFIEDTIP','The date of original identification on the original label, if identified by someone other than the collector. Enter as YYYY-MM-DD (see collection date).');
define('IDENTIFICATIONREFERENCETIP','Publication reference(s) used in the identification. If multiple references are given, separate them with “ | “.');
define('IDENTIFICATIONREMARKSTIP','Comments or notes about the identification (e.g., identifying traits, reasons for this ID)');
define('TAXONREMARKSTIP','Field disabled. Not used at OSU.');

// Locality & Georeferencing
//define('CONTINENTTIP', '');
//define('WATERBODYTIP', '');
//define('ISLANDGROUPTIP', '');
//define('ISLANDTIP', '');
define('COUNTRYTIP','The country where the specimen was collected. Spell out in full (“United States”, not “U.S.”).');
define('STATEPROVINCETIP','The state (USA) or province (Canada) where the specimen was collected. Enter as a full name rather than an abbreviation (e.g., “California” instead of “CA”). ');
define('COUNTYTIP','The county (parish in Louisiana) in which the specimen was collected. 
  * Don’t include things like “Co.” or “County”.
  * Select from dropdown list for USA & Canada specimens.');
define('MUNICIPALITYTIP','Not generally used at OSU, but record for specimens from Mexico.');
define('LOCATIONIDTIP','Field disabled. Not used at OSU.');
define('LOCALITYTIP','The geographic description of where the specimen was collected. This should not include country, state, or county information, unless it is necessary for context of the locality (e.g., “southwest corner of Jefferson county” or “northeast Oregon”). Only capitalize pronouns.');
//define('LOCATIONREMARKSTIP','');
define('LOCALITYAUTOLOOKUPTIP','Unless deactivated, when you type in a locality, it will try to autocomplete with previously entered localities that also match the collector and date. If you select one of those matches, it will fill in any associated data (lat/long, elevation, habitat, associated species, etc.)');
//define('LOCALITYSECURITYTIP','');
//define('LOCALITYSECURITYREASONTIP','');
define('DECIMALLATITUDETIP','If Latitude is stated on the label in decimal format, enter here. If on the label as degrees/minutes/seconds, TRS or UTM, click the F (format) button, enter in the respective fields and click the Insert Values button. This auto-populates the Verbatim Coordinates and Lat/Long fields.');
define('DECIMALLONGITUDETIP','If Longitude is stated on the label in decimal format, enter here. If on the label as degrees/minutes/seconds, TRS or UTM, click the F (format) button, enter in the respective fields and click the Insert Values button. This auto-populates the Verbatim Coordinates and Lat/Long fields.');
define('COORDINATEUNCERTAINITYINMETERSTIP','If GPS uncertainty is given, enter it here in meters. Otherwise, leave it blank.');
define('GOOGLEMAPSTIP','Google maps tool for finding a specimen location and getting coordinates.');
define('GEOLOCATETIP','GeoLocate tool for finding a specimen location and getting coordinates.');
define('COORDCLONETIP','Coordinate cloning tool for finding a specimen location and getting coordinates.');
define('GEOTOOLSTIP','Click to view additional coordinate fields of these types:
  * Latitude/Longitude if given in degrees, minutes, seconds
  * UTM
  * TRS ');
define('GEODETICDATUMTIP','If the coordinate datum is available (e.g., WGS84, NAD83) enter here. Otherwise, leave it blank.');
define('VERBATIMCOORDINATESTIP','Do not enter data directly into this field. If the label uses degrees/minutes/seconds, UTM’s or TRS coordinate systems, click the F (format) button to show fields specialized for capturing these data. Once entered click the respective “Insert values” button below the entered coordinates. This autopopulates the Verbatim Coordinates field.');
define('RECALCULATECOORDSTIP','Do not use this to convert verbatim coordinates. It is better to use the conversion provided by clicking the F button.');
define('ELEVATIONINMETERSTIP','Enter elevation under Verbatim Elevation field. This field will autopopulate.');
define('MINELEVATIONINMETERSTIP','Minimum elevation. Please enter with the Verbatim Elevation field, and click <<. This field will autopopulate.');
define('MAXELEVATIONINMETERSTIP','Maximum elevation. Please enter with the Verbatim Elevation field, and click <<. This field will autopopulate.');
define('RECALCULATEELEVTIP','Converts the verbatim elevation field to elevation in meters.');
define('VERBATIMELEVATIONTIP','Enter elevation followed by units (ft or m). then click << to the left of this field to autopopulate Elevation in Meters field.');
define('DEPTHINMETERSTIP','Enter the depth in meters, if aquatic');
define('MINDEPTHINMETERSTIP','Minimum depth in meters, if aquatic');
define('MAXDEPTHINMETERSTIP','Maximum depth in meters, if aquatic');
define('VERBATIMDEPTHTIP','Not generally used; use the main depth field if aquatic');
define('GEOREFERENCEBYTIP','Name of the person who georeferenced the specimen');
//define('GEOREFERENCESOURCESTIP','');
//define('GEOREFERENCEREMARKSTIP','');
//define('GEOREFERENCEPROTOCOLTIP','');
//define('GEOREFERENCEVERIFICATIONSTATUSTIP','');
define('GOOGLEMAPSPOLYGONTIP','Tool to georeference a location as a polygon.');
define('FOOTPRINTWKTTIP','A georeferenced location as a polygon. Do not fill in directly, click the globe beside this field to interactively construct a polygon.');

// Misc
define('HABITATTIP','Environmental conditions in which the specimen was found (e.g., marsh, grassy field). Include community types (e.g., “Douglas fir forest”). Sometimes it is necessary to repeat a part of the location phrase to provide needed context. End phrase with a period.');
define('SUBSTRATETIP','Enter the substrate, especially for bryophytes, algae, lichens, etc. Not generally used for OSU vascular plants except for epiphytes or parasitic plants.');
//define('HOSTTIP','');
define('ASSOCIATEDTAXATIP','Other taxa listed as growing with the specimen (species associated with community types belong in Habitat e.g., “Douglas fir forest”). Include common names if given on the label. Enter all names separated by a comma, excluding additional words (and, with, &). For quick entry, enter the first three letters of the genus, followed by a space, and the first three letters of the species.');
define('ASSOCIATEDTAXAAIDTIP','This pops up a helper box to add associated taxa names.');
define('VERBATIMATTRIBUTESTIP','Information specific to the individual specimen(s) (e.g., size, condition, color)');
define('OCCURRENCEREMARKSTIP','Add any additional data on the label that does not fit into the other data fields, including frequency (common, scattered, rare). Separate disparate information with a semicolon and end phrase with a period.');
define('DYNAMICPROPERTIESTIP','Not generally used at OSU.');
define('LIFESTAGETIP','Not generally used at OSU');
define('SEXTIP','Enter the gender of the specimen if noted on the original label (e.g., “male” or “female”).');
define('INDIVIDUALCOUNTTIP','Not generally used at OSU');
define('SAMPLINGPROTOCOLTIP','Not generally used at OSU');
define('PREPARATIONSTIP','Not generally used at OSU');
define('REPRODUCTIVECONDITIONTIP','Enter phenology here, if you have it.');
define('BEHAVIORTIP','Field disabled. Not used at OSU.');
define('VITALITYTIP','Not generally used at OSU');
define('ESTABLISHMENTMEANSTIP','Default value is “wild collection” to indicate the collection is naturally occurring (this includes self-propagating garden weeds). 
Other options are:
  * “cultivated” if the specimen was planted.
  * “wild seed” if the specimen was planted using seed from a natural occurrence.
  * “greenhouse weed” if the specimen was growing wild inside of a greenhouse.
  * “uncertain” if whether it was wild or cultivated is unclear.');
define('CULTIVATIONSTATUSTIP','This should be checked if the collection was from a cultivated plant. Also put “cultivated” in establishment means.');

// Curation
define('TYPESTATUSTIP','The type status (e.g., holotype, isotype) of the specimen. This field should only contain data if the specimen is a type, noted on the specimen.');
define('DISPOSITIONTIP','Field disabled. Leave this field blank, it will be filled in at the herbarium.');
define('OCCURRENCEIDTIP','Field disabled. Leave this field blank, it will be filled in at the herbarium.');
define('FIELDNUMBERTIP','Not generally used at OSU.');
define('BASISOFRECORDTIP','Do not edit this field. It will be set to Human Observation, until accessioned into an herbarium.');
define('LANGUAGETIP','The language of the label information, if other than English. Use RFC 5646 codes (e.g., “en” for English, “es” for Spanish).');
define('LABELPROJECTTIP','Use this field to create headings or label projects (e.g., “OSU 2023 Foray”) that you can search on.');
define('DUPLICATEQUANTITYTIP','Record the number of duplicates if you collected multiple specimens; this will then populate when you print your labels.');
define('INSTITUTIONCODETIP','Field disabled. Leave this blank. The herbarium will add it, if needed.');
define('COLLECTIONCODETIP','Field disabled. Leave this blank. The herbarium will add it, if needed.');
define('OWNERINSTITUTIONCODETIP','Field disabled. Leave this blank. The herbarium will add it, if needed.');
define('PROCESSINGSTATUSTIP','This field is used to indicate the record’s stage in the workflow. Use ‘Status Auto-set’ below to set the processing status for batches of records. You can decide how to use each of the processing statuses.');
define('DATAGENERALIZATIONSTIP','Field disabled. The herbarium will add it, if needed.');
define('STATUSAUTOSETTIP','Set this status once, and it sets all subsequent records to the same processing status. Note that this will OVERRIDE the processing status set in the processing status field itself.');

// Record Cloning
//define('CARRYOVERTIP','');
//define('RELATIONSHIPTIP','');
//define('TARGETCOLLECTIONTIP','');
//define('NUMBERRECORDSTIP','');
//define('PREPOPULATETIP','');
//define('CLONECATALOGNUMBERTIP','');

// Determinations
define('MAKECURRENTDETERMINATIONTIP','Check this box only if this is the most recent determination on the collection');
define('ANNOTATIONPRINTQUEUETIP','Check this box to add an annotation to the print queue to allow printing of a batch of annotations later.');
//define('SORTSEQUENCETIP','');


// OCR
//define('OCRWHOLEIMAGETIP','');
//define('OCRANALYSISTIP','');

// Batch Determinations
//define('DETERMINATIONTAXONTIP','');
//define('ANNOTATIONTYPETIP','');

// Occurrence Image Submission
//define('OCRTEXTTIP','');

?>
