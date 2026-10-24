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

	// Color the fields according to importance
	function colorFields(target) {

		// Data entry fields that are especially important and required
		let required = ['recordedby', 'recordnumber',  'eventdate','sciname', 'country',
			'stateprovince', 'county', 'locality', 'decimallatitude', 'decimallongitude',];

		// Other common data entry fields
		let common = ['associatedcollectors', 'identifiedby', 'dateidentified', 'geodeticdatum',
			'coordinateuncertaintyinmeters','minimumelevationinmeters','habitat', 'associatedtaxa',
			'verbatimattributes', 'occurrenceremarks', 'cultivationstatus', 'labelproject',
			'duplicatequantity', 'processingstatus'];

		// Add the required class to required fields
		if (required.includes(target.attr('name'))) {
			target.addClass("required");

		// Add the common class to commonly used but not required fields
		} else if (common.includes(target.attr('name'))) {
			target.addClass("common");
		}
	}

	// Cycle through each field and apply the colors
	$(':input').each(function() {
		colorFields($(this));
	});

	// Add a key for color coding, replacing the barcode/catalog number field
	let keyHTML = '<div id="colorKey" style="font-weight: bold">Color Key: <input type="text" class="required" placeholder="Required" disabled/> <input type="text" class="common" placeholder="Common" disabled/></div>';
	$('#catalogNumberDiv').html(keyHTML);

	// Remove color coding as fields are filled out, and toggle it back on if they are emptied
	document.addEventListener('change', function (event) {
	    const target = $(event.target);

	    // Text inputs
	    if (target.is('input[type="text"], textarea')) {
	        if (target.val() !== '') {
	            target.removeClass("required common");
	            return;
	        }
	    }

	    // Cultivation status checkbox
	    if (target.is('input[type="checkbox"][name="cultivationstatus"]')) {
	        if (target.prop('checked')) {
	            target.removeClass("required common");
	            return;
	        }
	    }

	    // Processing status select. Turn on for Pending Review and No Set Status
	    if (target.is('select[name="processingstatus"]')) {
	        if (target.val() !== 'pending review' && target.val() !== '') {
	            target.removeClass("required common");
	            return;
	        }
	    }

	    // Re-color fields if it didn't match above.
	    colorFields(target);
	});
});
