// Disable fields once page has loaded
document.addEventListener("DOMContentLoaded", function(event) { 
	
	document.getElementsByName("samplingprotocol")[0].disabled = true; // Maybe enable
	document.getElementsByName("preparations")[0].disabled = true; // Maybe enable

	// Disable taxonomy fields to force use of Determination history
	document.getElementsByName("sciname")[0].disabled = true;
	document.getElementsByName("scientificnameauthorship")[0].disabled = true; 
	document.getElementsByName("identificationqualifier")[0].disabled = true;
	document.getElementsByName("family")[0].disabled = true;
	document.getElementsByName("identifiedby")[0].disabled = true;
	document.getElementsByName("dateidentified")[0].disabled = true;
	$("#scinameDiv").before( '<div style="font-weight: bold; font-style: italic;">This section is read-only. Use the Determination History tab to edit this data.</div>');


	let fields = ['associatedcollectors','verbatimeventdate','coordinateuncertaintyinmeters',
		'geodeticdatum', 'verbatimcoordinates', 'minimumelevationinmeters', 'maximumelevationinmeters', 
		'verbatimelevation', 'habitat', 'associatedtaxa', 'verbatimattributes', 'occurrenceremarks', 
		'establishmentmeans', 'language', 'processingstatus'];

	let important = ['othercatalognumbers','recordedby','recordnumber', 'eventdate', 'country', 
		'stateprovince', 'county', 'locality', 'decimallatitude', 'decimallongitude']

	if( $("select[name='processingstatus']").val() == 'vouchervision qc') {
		$(':input').each(function() {
			if (important.includes($(this).attr('name'))) {
				$(this).css({"background-color": "#FFC5AD", "border": "1px solid gray"});
			} else if ($(this).val() === '' && fields.includes($(this).attr('name'))) {
				$(this).css({"background-color": "#B0EEB0", "border": "1px solid gray"});
			} else if (fields.includes($(this).attr('name'))) {				
				$(this).css({"background-color": "#FFFFA0", "border": "1px solid gray"});
			} else if ($(this).attr('name') === 'cultivationstatus' && $(this).attr('checked')) {
				$(this).css({"accent-color": "#FFFFA0", "box-shadow": "0 0 2px 1px gray"});
			}
		});
	}


	//document.getElementsByName("")[0].disabled = true;
	//document.getElementById("").disabled = true;
});




