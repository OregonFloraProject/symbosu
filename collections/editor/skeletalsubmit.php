<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceSkeletal.php');
if($LANG_TAG != 'en' && file_exists($SERVER_ROOT.'/content/lang/collections/editor/skeletalsubmit.'.$LANG_TAG.'.php')) include_once($SERVER_ROOT.'/content/lang/collections/editor/skeletalsubmit.'.$LANG_TAG.'.php');
else include_once($SERVER_ROOT.'/content/lang/collections/editor/skeletalsubmit.en.php');
header("Content-Type: text/html; charset=".$CHARSET);
if(!$SYMB_UID) header('Location: ../../profile/index.php?refurl=../collections/editor/skeletalsubmit.php?'.htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES));

$collid  = $_REQUEST["collid"];
$action = array_key_exists("formaction",$_REQUEST)?$_REQUEST["formaction"]:"";

$skeletalManager = new OccurrenceSkeletal();
if($collid){
	$skeletalManager->setCollid($collid);
	$collMap = $skeletalManager->getCollectionMap();
}

$statusStr = '';
$isEditor = 0;
if($collid){
	if($IS_ADMIN){
		$isEditor = 1;
	}
	elseif(array_key_exists("CollAdmin",$USER_RIGHTS) && in_array($collid,$USER_RIGHTS['CollAdmin'])){
		$isEditor = 1;
	}
	elseif(array_key_exists("CollEditor",$USER_RIGHTS) && in_array($collid,$USER_RIGHTS['CollEditor'])){
		$isEditor = 1;
	}
}

// Add collection customization variables
if($collid && file_exists('includes/config/occurVarColl'.$collid.'.php')){
	//Specific to particular collection
	include('includes/config/occurVarColl'.$collid.'.php');
}
elseif(file_exists('includes/config/occurVarDefault.php')){
	//Specific to Default values for portal
	include('includes/config/occurVarDefault.php');
}

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $CHARSET; ?>">
	<title><?php echo $DEFAULT_TITLE.' '.$LANG['OCC_SKEL_SUBMIT']; ?></title>
	<link href="<?php echo $CSS_BASE_PATH; ?>/jquery-ui.css" type="text/css" rel="stylesheet">
	<?php
	include_once($SERVER_ROOT.'/includes/head.php');
	?>
	
