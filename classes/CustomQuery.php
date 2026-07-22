<?php
include_once('utilities/Language.php');

class CustomQuery {
	const MAX_CUSTOM_INPUTS = 8;

	const OPERATOR_OPTIONS = [
		'EQUALS' => '=',
		'NOT_EQUALS' => '!=',
		'STARTS_WITH' => 'LIKE',
		'LIKE' => 'LIKE',
		'NOT_LIKE' => 'NOT LIKE',
		'GREATER_THAN' => '>',
		'LESS_THAN' => '<',
		'IS_NULL' => 'IS NULL',
		'NOT_NULL' => 'IS NOT NULL'
	];

	private static function parse_request(array $request, array $fieldFilter = []): array {
		if(!count($request)) {
			$request = $_REQUEST;
		}

		$customQueryRequest = [];

		$map = [
			'q_customandor' => [
				'field' => 'andor',
				'predicate' => fn($v) => ($v == 'AND' || $v== 'OR')
			],
			'q_customopenparen' => [
				'field' => 'openparen',
				'predicate' => fn($v) => preg_match('/^\({1,3}$/', $v)
			],
			'q_customfield' => [
				'field' => 'field',
				'predicate' => fn($v) => array_key_exists($v, $fieldFilter)
			],
			'q_customtype' => [
				'field' => 'term',
				'predicate' => fn($v) => array_key_exists($v, self::OPERATOR_OPTIONS)
			],
			'q_customvalue' => [
				'field' => 'value',
			],
			'q_customcloseparen' => [
				'field' => 'closeparen',
				'predicate' => fn($v) => preg_match('/^\){1,3}$/', $v)
			],
		];

		for($i = 1; $i <= self::MAX_CUSTOM_INPUTS; $i++) {
			$customValue = [];

			foreach($map as $key => $mapping) {
				if(($v = $request[$key . $i] ?? null) && (!isset($mapping['predicate']) || $mapping['predicate']($v))) {
					$customValue[$mapping['field']] = $v;
				}
			}

			$field = $customValue['field'] ?? null;
			$term = $customValue['term'] ?? null;
			$value = $customValue['value'] ?? null;

			if($field && $term && ($value || in_array($term, ['IS_NULL', 'NOT_NULL']))) {
				$customQueryRequest[$i] = $customValue;
			}
		}

		return $customQueryRequest;
	}

	static function buildCustomWhere(array $request, $tablePrefix ='', array $fieldFilter = []): array {
		if(!count($fieldFilter)) {
			$fieldFilter = self::getOccurrenceFields();
		}

		$customQueryRequest = self::parse_request($request, $fieldFilter);

		$sql = '';
		$binds = [];
		foreach($customQueryRequest as $customValue) {
			$field = $customValue['field'] ?? null;
			$andOr = $customValue['andor'] ?? null;
			$compareOperator = self::OPERATOR_OPTIONS[$customValue['term']] ?? null;
			$openParen = $customValue['openparen'] ?? '';
			$closeParen = $customValue['closeparen'] ?? '';

			if($field && $compareOperator) {
				if($sql) {
					if($andOr === 'AND') {
						$sql .= 'AND ';
					} else if($andOr) {
						$sql .= 'OR ';
					}
				}

				$bindValue = true;

				if($customValue['term'] === 'STARTS_WITH') {
					$binds[] = $customValue['value'] . '%';
				} else if($customValue['term'] === 'NOT_LIKE' || $customValue['term'] === 'LIKE') {
					$binds[] = '%' . $customValue['value'] . '%';
				} else if(!in_array($customValue['term'], ['IS_NULL', 'NOT_NULL'])) {
					$binds[] = $customValue['value'];
				} else {
					$bindValue = false;
				}

				$sql .= $openParen .
					($tablePrefix? $tablePrefix . '.': '') . $field . ' ' . $compareOperator . ($bindValue? ' ?':'') .
				$closeParen . ' ';
			}
		}

		return [
			'sql' => $sql,
			'bindings' => $binds
		];
	}

