<?php 
// OSC Vascular Plants

//Enter one to many custom cascading style sheet files 
const CSSARR = array('occurVarDefault.css');

//Enter one to many custom java script files 
//const JSARR = array('example1.js','example2.js'); 
const JSARR = array('occurVarOSUColls.js', 'occurVarColl8.js'); 

//Custom Processing Status setting
const PROCESSINGSTATUS = [
'merge duplicate', 'skeletal entry', 'transfer data', 'pending review-nfn', 'reviewed', "vouchervision qc", // Main workflow
'expert required', 'thea', 'curator', // Quality Control
'pull specimen', 're-image', 'foreign language', 'de-accession', // Problems
'special request', 'unprocessed', 'label transcription', 'notes from nature', 'coge', 'quality control', 'Unprocessed - Fern Project', // Admin
'pending review', 'closed', 'unprocessed/NLP', 'stage 1', 'stage 2', 'stage 3']; // Unused Symbiota defaults

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
//define('VERBATIMATTRIBUTESLABEL','');
define('OCCURRENCEREMARKSLABEL','Notes (Occurrence Remarks)');
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
define('CATALOGNUMBERTIP','Usually OSC-V- followed by 6 digits. Herbarium sheets with more than one collection label or taxon on the sheet at any time in its determination history, should have a barcode for each, with clear indication to show which barcode represents which collection.');
define('OTHERCATALOGNUMBERSTIP','Original accession number of the specimen, usually stamped on, preceded by the herbarium code prefix (see below), e.g., OSC24228. Newer specimens do not have a stamped accession number and will only have a barcode > OSC-V-255000. Leave these fields blank for those.

Enter the number in the Additional Identifier Value field with the following prefix (without leading zeros): 
  * OSC for Oregon State University, Oregon State College, Oregon Agricultural College (e.g., OSC24228)
  * ORE for University of Oregon or UO Museum of Natural History (e.g., ORE1456)
  * WILLU for Willamette University (e.g., WILLU19746)

Then, click into the Tag Name field and select “Accession Number” from the dropdown.');
define('RECORDEDBYTIP','Primary or only collector name. This is often the name that follows “col.”, “leg.”, and “legit.” Omit preceding titles (e.g., Dr., Mr., Mrs., Miss) unless Mrs. is followed by husband’s name. If no collector is present, enter “none”.');
define('RECORDNUMBERTIP','If no number is present, enter “s.n.” (sine numero is Latin for “without number”). Include any letters or symbols in the collector number (e.g., KP-004b)');
define('EVENTDATETIP','Date of specimen collection. Enter in the format YYYY-MM-DD. If year, month, or day is unknown, replace missing information with zeros (e.g. 2004-00-00). Roman numerals always indicate the month. If there is no collection date enter “0000-00-00”.');
define('EVENTDATE2TIP','End date of specimen collection, if multiple dates are specified on the label. Enter in the format YYYY-MM-DD. If year, month, or day is unknown, replace missing information with zeros (e.g. 2004-00-00). Roman numerals always indicate the month. 

If there is only one date specified on the label, enter it as the start date, and leave this blank.');
define('DUPLICATESTIP','Search for duplicate specimens in other herbarium collections to import data from.');
define('ASSOCIATEDCOLLECTORSTIP','Collectors following the primary collector. Each name should be in order of “First name Last name.”, separated by commas. Exclude other words (e.g., “and”, “with”, “&”). Remove preceding titles (see also Collector).');
define('VERBATIMEVENTDATETIP','Date as it appears on the label (e.g., 7-3 ‘82, date in another language, a range of dates)');

// Exsiccati
//define('EXSICCATITITLETIP','');
//define('EXSICCATINUMBERTIP','');

// Latest Identification
define('SCIENTIFICNAMETIP','The scientific name on the original label (often not the latest identification!), even if this name is a genus or family. Use “ssp.” for subspecies and “var.” for variety.
  * Select an option from the dropdown list rather than typing out the name when possible. 
  * If there are notes or references, click the pencil beside Date Identified to add them. 
  * If this name is the current determination, and the specimen is filed under a different name than the name on the specimen, this information should be noted in the Specimen Location (Disposition) field.
  * If there is no scientific name on the original label, leave it blank.
  * Do not enter authors in the Scientific name field. For example, Poa bulbosa L. var. concinna Beck” should be transcribed as “Poa bulbosa var. concinna”.
  * See Appendices 5 and 6 of Transcription Guide for further explanations with examples.');
define('SCIENTIFICNAMEAUTHORSHIPTIP','Not in use for transcription; this field will autofill if the taxon is in the dictionary of scientific names.');
define('IDCONFIDENCETIP','Not used during label transcription at OSU.');
define('IDENTIFICATIONQUALIFIERTIP','The determiner’s expression of uncertainty in their identification, if listed on the label along with the scientific name. Examples are “?”, “aff.”, “cf.”, “possibly”, “probably”, etc.).');
define('FAMILYTIP','Not in use for transcription; this field will autofill if the taxon is in the dictionary of scientific names.');
define('IDENTIFIEDBYTIP','The name of the person who identified the collection, according to the original label. Default to the collector unless “Determined”, “Det.”, or “Det. By” signifies a different person.');
define('DATEIDENTIFIEDTIP','The date of original identification on the original label, if identified by someone other than the collector. Enter as YYYY-MM-DD (see collection date).');
define('IDENTIFICATIONREFERENCETIP','Publication reference(s) used in the identification. If multiple references are given, separate them with “ | “.');
define('IDENTIFICATIONREMARKSTIP','Comments or notes about the identification (e.g., identifying traits, reasons for this ID)');
define('TAXONREMARKSTIP','Field disabled. Not used during label transcription at OSU.');

// Locality & Georeferencing
//define('CONTINENTTIP', '');
//define('WATERBODYTIP', '');
//define('ISLANDGROUPTIP', '');
//define('ISLANDTIP', '');
define('COUNTRYTIP','The country where the specimen was collected. Spell out in full (“United States”, not “U.S.”). If country is not listed, enter “none”.');
define('STATEPROVINCETIP','The state (USA) or province (Canada) where the specimen was collected. Enter as a full name rather than an abbreviation (e.g., “California” instead of “CA”). If none is listed, enter “none”.');
define('COUNTYTIP','The county (parish in Louisiana) in which the specimen was collected. 
  * Don’t include things like “Co.” or “County”.
  * Select from dropdown list for USA & Canada specimens. 
  * If no county is listed, enter “none”. 
  * If county was added as an annotation, enter the county here, and enter “county interpreted” in the Transcription Notes field.');
define('MUNICIPALITYTIP','Not generally used at OSU, but record for specimens from Mexico.');
define('LOCATIONIDTIP','Field disabled. Not in use for OSU collections.');
define('LOCALITYTIP','The geographic description of where the specimen was collected. This should not include country, state, or county information, unless it is necessary for context of the locality (e.g., “southwest corner of Jefferson county” or “northeast Oregon”). Only capitalize pronouns.');
define('LOCATIONREMARKSTIP','Not generally used at OSU.');
define('LOCALITYAUTOLOOKUPTIP','Unless deactivated, when you type in a locality, it will try to autocomplete with previously entered localities that also match the collector and date. If you select one of those matches, it will fill in any associated data (lat/long, elevation, habitat, etc.)');
define('LOCALITYSECURITYTIP','Not used during label transcription at OSU.');
define('LOCALITYSECURITYREASONTIP','Not used during label transcription at OSU.');
define('DECIMALLATITUDETIP','If Latitude is stated on the label in decimal format, enter here. If on the label as degrees/minutes/seconds, TRS or UTM, click the F (format) button, enter in the respective fields and click the Insert Values button. This auto-populates the Verbatim Coordinates and Lat/Long fields.');
define('DECIMALLONGITUDETIP','If Longitude is stated on the label in decimal format, enter here. If on the label as degrees/minutes/seconds, TRS or UTM, click the F (format) button, enter in the respective fields and click the Insert Values button. This auto-populates the Verbatim Coordinates and Lat/Long fields.');
define('COORDINATEUNCERTAINITYINMETERSTIP','If GPS uncertainty is given, enter it here in meters. Otherwise, leave it blank.');
define('GOOGLEMAPSTIP','Google maps tool for georeferencing. Not generally used during label transcription at OSU');
define('GEOLOCATETIP','GeoLocate tool for georeferencing. Not generally used during label transcription at OSU');
define('COORDCLONETIP','Coordinate cloning tool for georeferencing. Not generally used during label transcription at OSU');
define('GEOTOOLSTIP','Click to view additional coordinate fields of these types:
  * Latitude/Longitude if given in degrees, minutes, seconds
  * UTM
  * TRS ');
define('GEODETICDATUMTIP','If the coordinate datum is stated on the label (e.g., WGS84, NAD83) enter here. Otherwise, leave it blank.');
define('VERBATIMCOORDINATESTIP','Do not enter data directly into this field. If the label uses degrees/minutes/seconds, UTM’s or TRS coordinate systems, click the F (format) button to show fields specialized for capturing these data. Once entered click the respective “Insert values” button below the entered coordinates. This autopopulates the Verbatim Coordinates field.');
define('RECALCULATECOORDSTIP','Do not use this to convert verbatim coordinates. It is better to use the conversion provided by clicking the F button.');
define('ELEVATIONINMETERSTIP','Enter elevation under Verbatim Elevation field. This field will autopopulate.');
define('MINELEVATIONINMETERSTIP','Minimum elevation. Please enter with the Verbatim Elevation field, and click <<. This field will autopopulate.');
define('MAXELEVATIONINMETERSTIP','Maximum elevation. Please enter with the Verbatim Elevation field, and click <<. This field will autopopulate.');
define('RECALCULATEELEVTIP','Converts the verbatim elevation field to elevation in meters.');
define('VERBATIMELEVATIONTIP','Enter elevation followed by units (ft or m). then click << to the left of this field to autopopulate Elevation in Meters field.');
define('DEPTHINMETERSTIP','Not generally used in OSU Vascular Plants, but record if given.');
define('MINDEPTHINMETERSTIP','Minimum depth. Not generally used in OSU Vascular Plants, but record if given.');
define('MAXDEPTHINMETERSTIP','Maximum depth. Not generally used in OSU Vascular Plants, but record if given.');
define('VERBATIMDEPTHTIP','Not generally used in OSU Vascular Plants, but record if given.');
define('GEOREFERENCEBYTIP','Not used during label transcription at OSU.');
define('GEOREFERENCESOURCESTIP','Not used during label transcription at OSU.');
define('GEOREFERENCEREMARKSTIP','Not used during label transcription at OSU.');
define('GEOREFERENCEPROTOCOLTIP','Not used during label transcription at OSU.');
define('GEOREFERENCEVERIFICATIONSTATUSTIP','Not used during label transcription at OSU.');
define('GOOGLEMAPSPOLYGONTIP','Tool to georeference a location as a polygon. Not used during label transcription at OSU.');
define('FOOTPRINTWKTTIP','Not used during label transcription at OSU.');

// Misc
define('HABITATTIP','Environmental conditions in which the plant was found (e.g., marsh, grassy field). Include community types (e.g., “Douglas fir forest”). Sometimes it is necessary to repeat a part of the location phrase to provide needed context.');
define('SUBSTRATETIP','Only used for epiphytes (such as tree dwelling ferns and mistletoe, or a host of species growing in soil whose roots associate with other species for energy and nutrients). Descriptions of soil type or rock formations belong under habitat.');
//define('HOSTTIP','');
define('ASSOCIATEDTAXATIP','Other plant taxa listed as growing with the specimen (species associated with community types belong in Habitat e.g., “Douglas fir forest”). Include common names if given on the label. Enter all names separated by a comma, excluding additional words (and, with, &). For quick entry, enter the first three letters of the genus, followed by a space, and the first three letters of the species.');
define('ASSOCIATEDTAXAAIDTIP','This pops up a helper box to add associated taxa names.');
define('VERBATIMATTRIBUTESTIP','Information specific to the individual plant(s) as noted on the label (e.g., size, condition, color)');
define('OCCURRENCEREMARKSTIP','Add any additional data on the label that does not fit into the other data fields, including frequency (common, scattered, rare). Separate disparate information with a semicolon.');
define('DYNAMICPROPERTIESTIP','Not generally used at OSU.');
define('LIFESTAGETIP','Field disabled. Not in use for OSU collections.');
define('SEXTIP','Enter the gender of the specimen if noted on the original label (e.g., “male” or “female”).');
define('INDIVIDUALCOUNTTIP','Field disabled. Not in use for OSU collections.');
define('SAMPLINGPROTOCOLTIP','Field disabled. Not used in OSU Vascular Plants.');
define('PREPARATIONSTIP','Field disabled. Not used in OSU Vascular Plants.');
define('REPRODUCTIVECONDITIONTIP','If the phenology (life stage of the specimen such as “flowering” or “fruiting”) is stated on the label, enter it here.');
define('BEHAVIORTIP','Field disabled. Not in use for OSU collections.');
define('VITALITYTIP','Field disabled. Not in use for OSU collections.');
define('ESTABLISHMENTMEANSTIP','Default value is “wild collection” to indicate the collection is naturally occurring (this includes self-propagating garden weeds). 
Other options are:
  * “cultivated” if the specimen was planted.
  * “wild seed” if the specimen was planted using seed from a natural occurrence.
  * “greenhouse weed” if the specimen was growing wild inside of a greenhouse.
  * “uncertain” if whether it was wild or cultivated is unclear.

If cultivated, also tick the Cultivated/Captive box.');
define('CULTIVATIONSTATUSTIP','This should be checked if the label indicates that the collection was from a cultivated plant. Also put “cultivated” in establishment means');

// Curation
define('TYPESTATUSTIP','The type status (e.g., holotype, isotype) of the specimen. This field should only contain data if the specimen is a type, noted on the specimen.');
define('DISPOSITIONTIP','The location of the specimen in the herbarium, normally left blank. Other entries are “missing” and, “filed under” (used when the sheet has multiple taxa, “filed under” is followed by the taxon where it is filed). If the specimen is stored with boxed collections, enter “boxed”. Other locations can also be noted.');
define('OCCURRENCEIDTIP','Field disabled. Not in use for OSU collections.');
define('FIELDNUMBERTIP','Field disabled. Not in use for OSU collections.');
define('BASISOFRECORDTIP','Field disabled and autofilled. Should always be &quot;PreservedSpecimen&quot; for OSU collections.');
define('LANGUAGETIP','The language of the label information, if other than English. Use RFC 5646 codes (e.g., “en” for English, “es” for Spanish).');
define('LABELPROJECTTIP','If the heading on a label denotes a specific project other than flora of a specific place (e.g., “Oregon Flora Project”), enter it here.');
define('DUPLICATEQUANTITYTIP','Not generally used at OSU, but record if the number of duplicates are given.');
define('INSTITUTIONCODETIP','Field disabled and autofilled. Should always be &quot;OSU&quot;.');
define('COLLECTIONCODETIP','Field disabled and autofilled. Should always be &quot;V&quot;.');
define('OWNERINSTITUTIONCODETIP','Do not edit this for OSU collections.');
define('PROCESSINGSTATUSTIP','This field is used to indicate the record’s stage in the workflow. Use ‘Status Auto-set’ below to set the processing status for batches of records. Generally if the specimen record has no issues, set it as “Reviewed”, If you there are issues you are unsure about, flag it as “Expert Required” and note what the issues are in the Transcription Notes field. The full list of processing statuses is provided in the data entry protocol.');
define('DATAGENERALIZATIONSTIP','Information derived from the transcription process which is not stated on the original label. Use this to report an issue with transcription that requires future investigation. Also used to indicate interpretations such as “county interpreted” if the county was derived from an annotation and not stated on the label or to show information from the original label that was later corrected by an annotation (such as original latitude: 78.823°).');
define('STATUSAUTOSETTIP','Set this status once, and it sets all subsequent records to the same processing status. 
Note: setting the processing status manually above using the Processing Status dropdown will override this Status Auto-Set.');

// Record Cloning
define('CARRYOVERTIP','CARRYOVERTIP');
define('RELATIONSHIPTIP','RELATIONSHIPTIP');
define('TARGETCOLLECTIONTIP','TARGETCOLLECTIONTIP');
define('NUMBERRECORDSTIP','NUMBERRECORDSTIP');
define('PREPOPULATETIP','PREPOPULATETIP');
define('CLONECATALOGNUMBERTIP','CLONECATALOGNUMBERTIP');

// Determinations
define('MAKECURRENTDETERMINATIONTIP','Check this box only if this is the most recent determination on the collection');
define('ANNOTATIONPRINTQUEUETIP','Not generally used during label transcription at OSU.');
define('SORTSEQUENCETIP','Not generally used during label transcription at OSU.');

// OCR
//define('OCRWHOLEIMAGETIP','');
//define('OCRANALYSISTIP','');

// Batch Determinations
//define('DETERMINATIONTAXONTIP','');
//define('ANNOTATIONTYPETIP','');

// Occurrence Image Submission
//define('OCRTEXTTIP','');

?>
