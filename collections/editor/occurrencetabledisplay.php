<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceEditorManager.php');
include_once($SERVER_ROOT.'/content/lang/collections/editor/occurrencetabledisplay.'.$LANG_TAG.'.php');
header('Content-Type: text/html; charset='.$CHARSET);

$collId = array_key_exists('collid',$_REQUEST) ? filter_var($_REQUEST['collid'], FILTER_SANITIZE_NUMBER_INT) : false;
$recLimit = array_key_exists('reclimit', $_REQUEST) ? filter_var($_REQUEST['reclimit'], FILTER_SANITIZE_NUMBER_INT) : 1000;
$occIndex = array_key_exists('occindex', $_REQUEST) ? filter_var($_REQUEST['occindex'], FILTER_SANITIZE_NUMBER_INT) : 0;
$crowdSourceMode = array_key_exists('csmode', $_REQUEST) ? filter_var($_REQUEST['csmode'], FILTER_SANITIZE_NUMBER_INT) : 0;
$dynamicTable = array_key_exists('dynamictable', $_REQUEST) ? filter_var($_REQUEST['dynamictable'], FILTER_SANITIZE_NUMBER_INT) : 0;
$action = array_key_exists('submitaction', $_REQUEST) ? $_REQUEST['submitaction'] : '';

$occManager = new OccurrenceEditorManager();

if($crowdSourceMode) $occManager->setCrowdSourceMode(1);

$isEditor = 0;		//If not editor, edits will be submitted to omoccuredits table but not applied to omoccurrences
$displayQuery = 0;
$isGenObs = 0;
$collMap = array();
$recArr = array();
$headMap = array();

$qryCnt = 0;
$statusStr = '';

