// Function to query the Index Herbariorum API, parse the data, and put into Symbiota fields
function indexherbariorum(formName) {

	// Get the form name to add data to
	var form = document.getElementById(formName);

	// Get the institution code to look up
	var code = form.elements["institutioncode"].value;

	// Check if the code is filled in, and exit if not
	if(!code) {
		alert('No herbarium code provided. Fill in a code first to get data from Index Herbariorum');
		return;
	}

	// If data already exists, warn the user before continuing
	if(formName == "insteditform" && !confirm("Warning, this will overwrite existing data. Ok to proceed?")) return;

	// Note that we are querying a local serverside API, to get around cross-site API access over non-https
	$.ajax({
		data: {code: code},
		url: "rpc/indexHerbariorum.php",
		dataType: "json",

		// Function to run on a successful API call
		success: function (result, status, xhr) {

			// Check if no hits are found
			if(result.hits == 0 || !result.irn) {
				alert("No herbarium found for code: " + code);
				return;
			}

			// Check if more than one hit is found. Shouldn't happen, but just in case
			if(result.hits > 1) {
				alert("Error: Multiple herbaria found for code: " + code);
				return;
			}		

			// Show full API result for debugging
			//console.log(result);

			// Set Institution Name
			if(result.organization) form.elements["institutionname"].value = result.organization;

			// Set Institution Name2:
			// If either division or department are not filled in, then just use whichever is filled in
			if(result.division == '' || result.department == '') {
				form.elements["institutionname2"].value = result.division + result.department;

			// Otherwise, concatenate the two with a comma
			} else {
				form.elements["institutionname2"].value = result.division + ", " + result.department;
			}

			// Check for multi-line addresses, and split if possible
			// split by commas
			if(result.address.postalStreet.includes(",")){

				// Split the street address with commas. 
				var addrArr = result.address.postalStreet.split(",");

				// Set Address 1 to the first part of the address before a comma
				form.elements["address1"].value = addrArr[0];

				// Set Address 2 to everything else:
				addrArr.shift();
				form.elements["address2"].value = addrArr.join(", ").trim();

			// split by semicolons
			} else if(result.address.postalStreet.includes(";")){

				// Split the street address with commas. 
				var addrArr = result.address.postalStreet.split(";");

				// Set Address 1 to the first part of the address before a comma
				form.elements["address1"].value = addrArr[0];

				// Set Address 2 to everything else:
				addrArr.shift();
				form.elements["address2"].value = addrArr.join(", ").trim();

			} else {

				// Set Address:
				if(result.address.postalStreet) form.elements["address1"].value = result.address.postalStreet;
			}

			// Set City:
			if(result.address.postalCity) form.elements["city"].value = result.address.postalCity;

			// Set State/Province to its abbreviation, if supported
			if(result.address.postalState) form.elements["stateprovince"].value = abbrState(result.address.postalState);

			// Set Postal Code:
			if(result.address.postalZipCode) form.elements["postalcode"].value = result.address.postalZipCode;

			// Set Country:
			if(result.address.postalCountry) form.elements["country"].value = result.address.postalCountry;

			// Set Phone:
			if(result.contact.phone) form.elements["phone"].value = result.contact.phone;

			// Get corresponding contacts by querying the staff API 
			$.ajax({
				data: {code: code, correspondent: "yes"},
				url: "rpc/indexHerbariorum.php",
				dataType: "json",
				success: function (result, status, xhr) {

					// Show full API result for debugging
					//console.log(result)

					// Combine the correspondants into a list
					var contacts = "";
					result.data.forEach(element => 
						contacts += element.firstName + ' ' + element.lastName + " (" + element.position +"), "
					);

					// Remove trailing comma from the last contact
					contacts = contacts.substring(0, contacts.length - 2)

					// Set Contact
					if(contacts) form.elements["contact"].value = contacts;
				}
			});

			// Set Email:
			if(result.contact.email) form.elements["email"].value = result.contact.email;

			// Set URL:
			if(result.contact.webUrl) form.elements["url"].value = result.contact.webUrl;

			// Set Notes (this inclues a number of fields)
			var notes = "";
			if(result.notes) notes += result.notes;

			// Add taxonomic coverage to notes, if included
			if(result.taxonomicCoverage) notes += "<br/><br/><strong>Taxonomic Coverage:</strong> " + result.taxonomicCoverage;

			// Add geography to notes, if included
			if(result.geography) notes += "<br/><br/><strong>Geography:</strong> " + result.geography;

			// Add incorporated herbaria to notes, if included
			if(result.incorporatedHerbaria) notes += "<br/><br/><strong>Incorporated Herbaria:</strong> " + result.incorporatedHerbaria;

			// Add specimen total, if included
			if(result.specimenTotal) notes += "<br/><br/><strong>Total Specimens:</strong> " + result.specimenTotal;

			// Add a link to Index Herbariorum to the notes
			notes += "<br/><br/><strong><a href=http://sweetgum.nybg.org/science/ih/herbarium-details/?irn=" + result.irn + " target=_blank>Index Herbariorum Link</a></strong>";
			form.elements["notes"].value = notes;
		
		}
	})
}