	static function getOccurrenceFields(): array {
		global $LANG;
		Language::load('collections/editor/includes/queryform');
		//(defined('LABEL') ? LABEL : 
		return array(
			'absoluteAge'=> $LANG['ABS_AGE'],
			'associatedCollectors'=> (defined('ASSOCIATEDCOLLECTORSLABEL') ? ASSOCIATEDCOLLECTORSLABEL : $LANG['ASSOC_COLLECTORS']),
			'associatedOccurrences'=> $LANG['ASSOC_OCCS'],
			'associatedTaxa'=> (defined('ASSOCIATEDTAXALABEL') ? ASSOCIATEDTAXALABEL : $LANG['ASSOC_TAXA']),
			'attributes' => $LANG['ATTRIBUTES'],
			'scientificNameAuthorship' => (defined('SCIENTIFICNAMEAUTHORSHIPLABEL') ? SCIENTIFICNAMEAUTHORSHIPLABEL : $LANG['AUTHOR']),
			'basisOfRecord'=> (defined('BASISOFRECORDLABEL') ? BASISOFRECORDLABEL : $LANG['BASIS_OF_RECORD']),
			'bed'=>$LANG['BED'],
			'behavior'=> (defined('BEHAVIORLABEL') ? BEHAVIORLABEL : $LANG['BEHAVIOR']),
			'biostratigraphy'=>$LANG['BIOSTRAT'],
			'biota' => $LANG['BIOTA'],
			'catalogNumber'=> (defined('CATALOGNUMBERLABEL') ? CATALOGNUMBERLABEL : $LANG['CAT_NUM']),
			'collectionCode'=> (defined('COLLECTIONCODELABEL') ? COLLECTIONCODELABEL : $LANG['COL_CODE']),
			'recordNumber'=> (defined('RECORDNUMBERLABEL') ? RECORDNUMBERLABEL : $LANG['COL_NUMBER']),
			'recordedBy'=> (defined('RECORDEDBYLABEL') ? RECORDEDBYLABEL : $LANG['COL_OBS']),
			'continent'=>(defined('CONTINENTLABEL') ? CONTINENTLABEL : $LANG['CONTINENT']),
			'coordinateUncertaintyInMeters'=> (defined('COORDINATEUNCERTAINITYINMETERSLABEL') ? COORDINATEUNCERTAINITYINMETERSLABEL : $LANG['COORD_UNCERT_M']),
			'country'=> (defined('COUNTRYLABEL') ? COUNTRYLABEL : $LANG['COUNTRY']),
			'county'=> (defined('COUNTYLABEL') ? COUNTYLABEL : $LANG['COUNTY']),
			'cultivationStatus'=> (defined('CULTIVATIONSTATUSLABEL') ? CULTIVATIONSTATUSLABEL : $LANG['CULT_STATUS']),
			'dataGeneralizations'=> (defined('DATAGENERALIZATIONSLABEL') ? DATAGENERALIZATIONSLABEL : $LANG['DATA_GEN']),
			'eventDate'=> (defined('EVENTDATELABEL') ? EVENTDATELABEL : $LANG['DATE']),
			'eventDate2'=> (defined('EVENTDATE2LABEL') ? EVENTDATE2LABEL : $LANG['DATE2']),
			'dateEntered'=>$LANG['DATE_ENTERED'],
			'dateLastModified'=>$LANG['DATE_LAST_MODIFIED'],
			'dbpk'=> $LANG['DBPK'],
			'decimalLatitude'=> (defined('DECIMALLATITUDELABEL') ? DECIMALLATITUDELABEL : $LANG['DEC_LAT']),
			'decimalLongitude'=> (defined('DECIMALLONGITUDELABEL') ? DECIMALLONGITUDELABEL : $LANG['DEC_LONG']),
			'maximumDepthInMeters'=> $LANG['DEPTH_MAX'],
			'minimumDepthInMeters'=>$LANG['DEPTH_MIN'],
			'verbatimAttributes'=> (defined('VERBATIMATTRIBUTESLABEL') ? VERBATIMATTRIBUTESLABEL : $LANG['DESCRIPTION']),
			'disposition'=> (defined('DISPOSITIONLABEL') ? DISPOSITIONLABEL : $LANG['DISPOSITION']),
			'dynamicProperties'=> (defined('DYNAMICPROPERTIESLABEL') ? DYNAMICPROPERTIESLABEL : $LANG['DYNAMIC_PROPS']),
			'earlyInterval'=>$LANG['EARLY_INT'],
			'element'=>$LANG['ELEMENT'],
			'maximumElevationInMeters'=> $LANG['ELEV_MAX_M'],
			'minimumElevationInMeters'=> $LANG['ELEV_MIN_M'],
			'establishmentMeans'=> (defined('ESTABLISHMENTMEANSLABEL') ? ESTABLISHMENTMEANSLABEL : $LANG['ESTAB_MEANS']),
			'family'=> (defined('FAMILYLABEL') ? FAMILYLABEL : $LANG['FAMILY']),
			'fieldNotes'=>$LANG['FIELD_NOTES'],
			'fieldnumber'=> (defined('FIELDNUMBERLABEL') ? FIELDNUMBERLABEL : $LANG['FIELD_NUMBER']),
			'formation'=>$LANG['FORMATION'],
			'geodeticDatum'=> (defined('GEODETICDATUMLABEL') ? GEODETICDATUMLABEL : $LANG['GEO_DATUM']),
			'georeferenceProtocol'=> (defined('GEOREFERENCEPROTOCOLLABEL') ? GEOREFERENCEPROTOCOLLABEL : $LANG['GEO_PROTOCOL']),
			'geologicalContextID'=>$LANG['GEO_CONTEXT_ID'],
			'georeferenceRemarks'=> (defined('GEOREFERENCEREMARKSLABEL') ? GEOREFERENCEREMARKSLABEL : $LANG['GEO_REMARKS']),
			'georeferenceSources'=> (defined('GEOREFERENCESOURCESLABEL') ? GEOREFERENCESOURCESLABEL : $LANG['GEO_SOURCES']),
			'georeferenceVerificationStatus'=> (defined('GEOREFERENCEVERIFICATIONSTATUSLABEL') ? GEOREFERENCEVERIFICATIONSTATUSLABEL : $LANG['GEO_VERIF_STATUS']),
			'georeferencedBy'=> (defined('GEOREFERENCEBYLABEL') ? GEOREFERENCEBYLABEL : $LANG['GEO_BY']),
			'lithogroup'=>$LANG['GROUP'],
			'habitat'=> (defined('HABITATLABEL') ? HABITATLABEL : $LANG['HABITAT']),
			'identificationQualifier'=> (defined('IDENTIFICATIONQUALIFIERLABEL') ? IDENTIFICATIONQUALIFIERLABEL : $LANG['ID_QUALIFIER']),
			'identificationReferences'=> (defined('IDENTIFICATIONREFERENCELABEL') ? IDENTIFICATIONREFERENCELABEL : $LANG['ID_REFERENCES']),
			'identificationRemarks'=> (defined('IDENTIFICATIONREMARKSLABEL') ? IDENTIFICATIONREMARKSLABEL : $LANG['ID_REMARKS']),
			'identifiedBy'=> (defined('IDENTIFIEDBYLABEL') ? IDENTIFIEDBYLABEL : $LANG['IDED_BY']),
			'individualCount'=> (defined('INDIVIDUALCOUNTLABEL') ? INDIVIDUALCOUNTLABEL : $LANG['IND_COUNT']),
			'identifierName' => $LANG['IDENTIFIER_TAG_NAME'],
			'identifierValue' => $LANG['IDENTIFIER_TAG_VALUE'],
			'informationWithheld'=>$LANG['INFO_WITHHELD'],
			'institutionCode'=> (defined('INSTITUTIONCODELABEL') ? INSTITUTIONCODELABEL : $LANG['INST_CODE']),
			'island'=> (defined('ISLANDLABEL') ? ISLANDLABEL : $LANG['ISLAND']),
			'islandgroup'=> (defined('ISLANDGROUPLABEL') ? ISLANDGROUPLABEL : $LANG['ISLAND_GROUP']),
			'labelProject'=> (defined('LABELPROJECTLABEL') ? LABELPROJECTLABEL : $LANG['LAB_PROJECT']),
			'language'=> (defined('LANGUAGELABEL') ? LANGUAGELABEL : $LANG['LANGUAGE']),
			'lateInterval'=>$LANG['LATE_INT'],
			'lifeStage'=> (defined('LIFESTAGELABEL') ? LIFESTAGELABEL : $LANG['LIFE_STAGE']),
			'lithology'=>$LANG['LITHOLOGY'],
			'locationid'=> (defined('LOCATIONIDLABEL') ? LOCATIONIDLABEL : $LANG['LOCATION_ID']),
			'locality'=> (defined('LOCALITYLABEL') ? LOCALITYLABEL : $LANG['LOCALITY']),
			'recordSecurity'=> (defined('RECORDSECURITYLABEL') ? RECORDSECURITYLABEL : $LANG['SECURITY']),
			'securityReason'=> (defined('SECURITYREASONLABEL') ? SECURITYREASONLABEL : $LANG['SECURITY_REASON']),
			'localStage'=>$LANG['LOCAL_STAGE'],
			'locationRemarks'=> (defined('LOCATIONREMARKSLABEL') ? LOCATIONREMARKSLABEL : $LANG['LOC_REMARKS']),
			'member'=>$LANG['MEMBER'],
			'username'=>$LANG['MODIFIED_BY'],
			'municipality'=> (defined('MUNICIPALITYLABEL') ? MUNICIPALITYLABEL : $LANG['MUNICIPALITY']),
			'occurrenceRemarks'=> (defined('OCCURRENCEREMARKSLABEL') ? OCCURRENCEREMARKSLABEL : $LANG['NOTES_REMARKS']),
			'ocrFragment'=>$LANG['OCR_FRAGMENT'],
			'otherCatalogNumbers'=> (defined('OTHERCATALOGNUMBERSLABEL') ? OTHERCATALOGNUMBERSLABEL : $LANG['OTHER_CAT_NUMS']),
			'ownerInstitutionCode'=> (defined('OWNERINSTITUTIONCODELABEL') ? OWNERINSTITUTIONCODELABEL : $LANG['OWNER_CODE']),
			'preparations'=> (defined('PREPARATIONSLABEL') ? PREPARATIONSLABEL : $LANG['PREPARATIONS']),
			'reproductiveCondition'=> (defined('REPRODUCTIVECONDITIONLABEL') ? REPRODUCTIVECONDITIONLABEL : $LANG['REP_COND']),
			'samplingEffort'=>$LANG['SAMP_EFFORT'],
			'samplingProtocol'=> (defined('SAMPLINGPROTOCOLLABEL') ? SAMPLINGPROTOCOLLABEL : $LANG['SAMP_PROTOCOL']),
			'sciname'=> (defined('SCIENTIFICNAMELABEL') ? SCIENTIFICNAMELABEL : $LANG['SCI_NAME']),
			'sex'=> (defined('SEXLABEL') ? SEXLABEL : $LANG['SEX']),
			'slideProperties'=>$LANG['SLIDE_PROP'],
			'stateProvince'=> (defined('STATEPROVINCELABEL') ? STATEPROVINCELABEL : $LANG['STATE_PROVINCE']),
			'stratRemarks'=>$LANG['STRAT_REMARKS'],
			'substrate'=> (defined('SUBSTRATELABEL') ? SUBSTRATELABEL : $LANG['SUBSTRATE']),
			'taxonEnvironment'=>$LANG['TAXON_ENVIRONMENT'],
			'taxonRemarks'=> (defined('TAXONREMARKSLABEL') ? TAXONREMARKSLABEL : $LANG['TAXON_REMARKS']),
			'typeStatus'=> (defined('TYPESTATUSLABEL') ? TYPESTATUSLABEL : $LANG['TYPE_STATUS']),
			'verbatimCoordinates'=> (defined('VERBATIMCOORDINATESLABEL') ? VERBATIMCOORDINATESLABEL : $LANG['VERBAT_COORDS']),
			'verbatimEventDate'=> (defined('VERBATIMEVENTDATELABEL') ? VERBATIMEVENTDATELABEL : $LANG['VERBATIM_DATE']),
			'verbatimDepth'=> (defined('VERBATIMDEPTHLABEL') ? VERBATIMDEPTHLABEL : $LANG['VERBATIM_DEPTH']),
			'verbatimElevation'=> (defined('VERBATIMELEVATIONLABEL') ? VERBATIMELEVATIONLABEL : $LANG['VERBATIM_ELE']),
			'waterbody'=> (defined('WATERBODYLABEL') ? WATERBODYLABEL : $LANG['WATER_BODY'])
		);
	}

	static function renderCustomInputs(array $customFields = []): void {
		global $SERVER_ROOT;

		if(!count($customFields)) {
			$CUSTOM_FIELDS = self::getOccurrenceFields();
		} else {
			$CUSTOM_FIELDS = $customFields;
		}

		$MAX_CUSTOM_INPUTS = self::MAX_CUSTOM_INPUTS;
		$CUSTOM_TERMS = array_keys(self::OPERATOR_OPTIONS);
		$CUSTOM_VALUES = self::parse_request(
			$_REQUEST,
			$CUSTOM_FIELDS,
		);

		include($SERVER_ROOT . '/collections/editor/includes/customInput.php');
	}
}
