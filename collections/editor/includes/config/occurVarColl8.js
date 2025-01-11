// Javascript customizations for the OSC Vascular Plants collection. 

// Runs once the page has loaded
document.addEventListener("DOMContentLoaded", function(event) { 
	
	// Function to disable taxonomy fields to force use of Determination Jistory
	function disableTaxonomy() {
	        document.getElementsByName("sciname")[0].disabled = true;
	        document.getElementsByName("scientificnameauthorship")[0].disabled = true;
	        document.getElementsByName("identificationqualifier")[0].disabled = true;
	        document.getElementsByName("family")[0].disabled = true;
	        document.getElementsByName("identifiedby")[0].disabled = true;
	        document.getElementsByName("dateidentified")[0].disabled = true;
	        $("#scinameDiv").before( '<div style="font-weight: bold; font-style: italic;">This section is read-only. Use the Determination History tab');
	}

	// Disable the following fields, which we do not use
	document.getElementsByName("samplingprotocol")[0].disabled = true;
	document.getElementsByName("preparations")[0].disabled = true;
	//document.getElementsByName("")[0].disabled = true;
	//document.getElementById("").disabled = true;

	// Data entry fields that are especially important and need to be checked carefully for correctness
	let important = ['idname[]', 'idvalue[]','recordedby','recordnumber', 'eventdate', 'country', 
		'stateprovince', 'county', 'locality', 'decimallatitude', 'decimallongitude'];

	// Other common data entry fields
	let fields = ['associatedcollectors','verbatimeventdate','coordinateuncertaintyinmeters',
		'geodeticdatum', 'verbatimcoordinates', 'minimumelevationinmeters', 'maximumelevationinmeters', 
		'verbatimelevation', 'habitat', 'associatedtaxa', 'verbatimattributes', 'occurrenceremarks', 
		'establishmentmeans', 'language', 'processingstatus'];

	// Processing statuses to enable vouchervision field colors etc.
	let vvProcessingStatus = ['vouchervision qc'];

	// Add some customizations for VoucherVision processing statuses
	if( vvProcessingStatus.includes($("select[name='processingstatus']").val())) {

		// Disable taxonomy fields to force use of Determination History
		// Defined in occurVarOSUColls.js
		disableTaxonomy();

		// Color the fields according to importance and whether they are empty or not
		$(':input').each(function() {

			// Important fields in red
			if (important.includes($(this).attr('name'))) {
				$(this).css({"background-color": "#FFC5AD", "border": "1px solid gray"});

			// Empty fields data entry fields in green
			} else if ($(this).val() === '' && fields.includes($(this).attr('name'))) {
				$(this).css({"background-color": "#B0EEB0", "border": "1px solid gray"});

			// Non-empty data entry fields in yellow
			} else if (fields.includes($(this).attr('name'))) {				
				$(this).css({"background-color": "#FFFFA0", "border": "1px solid gray"});
			} else if ($(this).attr('name') === 'cultivationstatus' && $(this).attr('checked')) {
				$(this).css({"accent-color": "#FFFFA0", "box-shadow": "0 0 2px 1px gray"});
			}
		});
	}

});