// Function to convert a full state/province name to an abbreviation
// https://gist.github.com/calebgrove/c285a9510948b633aa47
function abbrState(state){

    // United States
    var states = [
        ['Alabama', 'AL'],
        ['Alaska', 'AK'],
        ['American Samoa', 'AS'],
        ['Arizona', 'AZ'],
        ['Arkansas', 'AR'],
        ['Armed Forces Americas', 'AA'],
        ['Armed Forces Europe', 'AE'],
        ['Armed Forces Pacific', 'AP'],
        ['California', 'CA'],
        ['Colorado', 'CO'],
        ['Connecticut', 'CT'],
        ['Delaware', 'DE'],
        ['District Of Columbia', 'DC'],
        ['Florida', 'FL'],
        ['Georgia', 'GA'],
        ['Guam', 'GU'],
        ['Hawaii', 'HI'],
        ['Idaho', 'ID'],
        ['Illinois', 'IL'],
        ['Indiana', 'IN'],
        ['Iowa', 'IA'],
        ['Kansas', 'KS'],
        ['Kentucky', 'KY'],
        ['Louisiana', 'LA'],
        ['Maine', 'ME'],
        ['Marshall Islands', 'MH'],
        ['Maryland', 'MD'],
        ['Massachusetts', 'MA'],
        ['Michigan', 'MI'],
        ['Minnesota', 'MN'],
        ['Mississippi', 'MS'],
        ['Missouri', 'MO'],
        ['Montana', 'MT'],
        ['Nebraska', 'NE'],
        ['Nevada', 'NV'],
        ['New Hampshire', 'NH'],
        ['New Jersey', 'NJ'],
        ['New Mexico', 'NM'],
        ['New York', 'NY'],
        ['North Carolina', 'NC'],
        ['North Dakota', 'ND'],
        ['Northern Mariana Islands', 'NP'],
        ['Ohio', 'OH'],
        ['Oklahoma', 'OK'],
        ['Oregon', 'OR'],
        ['Pennsylvania', 'PA'],
        ['Puerto Rico', 'PR'],
        ['Rhode Island', 'RI'],
        ['South Carolina', 'SC'],
        ['South Dakota', 'SD'],
        ['Tennessee', 'TN'],
        ['Texas', 'TX'],
        ['US Virgin Islands', 'VI'],
        ['Utah', 'UT'],
        ['Vermont', 'VT'],
        ['Virginia', 'VA'],
        ['Washington', 'WA'],
        ['West Virginia', 'WV'],
        ['Wisconsin', 'WI'],
        ['Wyoming', 'WY'],
    ];

    // Canada
    var provinces = [
        ['Alberta', 'AB'],
        ['British Columbia', 'BC'],
        ['Manitoba', 'MB'],
        ['New Brunswick', 'NB'],
        ['Newfoundland', 'NF'],
        ['Northwest Territory', 'NT'],
        ['Nova Scotia', 'NS'],
        ['Nunavut', 'NU'],
        ['Ontario', 'ON'],
        ['Prince Edward Island', 'PE'],
        ['Quebec', 'QC'],
        ['Saskatchewan', 'SK'],
        ['Yukon', 'YT'],
    ];

    // Combine states and provinces
    var regions = states.concat(provinces);

    // Check for a case insensitive match in states and provinces
	const selectedState = regions.find(s =>
		s.find(x => x.toLowerCase() === state.toLowerCase())
	)

	// Return the unabbreviated name if no match is found
	if (!selectedState) return state;

	// Return the abbreviation for the match
	return selectedState
		//.filter(s => s.toLowerCase() !== state.toLowerCase())
		.filter(s => s.length == 2)
		.join("");
}