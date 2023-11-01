// Disable fields once page has loaded
document.addEventListener("DOMContentLoaded", function(event) { 

	document.getElementsByName("taxonremarks")[0].disabled = true;
	document.getElementsByName("locationid")[0].disabled = true;

	
	document.getElementsByName("samplingprotocol")[0].disabled = true;
	document.getElementsByName("preparations")[0].disabled = true;
	document.getElementsByName("occurrenceid")[0].disabled = true;
	document.getElementsByName("fieldnumber")[0].disabled = true;
	document.getElementsByName("institutioncode")[0].disabled = true;
	document.getElementsByName("collectioncode")[0].disabled = true;
	document.getElementsByName("basisofrecord")[0].disabled = true;



	document.getElementsByName("basisofrecord")[0].onchange = function () {
		getElementsByName("basisofrecord")[0].disabled = this.value == 'PreservedSpecimen';
	}
	//document.getElementsByName("")[0].disabled = true;
	//document.getElementById("").disabled = true;
});