</head>
<body>
	<?php
	$displayLeftMenu = false;
	include($SERVER_ROOT.'/includes/header.php');
	?>
	<script src="../../js/jquery.js" type="text/javascript"></script>
	<script src="../../js/jquery-ui.js" type="text/javascript"></script>
	<script src="../../js/symb/collections.editor.skeletal.js?ver=1" type="text/javascript"></script>
	<script src="../../js/symb/shared.js?ver=150324" type="text/javascript"></script>
	<style type="text/css">
		label{ font-weight: bold; }
		fieldset{ padding: 15px; }
		legend{ font-weight: bold; }
	</style>
	<div class='navpath'>
		<a href="../../index.php"><?php echo $LANG['HOME']; ?></a> &gt;&gt;
		<a href="../misc/collprofiles.php?collid=<?php echo $collid; ?>&emode=1"><?php echo $LANG['COL_MNGMT']; ?></a> &gt;&gt;
		<b><?php echo $LANG['OCC_SKEL_SUBMIT']; ?></b>
	</div>
	<!-- inner text -->
	<div id="innertext">
		<div style="float:right;"><a href="#" onclick="toggle('descriptiondiv')"><b><?php echo $LANG['DISPLAY_INSTRUCTIONS']; ?></b></a></div>
		<h1><?php echo $collMap['collectionname']; ?></h1>
		<?php
		if($statusStr){
			echo '<div style="margin:15px;color:red;">'.$statusStr.'</div>';
		}
		if($isEditor){
			?>
			<fieldset>
				<legend>
					<b><?php echo $LANG['SKELETAL_DATA']; ?></b>
					<a id="optionimgspan" href="#" onclick="showOptions()"><img src="../../images/list.png" style="width:12px;" title="<?php echo $LANG['DISPLAY_OPTIONS']; ?>" /></a>
					<a href="#" onclick="toggle('descriptiondiv')"><img src="../../images/info.png" style="width:12px;" title="<?php echo $LANG['TOOL_DESCRIPTION']; ?>" /></a>
				</legend>
				<div id="descriptiondiv" style="display:none;margin:10px;width:80%">
					<div style="margin-bottom:5px">
						<?php echo $LANG['SKELETAL_DESCIPRTION_1']; //This page is typically used to enter skeletal records into the system during the imaging process...?>
					</div>
					<div style="margin-bottom:5px">
						<?php echo $LANG['SKELETAL_DESCIPRTION_2']; //More complete data can be entered by clicking on the catalog number...?>
					</div>
					<div>
						<?php echo $LANG['SKELETAL_DESCIPRTION_3']; //Click the Display Option symbol located above scientific name to adjust field display...?>
					</div>
 				</div>
				<form id="defaultform" name="defaultform" action="skeletalsubmit.php" method="post" autocomplete="off" onsubmit="return submitDefaultForm(this)">
					<div id="optiondiv" style="display:none;background-color:white;">
						<fieldset style="margin-top: -10px;padding-top:5px">
							<legend><b><?php echo $LANG['OPTIONS']; ?></b></legend>
							<div style="float:right;"><a href="#" onclick="hideOptions()" style="color:red" ><?php echo $LANG['X_CLOSE']; ?></a></div>
							<div style="text-decoration: underline"><?php echo $LANG['FIELD_DISPLAY']; ?>:</div>
							<input type="checkbox" onclick="toggleFieldDiv('othercatalognumbersdiv')" /> <?php echo (defined('OTHERCATALOGNUMBERSLABEL')?OTHERCATALOGNUMBERSLABEL:(isset($LANG['OTHER_CAT_NUMS'])?$LANG['OTHER_CAT_NUMS']:'Other Catalog Numbers')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('authordiv')" CHECKED /> <?php echo (defined('SCIENTIFICNAMEAUTHORSHIPLABEL')?SCIENTIFICNAMEAUTHORSHIPLABEL:(isset($LANG['AUTHOR'])?$LANG['AUTHOR']:'Author')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('familydiv')" CHECKED /> <?php echo (defined('FAMILYLABEL')?FAMILYLABEL:(isset($LANG['FAMILY'])?$LANG['FAMILY']:'Family')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('localitysecuritydiv')" CHECKED /> <?php echo $LANG['LOCALITY_SECURITY']; ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('countrydiv')" /> <?php echo (defined('COUNTRYLABEL')?COUNTRYLABEL:(isset($LANG['COUNTRY'])?$LANG['COUNTRY']:'Country')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('statediv')" CHECKED /> <?php echo (defined('STATEPROVINCELABEL')?STATEPROVINCELABEL:(isset($LANG['STATE_PROVINCE'])?$LANG['STATE_PROVINCE']:'State/Province')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('countydiv')" CHECKED /> <?php echo (defined('COUNTYLABEL')?COUNTYLABEL:(isset($LANG['COUNTY_PARISH'])?$LANG['COUNTY_PARISH']:'County')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('recordedbydiv')" /> <?php echo (defined('RECORDEDBYLABEL')?RECORDEDBYLABEL:(isset($LANG['COLLECTOR'])?$LANG['COLLECTOR']:'Collector')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('recordnumberdiv')" /> <?php echo (defined('RECORDNUMBERLABEL')?RECORDNUMBERLABEL:(isset($LANG['COLLECTOR_NO'])?$LANG['COLLECTOR_NO']:'Collector Number')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('eventdatediv')" /> <?php echo (defined('EVENTDATELABEL')?EVENTDATELABEL:(isset($LANG['COLLECTION_DATE'])?$LANG['COLLECTION_DATE']:'Collection Date')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('labelprojectdiv')" /> <?php echo (defined('LABELPROJECTLABEL')?LABELPROJECTLABEL:(isset($LANG['LABEL_PROJECT'])?$LANG['LABEL_PROJECT']:'Label Project')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('processingstatusdiv')" /> <?php echo (defined('PROCESSINGSTATUSLABEL')?PROCESSINGSTATUSLABEL:(isset($LANG['PROCESSING_STATUS'])?$LANG['PROCESSING_STATUS']:'Processing Status')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('languagediv')" /> <?php echo (defined('LANGUAGELABEL')?LANGUAGELABEL:(isset($LANG['LANGUAGE'])?$LANG['LANGUAGE']:'Language')); ?><br/>
							<input type="checkbox" onclick="toggleFieldDiv('exsiccatadiv')" /> <?php echo (defined('EXSICCATITITLELABEL')?EXSICCATITITLELABEL:(isset($LANG['EXSICCATA'])?$LANG['EXSICCATA']:'Exsiccata Title')); ?><br/>
							<div style="font-weight:bold"><?php echo $LANG['CATNUM_MATCH']; ?>:</div>
							<input name="addaction" type="radio" value="1" checked /> <?php echo $LANG['RESTRICT_IF_EXISTS']; ?> <br/>
							<input name="addaction" type="radio" value="2" /> <?php echo $LANG['APPEND_VALUES']; ?>
						</fieldset>
					</div>
					<div style="position:absolute;background-color:white;top:10px;right:10px;">
						<?php echo $LANG['SESSION']; ?>: <label id="minutes">00</label>:<label id="seconds">00</label><br/>
						<?php echo $LANG['COUNT']; ?>: <label id="count">0</label><br/>
						<?php echo $LANG['RATE']; ?>: <label id="rate">0</label> <?php echo $LANG['PER_HOUR']; ?>
					</div>
					<div>
						<div style="">
							<div id="scinamediv" style="float:left" title="<?php echo (defined('SCIENTIFICNAMETIP') ? SCIENTIFICNAMETIP : ''); ?>">
								<label><?php echo (defined('SCIENTIFICNAMELABEL')?SCIENTIFICNAMELABEL:(isset($LANG['SCINAME'])?$LANG['SCINAME']:'Scientific Name')); ?>:</label>
								<input id="fsciname" name="sciname" type="text" value="" style="width:300px"/>
								<input id="ftidinterpreted" name="tidinterpreted" type="hidden" value="" />&nbsp;
							</div>
							<div id="authordiv" style="float:left" title="<?php echo (defined('SCIENTIFICNAMEAUTHORSHIPTIP') ? SCIENTIFICNAMEAUTHORSHIPTIP : ''); ?>">
								<label><?php echo (defined('SCIENTIFICNAMEAUTHORSHIPLABEL')?SCIENTIFICNAMEAUTHORSHIPLABEL:(isset($LANG['AUTHOR'])?$LANG['AUTHOR']:'Author')); ?>:</label>
								<input id="fscientificnameauthorship" name="scientificnameauthorship" type="text" value="" />
							</div>
							<?php
							if($IS_ADMIN || isset($USER_RIGHTS['Taxonomy'])){
								?>
								<div style="float:left;padding:2px 3px;">
									<a href="../../taxa/taxonomy/taxonomyloader.php" target="_blank">
										<img src="../../images/add.png" style="width:14px;" title="<?php echo $LANG['ADD_NAME_THESAURUS']; ?>" />
									</a>
								</div>
								<?php
							}
							?>
							<div style="clear:both;">
								<div id="familydiv" style="float:left" title="<?php echo (defined('FAMILYTIP') ? FAMILYTIP : ''); ?>">
									<label><?php echo (defined('FAMILYLABEL')?FAMILYLABEL:(isset($LANG['FAMILY'])?$LANG['FAMILY']:'Family')); ?>:</label> <input id="ffamily" name="family" type="text" tabindex="0" value="" />
								</div>
								<div id="localitysecuritydiv" style="float:left;margin:3px 5px">
									<input id="flocalitysecurity" name="localitysecurity" type="checkbox" tabindex="0" value="1" />
									<?php echo $LANG['PROTECT_LOCALITY']; ?>
								</div>
							</div>
						</div>
						<div style="clear:both;padding-top:5px">
							<div id="countrydiv" style="display:none;float:left;margin:3px 3px 3px 0px;" title="<?php echo (defined('COUNTRYTIP') ? COUNTRYTIP : ''); ?>">
								<label><?php echo (defined('COUNTRYLABEL')?COUNTRYLABEL:(isset($LANG['COUNTRY'])?$LANG['COUNTRY']:'Country')); ?>:</label><br/>
								<input id="fcountry" name="country" type="text" value="" autocomplete="off" />
							</div>
							<div id="statediv" style="float:left;margin:3px 3px 3px 0px;" title="<?php echo (defined('STATEPROVINCETIP') ? STATEPROVINCETIP : ''); ?>">
								<label><?php echo (defined('STATEPROVINCELABEL')?STATEPROVINCELABEL:(isset($LANG['STATE_PROVINCE'])?$LANG['STATE_PROVINCE']:'State/Province')); ?>:</label><br/>
								<input id="fstateprovince" name="stateprovince" type="text" value="" autocomplete="off" onchange="localitySecurityCheck(this.form)" />
							</div>
							<div id="countydiv" style="float:left;margin:3px 3px 3px 0px;" title="<?php echo (defined('COUNTYTIP') ? COUNTYTIP : ''); ?>">
								<label><?php echo (defined('COUNTYLABEL')?COUNTYLABEL:(isset($LANG['COUNTY_PARISH'])?$LANG['COUNTY_PARISH']:'County')); ?>:</label><br/>
								<input id="fcounty" name="county" type="text" autocomplete="off" value="" />
							</div>
						</div>
						<div style="clear:both;padding-top:5px">
							<div id="recordedbydiv" style="display:none;float:left;margin:3px;" title="<?php echo (defined('RECORDEDBYTIP') ? RECORDEDBYTIP : ''); ?>">
								<label><?php echo (defined('RECORDEDBYLABEL')?RECORDEDBYLABEL:(isset($LANG['COLLECTOR'])?$LANG['COLLECTOR']:'Collector')); ?>:</label><br/>
								<input id="frecordedby" name="recordedby" type="text" value="" />
							</div>
							<div id="recordnumberdiv" style="display:none;float:left;margin:3px;" title="<?php echo (defined('RECORDNUMBERTIP') ? RECORDNUMBERTIP : ''); ?>">
								<label><?php echo (defined('RECORDNUMBERLABEL')?RECORDNUMBERLABEL:(isset($LANG['COLLECTOR_NO'])?$LANG['COLLECTOR_NO']:'Collector Number')); ?>:</label><br/>
								<input id="frecordnumber" name="recordnumber" type="text" value="" />
							</div>
							<div id="eventdatediv" style="display:none;float:left;margin:3px;" title="<?php echo (defined('EVENTDATETIP') ? EVENTDATETIP : 'Earliest Date Collected'); ?>">
								<label><?php echo (defined('EVENTDATELABEL')?EVENTDATELABEL:(isset($LANG['DATE'])?$LANG['DATE']:'Date')); ?>:</label><br/>
								<input id="feventdate" name="eventdate" type="text" value="" onchange="eventDateChanged(this)" />
							</div>
							<div id="labelprojectdiv" style="display:none;float:left;margin:3px;" title="<?php echo (defined('LABELPROJECTTIP') ? LABELPROJECTTIP : ''); ?>">
								<label><?php echo (defined('LABELPROJECTLABEL')?LABELPROJECTLABEL:(isset($LANG['LABEL_PROJECT'])?$LANG['LABEL_PROJECT']:'Label Project')); ?>:</label><br/>
								<input id="flabelproject" name="labelproject" type="text" value="" />
							</div>
							<div id="processingstatusdiv" style="display:none;float:left;margin:3px" title="<?php echo (defined('PROCESSINGSTATUSTIP') ? PROCESSINGSTATUSTIP : ''); ?>">
								<label><?php echo (defined('PROCESSINGSTATUSLABEL')?PROCESSINGSTATUSLABEL:(isset($LANG['PROCESSING_STATUS'])?$LANG['PROCESSING_STATUS']:'Processing Status')); ?>:</label><br/>
								<select id="fprocessingstatus" name="processingstatus" style="margin-top:4px;width:150px">
									<option value="">No Set Status</option>
									<option>-------------------</option>
									<?php

									// Set the list of processing statuses, from the collection editor template
									$processingStatusArr = array();
									if(defined('PROCESSINGSTATUS') && PROCESSINGSTATUS){
										$processingStatusArr = PROCESSINGSTATUS;
									}
									else{
										$processingStatusArr = array('unprocessed','unprocessed/NLP','stage 1','stage 2','stage 3','pending duplicate','pending review-nfn','pending review','expert required','reviewed','closed');
									}

									foreach($processingStatusArr as $v){

										$keyOut = strtolower($v);

										echo '<option value="'.$keyOut.'">'.ucwords($v).'</option>';
									}
									?>
								</select>
							</div>
							<div id="languagediv" style="display:none;float:left;margin:3px;" title="<?php echo (defined('LANGUAGETIP') ? LANGUAGETIP : ''); ?>">
								<label><?php echo (defined('LANGUAGELABEL')?LANGUAGELABEL:(isset($LANG['LANGUAGE'])?$LANG['LANGUAGE']:'Language')); ?>:</label><br/>
								<select id="flanguage" name="language" style="margin-top:4px">
									<option value=""></option>
									<?php
									$langArr = $skeletalManager->getLanguageArr();
									foreach($langArr as $code => $langStr){
										echo '<option value="'.$code.'">'.$langStr.'</option>';
									}
									?>
								</select>
							</div>
							<div id="exsiccatadiv" style="display:none;clear:both;">
								<div id="ometidDiv" style="float:left" title="<?php echo (defined('EXSICCATITITLETIP') ? EXSICCATITITLETIP : ''); ?>">
									<label><?php echo (defined('EXSICCATITITLELABEL')?EXSICCATITITLELABEL:(isset($LANG['EXSTITLE'])?$LANG['EXSTITLE']:'Exsiccati Title')); ?></label><br/>
									<input id="fexstitle" name="exstitle" value="" style="width: 600px" />
									<input id="fometid" name="ometid" type="hidden" value="" />
								</div>
								<div id="exsnumberDiv" title="<?php echo (defined('EXSICCATINUMBERTIP') ? EXSICCATINUMBERTIP : ''); ?>">
									<label><?php echo (defined('EXSICCATINUMBERLABEL')?EXSICCATINUMBERLABEL:(isset($LANG['EXSNUMBER'])?$LANG['EXSNUMBER']:'Number')); ?></label><br/>
									<input id="fexsnumber" name="exsnumber" type="text" value="" />
								</div>
							</div>
						</div>
						<div style="clear:both;padding:15px;">
							<div style="float:right;margin:16px 30px 0px 0px;">
								<input name="clearform" type="reset" onclick="resetForm()" value="Clear Form" style="margin-right:40px" />
							</div>
							<div style="float:left;" title="<?php echo (defined('CATALOGNUMBERTIP') ? CATALOGNUMBERTIP : ''); ?>">
								<label><?php echo (defined('CATALOGNUMBERLABEL')?CATALOGNUMBERLABEL:(isset($LANG['CAT_NUM'])?$LANG['CAT_NUM']:'Catalog Number')); ?>:</label><br/>
								<input id="fcatalognumber" name="catalognumber" type="text" style="border-color:green;" />
							</div>
							<div id="othercatalognumbersdiv" style="display:none;float:left;margin:3px;" title="<?php echo (defined('OTHERCATALOGNUMBERSTIP') ? OTHERCATALOGNUMBERSTIP : ''); ?>">
								<label><?php echo (defined('OTHERCATALOGNUMBERSLABEL')?OTHERCATALOGNUMBERSLABEL:(isset($LANG['OTHER_CAT_NUMS'])?$LANG['OTHER_CAT_NUMS']:'Other Catalog Numbers')); ?>:</label><br/>
								<input id="fothercatalognumbers" name="othercatalognumbers" type="text" value="" />
							</div>
							<div style="float:left;margin:16px 3px 3px 3px;">
								<input id="fcollid" name="collid" type="hidden" value="<?php echo $collid; ?>" />
								<button name="recordsubmit" type="submit" value="Add Record"><?php echo $LANG['ADD_RECORD']; ?></button>
							</div>
						</div>
					</div>
				</form>
			</fieldset>
			<fieldset style="padding:15px;">
				<legend><b><?php echo $LANG['RECORDS']; ?></b></legend>
				<div id="occurlistdiv"></div>
			</fieldset>
			<?php
		}
		else{
			if($collid){
				echo $LANG['NOT_AUTHORIZED'].'<br/>';
				echo $LANG['CONTACT_ADMIN'].'</b> ';
			}
			else{
				echo $LANG['ERROR_NO_ID'];
			}
		}
		?>
	</div>
<?php
	include($SERVER_ROOT.'/includes/footer.php');
?>
</body>
</html>
