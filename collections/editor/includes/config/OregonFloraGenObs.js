// Disable fields once page has loaded
document.addEventListener("DOMContentLoaded", function(event) { 

	document.getElementById("catalognumber").disabled = true;
	document.getElementById("othercatalognumbers").disabled = true;
	document.getElementsByName("verbatimeventdate")[0].disabled = true;
	document.getElementsByName("taxonremarks")[0].disabled = true;
	document.getElementsByName("locationid")[0].disabled = true;
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
});