if($SYMB_UID){
	$occManager->setCollId($collId);
	$collMap = $occManager->getCollMap();
	if($IS_ADMIN || ($collId && array_key_exists('CollAdmin',$USER_RIGHTS) && in_array($collId,$USER_RIGHTS['CollAdmin']))){
		$isEditor = 1;
	}

	if($collMap && $collMap['colltype']=='General Observations') $isGenObs = 1;
	if(!$isEditor){
		if($isGenObs){
			if($collId && array_key_exists('CollEditor',$USER_RIGHTS) && in_array($collId,$USER_RIGHTS['CollEditor'])){
				//Approved General Observation editors can add records
				$isEditor = 2;
			}
			elseif($action){
				//Lets assume that Edits where submitted and they remain on same specimen, user is still approved
				 $isEditor = 2;
			}
			elseif($occManager->getObserverUid() == $SYMB_UID){
				//User can only edit their own records
				$isEditor = 2;
			}
		}
		elseif($collId && array_key_exists("CollEditor",$USER_RIGHTS) && in_array($collId,$USER_RIGHTS["CollEditor"])){
			$isEditor = 2;
		}
	}

	//Bring in config variables
	if($isGenObs){
		if(file_exists('includes/config/occurVarGenObs'.$SYMB_UID.'.php')){
			//Specific to particular collection
			include('includes/config/occurVarGenObs'.$SYMB_UID.'.php');
		}
		elseif(file_exists('includes/config/occurVarGenObsDefault.php')){
			//Specific to Default values for portal
			include('includes/config/occurVarGenObsDefault.php');
		}
	}
	else{
		if($collId && file_exists('includes/config/occurVarColl'.$collId.'.php')){
			//Specific to particular collection
			include('includes/config/occurVarColl'.$collId.'.php');
		}
		elseif(file_exists('includes/config/occurVarDefault.php')){
			//Specific to Default values for portal
			include('includes/config/occurVarDefault.php');
		}
		if($crowdSourceMode && file_exists('includes/config/crowdSourcingVar.php')){
			//Specific to Crowdsourcing
			include('includes/config/crowdSourcingVar.php');
		}
	}

	$headerMapBase = array(
		'institutioncode'=>defined('INSTITUTIONCODELABEL')?INSTITUTIONCODELABEL:'Institution Code (override)',
		'collectioncode'=>defined('COLLECTIONCODELABEL')?COLLECTIONCODELABEL:'Collection Code (override)',
		'ownerinstitutioncode'=> defined('OWNERINSTITUTIONCODELABEL')?OWNERINSTITUTIONCODELABEL:'Owner Code (override)',
		'catalognumber' => defined('CATALOGNUMBERLABEL')?CATALOGNUMBERLABEL:'Catalog Number',
		'othercatalognumbers' => defined('OTHERCATALOGNUMBERSLABEL')?OTHERCATALOGNUMBERSLABEL:'Other Catalog #',
		'family' => defined('FAMILYLABEL')?FAMILYLABEL:'Family',
		'identificationqualifier' => defined('IDENTIFICATIONQUALIFIERLABEL')?IDENTIFICATIONQUALIFIERLABEL:'ID Qualifier',
		'sciname' => defined('SCIENTIFICNAMELABEL')?SCIENTIFICNAMELABEL:'Scientific Name',
		'scientificnameauthorship' => defined('SCIENTIFICNAMEAUTHORSHIPLABEL')?SCIENTIFICNAMEAUTHORSHIPLABEL:'Author',
		'recordedby' => defined('RECORDEDBYLABEL')?RECORDEDBYLABEL:'Collector',
		'recordnumber' => defined('RECORDNUMBERLABEL')?RECORDNUMBERLABEL:'Number',
		'associatedcollectors' => defined('ASSOCIATEDCOLLECTORSLABEL')?ASSOCIATEDCOLLECTORSLABEL:'Associated Collectors',
		'eventdate' => defined('EVENTDATELABEL')?EVENTDATELABEL:'Event Date',
		'verbatimeventdate' => defined('VERBATIMEVENTDATELABEL')?VERBATIMEVENTDATELABEL:'Verbatim Date',
		'identificationremarks' => defined('IDENTIFICATIONREMARKSLABEL')?IDENTIFICATIONREMARKSLABEL:'Identification Remarks',
		'taxonremarks' => defined('TAXONREMARKSLABEL')?TAXONREMARKSLABEL:'Taxon Remarks',
		'identifiedby' => defined('IDENTIFIEDBYLABEL')?IDENTIFIEDBYLABEL:'Identified By',
		'dateidentified' => defined('DATEIDENTIFIEDLABEL')?DATEIDENTIFIEDLABEL:'Date Identified',
		'identificationreferences' => defined('IDENTIFICATIONREFERENCELABEL')?IDENTIFICATIONREFERENCELABEL:'Identification References',
		'country' => defined('COUNTRYLABEL')?COUNTRYLABEL:'Country',
		'stateprovince' => defined('STATEPROVINCELABEL')?STATEPROVINCELABEL:'State/Province',
		'county' => defined('COUNTYLABEL')?COUNTYLABEL:'County',
		'municipality' => defined('MUNICIPALITYLABEL')?MUNICIPALITYLABEL:'Municipality',
		'locality' => defined('LOCALITYLABEL')?LOCALITYLABEL:'Locality',
		'decimallatitude' => defined('DECIMALLATITUDELABEL')?DECIMALLATITUDELABEL:'Latitude',
		'decimallongitude' => defined('DECIMALLONGITUDELABEL')?DECIMALLONGITUDELABEL:'Longitude',
		'coordinateuncertaintyinmeters' => defined('COORDINATEUNCERTAINITYINMETERSLABEL')?COORDINATEUNCERTAINITYINMETERSLABEL:'Uncertainty In Meters',
		'verbatimcoordinates' => defined('VERBATIMCOORDINATESLABEL')?VERBATIMCOORDINATESLABEL:'Verbatim Coordinates',
		'geodeticdatum' => defined('GEODETICDATUMLABEL')?GEODETICDATUMLABEL:'Datum',
		'georeferencedby' => defined('GEOREFERENCEBYLABEL')?GEOREFERENCEBYLABEL:'Georeferenced By',
		'georeferenceprotocol' => defined('GEOREFERENCEPROTOCOLLABEL')?GEOREFERENCEPROTOCOLLABEL:'Georeference Protocol',
		'georeferencesources' => defined('GEOREFERENCESOURCESLABEL')?GEOREFERENCESOURCESLABEL:'Georeference Sources',
		'georeferenceverificationstatus' => defined('GEOREFERENCEVERIFICATIONSTATUSLABEL')?GEOREFERENCEVERIFICATIONSTATUSLABEL:'Georef Verification Status',
		'georeferenceremarks' => defined('GEOREFERENCEREMARKSLABEL')?GEOREFERENCEREMARKSLABEL:'Georef Remarks',
		'minimumelevationinmeters' => 'Elev. Min. (m)',
		'maximumelevationinmeters' => 'Elev. Max. (m)',
		'verbatimelevation' => defined('VERBATIMELEVATIONLABEL')?VERBATIMELEVATIONLABEL:'Verbatim Elev.',
		'minimumdepthinmeters' => 'Depth. Min. (m)',
		'maximumdepthinmeters' => 'Depth. Max. (m)',
		'verbatimdepth' => defined('VERBATIMDEPTHLABEL')?VERBATIMDEPTHLABEL:'Verbatim Depth',
		'habitat' => defined('HABITATLABEL')?HABITATLABEL:'Habitat',
		'substrate' => defined('SUBSTRATELABEL')?SUBSTRATELABEL:'Substrate',
		'occurrenceremarks' => defined('FAMILYLABEL')?FAMILYLABEL:'Notes (Occurrence Remarks)',
		'associatedtaxa' => defined('ASSOCIATEDTAXALABEL')?ASSOCIATEDTAXALABEL:'Associated Taxa',
		'verbatimattributes' => defined('VERBATIMATTRIBUTESLABEL')?VERBATIMATTRIBUTESLABEL:'Description',
		'lifestage' => defined('LIFESTAGELABEL')?LIFESTAGELABEL:'Life Stage',
		'sex' => defined('SEXLABEL')?SEXLABEL:'Sex',
		'individualcount' => defined('INDIVIDUALCOUNTLABEL')?INDIVIDUALCOUNTLABEL:'Individual Count',
		'samplingprotocol' => defined('SAMPLINGPROTOCOLLABEL')?SAMPLINGPROTOCOLLABEL:'Sampling Protocol',
		'preparations' => defined('PREPARATIONSLABEL')?PREPARATIONSLABEL:'Preparations',
		'reproductivecondition' => defined('REPRODUCTIVECONDITIONLABEL')?REPRODUCTIVECONDITIONLABEL:'Reproductive Condition',
		'typestatus' => defined('TYPESTATUSLABEL')?TYPESTATUSLABEL:'Type Status',
		'cultivationstatus' => defined('CULTIVATIONSTATUSLABEL')?CULTIVATIONSTATUSLABEL:'Cultivation Status',
		'establishmentmeans' => defined('ESTABLISHMENTMEANSLABEL')?ESTABLISHMENTMEANSLABEL:'Establishment Means',
		'datageneralizations' => defined('DATAGENERALIZATIONSLABEL')?DATAGENERALIZATIONSLABEL:'Data Generalizations',
		'disposition' => defined('DISPOSITIONLABEL')?DISPOSITIONLABEL:'Disposition',
		'duplicatequantity' => defined('DUPLICATEQUANTITYCOUNTLABEL')?DUPLICATEQUANTITYCOUNTLABEL:'Duplicate Qty',
		'datelastmodified' => 'Date Last Modified',
		'labelproject' => defined('LABELPROJECTLABEL')?LABELPROJECTLABEL:'Label Project',
		'processingstatus' => defined('PROCESSINGSTATUSLABEL')?PROCESSINGSTATUSLABEL:'Processing Status',
		'recordenteredby' => 'Entered By',
		'recordmodifiedby' => 'Last Modified By',
		'dbpk' => 'dbpk',
		'basisofrecord' => defined('BASISOFRECORDLABEL')?BASISOFRECORDLABEL:'Basis Of Record',
		'language' => defined('LANGUAGELABEL')?LANGUAGELABEL:'Language');

	if(array_key_exists('bufieldname',$_POST)){
		$occManager->setQueryVariables();
		$statusStr = $occManager->batchUpdateField($_POST['bufieldname'],$_POST['buoldvalue'],$_POST['bunewvalue'],$_POST['bumatch']);
	}

	if($occIndex !== false){
		//Query Form has been activated
		$occManager->setQueryVariables();
		$qryCnt = $occManager->getQueryRecordCount(1);
	}
	elseif(isset($_SESSION['editorquery'])){
		//Make sure query is null
		unset($_SESSION['editorquery']);
	}
	if(!is_numeric($occIndex)) $occIndex = 0;
	$recStart = floor($occIndex/$recLimit)*$recLimit;
	$recArr = $occManager->getOccurMap($recStart, $recLimit);
	$navStr = '<div class="navpath">';

	if($recStart >= $recLimit){
		$navStr .= '<a href="#" onclick="return submitQueryForm(0);" title="'.(isset($LANG['FIRST'])?$LANG['FIRST']:'First').' '.$recLimit.' '.(isset($LANG['RECORDS'])?$LANG['RECORDS']:'records').'">|&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;';
		$navStr .= '<a href="#" onclick="return submitQueryForm('.($recStart-$recLimit).');" title="'.(isset($LANG['PREVIOUS'])?$LANG['PREVIOUS']:'Previous').' '.$recLimit.' '.(isset($LANG['RECORDS'])?$LANG['RECORDS']:'records').'">&lt;&lt;</a>';
	}
	$navStr .= ' | ';
	$navStr .= ($recStart+1).'-'.($qryCnt<$recLimit+$recStart?$qryCnt:$recLimit+$recStart).' '.(isset($LANG['OF'])?$LANG['OF']:'of').' '.$qryCnt.' '.(isset($LANG['RECORDS'])?$LANG['RECORDS']:'records');
	$navStr .= ' | ';
	if($qryCnt > ($recLimit+$recStart)){
		$navStr .= '<a href="#" onclick="return submitQueryForm('.($recStart+$recLimit).');" title="'.(isset($LANG['NEXT'])?$LANG['NEXT']:'Next').' '.$recLimit.' '.(isset($LANG['RECORDS'])?$LANG['RECORDS']:'records').'">&gt;&gt;</a>&nbsp;&nbsp;&nbsp;&nbsp;';

		$navStr .= '<a href="#" onclick="return submitQueryForm('.(floor($qryCnt/$recLimit) * $recLimit).');" title="'.(isset($LANG['LAST'])?$LANG['LAST']:'Last').' '.$recLimit.' '.(isset($LANG['RECORDS'])?$LANG['RECORDS']:'records').'">&gt;|</a>';
	}
	$navStr .= ' * ' . (isset($LANG['CLICK_ID'])?$LANG['CLICK_ID']:'Click on the Symbiota identifier in the first column to open the editor.');
	$navStr .= '</div>';
}
else{
	header('Location: ../../profile/index.php?refurl=../collections/editor/occurrencetabledisplay.php?'.htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES));
}
?>
<!DOCTYPE html>
<html lang="<?php echo $LANG_TAG ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $CHARSET; ?>">
	<title><?php echo $DEFAULT_TITLE.' '.(isset($LANG['TABLE_VIEW'])?$LANG['TABLE_VIEW']:'Occurrence Table View'); ?></title>
	<link href="<?php echo $CSS_BASE_PATH; ?>/jquery-ui.css" type="text/css" rel="stylesheet">
	<?php
	include_once($SERVER_ROOT.'/includes/head.php');
	?>
	<link href="<?php echo htmlspecialchars($CLIENT_ROOT, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?>/js/datatables/datatables.min.css" type="text/css" rel="stylesheet">
	<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-3.7.1.min.js" type="text/javascript"></script>
	<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="../../js/datatables/datatables.min.js?ver=1" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(
			function () {
				$('#dynamictable').DataTable( {
					paging: false,
					searching: false,
					fixedColumns: {
						left: 1
					}
				} );
			}
		);
	</script>
	<script src="../../js/symb/collections.editor.table.js?ver=2" type="text/javascript" ></script>
	<script src="../../js/symb/collections.editor.query.js?ver=6" type="text/javascript" ></script>
	<style>
		#titleDiv{margin-bottom: 1rem}
		table.styledtable td { white-space: nowrap; }
		fieldset{ padding:15px }
		fieldset > legend{ font-weight:bold }
		.fieldGroupDiv { 
			display: flex;
			align-items: center;
			gap: 0.75rem;
			margin-bottom: 1rem;
			input {
				margin: 0
			}
			a {
				display: flex;
			}
		}
		.fieldDiv{ 
			display: inline;
		}
		#innertext{ background-color: white; margin: 0px 10px; }

		#record-viewer-innertext { 
			margin-left: 2em;
			width: calc(100vw - 4em);
			background-color: white; 
		}
		.editimg{ width: 15px; }
		.table-scroll {
			display: block;
			white-space: nowrap;
			overflow-x: scroll;
			overflow-y: scroll;
			max-height: 80vh;
			padding-bottom: 1.2rem;
		}

		/* JGM: Use OregonFlora styles
		.button-toggle {
			background-color: transparent; 
			color: var(--body-text-color); 
			border: 2px solid var(--darkest-color);

			&.active {
				background-color: var(--darkest-color); 
				color: white; 
			}

			&:hover {
				background-color: var(--medium-color);
				border: 2px solid var(--medium-color);
				color: var(--light-color);
			}
		}*/
	</style>
