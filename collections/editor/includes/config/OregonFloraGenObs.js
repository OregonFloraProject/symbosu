// Disable fields once page has loaded
document.addEventListener("DOMContentLoaded", function(event) { 

	document.getElementById("catalognumber").disabled = true;
	//document.getElementById("othercatalognumbers").disabled = true;
	document.getElementsByName("verbatimeventdate")[0].disabled = true;
	document.getElementsByName("taxonremarks")[0].disabled = true;
	document.getElementsByName("locationid")[0].disabled = true;
	document.getElementsByName("behavior")[0].disabled = true;
	document.getElementsByName("disposition")[0].disabled = true;
	document.getElementsByName("occurrenceid")[0].disabled = true;
	document.getElementsByName("institutioncode")[0].disabled = true;
	document.getElementsByName("collectioncode")[0].disabled = true;
	document.getElementsByName("ownerinstitutioncode")[0].disabled = true;
	document.getElementsByName("datageneralizations")[0].disabled = true;
	document.getElementsByName("basisofrecord")[0].disabled = true;

	document.getElementsByName("basisofrecord")[0].onchange = function () {
		getElementsByName("basisofrecord")[0].disabled = this.value == 'HumanObservation';
	}
	//document.getElementsByName("")[0].disabled = true;


	// Synchronize Establishment Means and Cultivation Status
	$('input[name=cultivationstatus]').change(function () {
		if ($(this).prop('checked')) {
			// Synchronize establishment means: cultivated
			$("input[name=establishmentmeans]").val("cultivated");
		} else {
			// Synchronize establishment means: not cultivated
			$("input[name=establishmentmeans]").val("wild collection");
		}
	});
	$('input[name=establishmentmeans]').change(function () {
		if ($(this).val() === 'cultivated') {
			// Check the captive/cultivated box
			$('input[name=cultivationstatus]').prop('checked', true);
		} else {
			// Uncheck the captive/cultivated box
			$('input[name=cultivationstatus]').prop('checked', false);
		}
	});

	// Data entry fields that are especially important and required
	let required = ['recordedby', 'recordnumber',  'eventdate','sciname', 'scientificnameauthorship',
		'country', 'stateprovince', 'county', 'locality', 'decimallatitude', 'decimallongitude',];

	// Other common data entry fields
	let common = ['associatedcollectors', 'family', 'identifiedby', 'dateidentified',
		'geodeticdatum',  'coordinateuncertaintyinmeters','minimumelevationinmeters',
		'habitat', 'associatedtaxa', 'verbatimattributes', 'occurrenceremarks', 'labelproject',
		'duplicatequantity', 'processingstatus'];


	// Color the fields according to importance
	$(':input').each(function() {

		// Required fields in red
		if (required.includes($(this).attr('name'))) {
			$(this).css({"background-color": "#FFC5AD", "border": "1px solid gray"});

		// Non-empty data entry fields in yellow
		} else if (common.includes($(this).attr('name'))) {
			$(this).css({"background-color": "#FFFFA0", "border": "1px solid gray"});
		} else if ($(this).attr('name') === 'cultivationstatus') {
			$(this).css({"background-color": "#FFFFA0", "box-shadow": "0 0 2px 1px gray"});
		}
	});

	// Add a key for colorcoding
	let keyHTML = '<div id="colorKey" style="margin-bottom: 5px;">Color Key: <input type="text" placeholder="Required" disabled style="background-color: #FFC5AD; width: 60px;"/> <input type="text" placeholder="Common" disabled style="background-color: #FFFFA0; width: 60px;"/></div>';
	$('#catalogNumberDiv').prepend(keyHTML);
});
