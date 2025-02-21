// Disable fields once page has loaded
document.addEventListener("DOMContentLoaded", function(event) { 

	document.getElementsByName("taxonremarks")[0].disabled = true;
	document.getElementsByName("locationid")[0].disabled = true;
	document.getElementsByName("lifestage")[0].disabled = true;
	document.getElementsByName("individualcount")[0].disabled = true;
	document.getElementsByName("behavior")[0].disabled = true;
	document.getElementsByName("vitality")[0].disabled = true;
	document.getElementsByName("occurrenceid")[0].disabled = true;
	document.getElementsByName("fieldnumber")[0].disabled = true;
	document.getElementsByName("institutioncode")[0].disabled = true;
	document.getElementsByName("collectioncode")[0].disabled = true;
	document.getElementsByName("basisofrecord")[0].disabled = true;


	// Disable if it is set to preserved specimen
	document.getElementsByName("basisofrecord")[0].onchange = function () {
		getElementsByName("basisofrecord")[0].disabled = this.value == 'PreservedSpecimen';
	}
	//document.getElementsByName("")[0].disabled = true;
	//document.getElementById("").disabled = true;

	// Hide georef extra div by default
	$('#georefExtraDiv').css({"display": "none"})

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

	// Highlight the additional identifier value field (accession number) in green for Merge Duplicate processing status
	// Only if empty
	if ($("select[name='processingstatus']").val() == 'merge duplicate') {
		if( !$('input[name="idvalue[]"]:first').val()) {
			$('input[name="idvalue[]"]:first').css({"background-color": "#B0EEB0", "border": "1px solid gray"});
		}
	}
});