</head>
<body style="margin-left: 0px; margin-right: 0px;background-color:white;">
	<a class="screen-reader-only" href="#skip-search"><?php echo $LANG['SKIP_SEARCH'] ?></a>
	<div id="record-viewer-innertext">
		<h1 class="page-heading screen-reader-only">Occurrence Table Display</h1>
		<?php
		if(($isEditor || $crowdSourceMode)){
			?>

			<div style="width:850px;clear:both;">
				<div class='navpath'>
					<a href="../../index.php"><?php echo htmlspecialchars((isset($LANG['HOME'])?$LANG['HOME']:'Home'), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?></a> &gt;&gt;
					<?php
					if($crowdSourceMode){
						?>
						<a href="../specprocessor/crowdsource/index.php"><?php echo htmlspecialchars((isset($LANG['CENTRAL_CROWD'])?$LANG['CENTRAL_CROWD']:'Crowd Source Central'), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?></a> &gt;&gt;
						<?php
					}
					else{
						if(!$isGenObs || $IS_ADMIN){
							?>
							<a href="../misc/collprofiles.php?collid=<?php echo htmlspecialchars($collId, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?>&emode=1"><?php echo htmlspecialchars((isset($LANG['COL_MANAGEMENT'])?$LANG['COL_MANAGEMENT']:'Collection Management'), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?></a> &gt;&gt;
							<?php
						}
						if($isGenObs){
							?>
							<a href="../../profile/viewprofile.php?tabindex=1"><?php echo htmlspecialchars((isset($LANG['PERS_MANAGEMENT'])?$LANG['PERS_MANAGEMENT']:'Personal Management'), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?></a> &gt;&gt;
							<?php
						}
					}
					?>
					<b><?php echo (isset($LANG['TABLE_VIEW'])?$LANG['TABLE_VIEW']:'Occurrence Table View'); ?></b>
				</div>
			</div>
			<div id="titleDiv">
				<?php
				if($collMap) echo '<h1>' . $collMap['collectionname'].' ('.$collMap['institutioncode'].($collMap['collectioncode']?':'.$collMap['collectioncode']:'').')</h1>';
				?>
				<div style="display: flex; flex-wrap: wrap; gap: 0.5rem">
				   <button id="query-btn" type="button" class="button-toggle active" onclick="toggleSearch(); toggleButtonVisuals(this, 'querydiv', ['batch-update-btn'])">
					  <?= $LANG['SEARCH_FILTER'] ?>
				   </button>
				   <?php 
				   // JGM: Allowing editors to use the batch update tool, at least for now
				   //if($isEditor == 1 || $isGenObs): 
				   if($isEditor || $isGenObs):
				   ?>
				   <button id="batch-update-btn" type="button" class="button-toggle" onclick="toggleBatchUpdate(); toggleButtonVisuals(this, 'batchupdatediv', ['query-btn'])">
					  <?= $LANG['BATCH_TOOL'] ?>
				   </button>
				   <?php endif ?>
				</div>
			</div>
			<?php
			if(!$recArr) $displayQuery = 1;

			include 'includes/queryform.php';
			//Setup header map
			if($recArr){
				$headerArr = array();
				foreach($recArr as $id => $occArr){
					foreach($occArr as $k => $v){
						if(!is_array($v)){
							if($v && trim($v) && !array_key_exists($k,$headerArr)){
								$headerArr[$k] = $k;
							}
						}
					}
				}
				for($x=1; $x<9; $x++){
					if(isset($customArr[$x]['field'])){
						$customField = $customArr[$x]['field'];
						if($customField && !array_key_exists(strtolower($customField), $headerArr)){
							$headerArr[strtolower($customField)] = $customField;
						}
					}
				}
				$headerMap = array_intersect_key($headerMapBase, $headerArr);
			}
			// JGM: Allowing editors to use the batch update tool, at least for now
			//if($isEditor == 1 || $isGenObs){
			if($isEditor || $isGenObs){
				$buFieldName = (array_key_exists('bufieldname',$_REQUEST)?$_REQUEST['bufieldname']:'');
				?>
				<div id="batchupdatediv" style="width:600px;clear:both;display:<?php echo ($buFieldName?'block':'none'); ?>;">
					<form name="batchupdateform" action="occurrencetabledisplay.php" method="post" onsubmit="return false;">
						<fieldset>
							<legend><b><?php echo (isset($LANG['BATCH_UPDATE'])?$LANG['BATCH_UPDATE']:'Batch Update'); ?></b></legend>
							<div style="float:left;">
								<div style="margin:2px;">
									<?php echo (isset($LANG['FIELD_NAME'])?$LANG['FIELD_NAME']:'Field name'); ?>:
									<select name="bufieldname" id="bufieldname" onchange="detectBatchUpdateField();">
										<option value=""><?php echo (isset($LANG['SELECT_FIELD'])?$LANG['SELECT_FIELD']:'Select Field Name'); ?></option>
										<option value="">----------------------</option>
										<?php
										asort($headerMapBase);
										foreach($headerMapBase as $k => $v){
											//Scientific name fields are excluded because batch updates will not update tidinterpreted index and authors
											//Scientific name updates should happen within
											if($k != 'scientificnameauthorship' && $k != 'sciname'){
												echo '<option value="'.$k.'" '.($buFieldName==$k?'SELECTED':'').'>'.$v.'</option>';
											}
										}
										?>
									</select>
								</div>
								<div style="margin:2px;">
									<?php echo (isset($LANG['CURRENT_VALUE'])?$LANG['CURRENT_VALUE']:'Current Value'); ?>:
									<input name="buoldvalue" type="text" value="<?php echo (array_key_exists('buoldvalue',$_REQUEST)?$_REQUEST['buoldvalue']:''); ?>" />
								</div>
								<div style="margin:2px;">
									<?php echo (isset($LANG['NEW_VALUE'])?$LANG['NEW_VALUE']:'New Value'); ?>:
									<span id="bunewvaluediv">
										<?php
										if($buFieldName=='processingstatus'){
											?>
											<select name="bunewvalue">
												<?php
												foreach($processingStatusArr as $v){

													$keyOut = strtolower($v);

													echo '<option value="'.$keyOut.'"'.(array_key_exists('bunewvalue',$_REQUEST) && $_REQUEST['bunewvalue'] == $keyOut ? ' SELECTED' : '').'>'.isset($LANG[strtoupper($v)])?$LANG[strtoupper($v)]:ucwords($v).'</option>';
												}

												?>
												<option value="" <?php echo (array_key_exists('bunewvalue',$_REQUEST)&&$_REQUEST['bunewvalue']=='no set status'?'SELECTED':''); ?>><?php echo (isset($LANG['NO_STATUS'])?$LANG['NO_STATUS']:'No Set Status'); ?></option>
											</select>
											<?php
										}
										else{
											?>
											<input name="bunewvalue" type="text" value="<?php echo (array_key_exists('bunewvalue',$_POST)?$_POST['bunewvalue']:''); ?>" />
											<?php
										}
										?>
									</span>
								</div>
							</div>
							<div style="float:left;margin-left:30px;">
								<div style="margin:2px;">
									<input name="bumatch" type="radio" value="0" checked />
									<?php echo (isset($LANG['MATCH_WHOLE'])?$LANG['MATCH_WHOLE']:'Match Whole Field'); ?><br/>
									<input name="bumatch" type="radio" value="1" />
									<?php echo (isset($LANG['MATCH_PART'])?$LANG['MATCH_PART']:'Match Any Part of Field'); ?>
								</div>
								<div style="margin:2px;">
									<select id= "processingStatus" name="processingStatus" style="display: none;">
										<?php
										// Make a hidden processing status select box, that javascript detectBatchUpdateField()
										// in js/symb/collections.occureditorshare.js can pull custom processing statuses from
										foreach($processingStatusArr as $v){

											$keyOut = strtolower($v);

											echo '<option value="'.$keyOut.'"'.(array_key_exists('bunewvalue',$_REQUEST) && $_REQUEST['bunewvalue'] == $keyOut ? ' SELECTED' : '').'>'.ucwords($v).'</option>';
										}
										?>
									</select>
									<input name="collid" type="hidden" value="<?php echo $collId; ?>" />
									<input name="occid" type="hidden" value="0" />
									<input name="occindex" type="hidden" value="0" />
									<button name="submitaction" type="submit" value="Batch Update Field" onclick="submitBatchUpdate(this.form); return false;"><?php echo (isset($LANG['BATCH_UP_FIELD'])?$LANG['BATCH_UP_FIELD']:'Batch Update Field'); ?></button>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
				<?php
			}
			?>
			<div style="display:flex;width:850px;clear:both;">
				<?php echo $navStr; ?>
				
			</div>
			<?php
			if($recArr){
				?>
				<div style="clear: both; padding-top:10px" id="skip-search">
					<?php
					$tableId = 'defaulttable';
					$tableClass = 'styledtable';
					if($dynamicTable){
						$tableId = 'dynamictable';
						$tableClass = 'stripe hover order-column compact nowrap cell-border';
					}
					?>
					<table id="<?php echo $tableId; ?>" class="<?php echo $tableClass; ?> table-scroll" title="<?php echo htmlspecialchars((isset($LANG['TABLE_VIEW']) ? $LANG['TABLE_VIEW'] : 'Occurrence Table View'), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?>" aria-describedby="table-desc">
						<thead>
							<tr>
								<th><?php echo (isset($LANG['SYMB_ID'])?$LANG['SYMB_ID']:'Symbiota ID'); ?></th>
								<?php
								foreach($headerMap as $k => $v){
									echo '<th>'.$v.'</th>';
								}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
							$recCnt = 0;
							foreach($recArr as $id => $occArr){
								if($occArr['sciname']){
									$occArr['sciname'] = '<i>'.$occArr['sciname'].'</i> ';
								}
								echo "<tr ".($recCnt%2?'class="alt"':'').">\n";
								echo '<td>';
								$url = 'occurrenceeditor.php?csmode='.$crowdSourceMode.'&occindex='.($recCnt+$recStart).'&occid='.$id.'&collid='.$collId;
								echo '<a href="' . htmlspecialchars($url, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE) . '" title="' . htmlspecialchars((isset($LANG['SAME_WINDOW'])?$LANG['SAME_WINDOW']:'open in same window'), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE) . '" aria-label="' .  htmlspecialchars($id, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE) . '">' . htmlspecialchars($id, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE) . '</a> ';
								echo '<a href="' . htmlspecialchars($url, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE) . '" target="_blank" title="' . (isset($LANG['NEW_WINDOW'])?$LANG['NEW_WINDOW']:'open in new window') . '" aria-label="' . (isset($LANG['NEW_WINDOW'])?$LANG['NEW_WINDOW']:'open in new window') . '">';
								echo '<img src="../../images/newwin.png" style="width:1.1em;" alt="' . $LANG['IMG_LINK'] . '" />';
								echo '</a>';
								echo '</td>'."\n";
								foreach($headerMap as $k => $v){
									$displayStr = $occArr[$k];
									if($displayStr){
										if(strlen($displayStr) > 60){
											$displayStr = substr($displayStr,0,60).'...';
										}
									}
									else $displayStr = '&nbsp;';
									echo '<td>'.$displayStr.'</td>'."\n";
								}
								echo "</tr>\n";
								$recCnt++;
							}
							?>
						</tbody>
					</table>
					<p id="table-desc">
							<?php echo htmlspecialchars((isset($LANG['TABLE_VIEW_DESC']) ? $LANG['TABLE_VIEW_DESC'] : 'Table displays occurrence information with columns showing Symbiota ID, Family, Event Date, Author, Location, and other details'), ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?>
					</p>
				</div>
				<?php
			}
			else{
				?>
				<div style="clear:both;padding:20px;font-weight:bold;font-size:120%;">
					<?php echo (isset($LANG['NONE_FOUND'])?$LANG['NONE_FOUND']:'No records found matching the query'); ?>
				</div>
				<?php
			}
		}
		else{
			if(!$isEditor){
				echo '<h2>'.(isset($LANG['NOT_AUTH'])?$LANG['NOT_AUTH']:'You are not authorized to access this page').'</h2>';
			}
		}
		?>
	</div>
</body>
</html>
