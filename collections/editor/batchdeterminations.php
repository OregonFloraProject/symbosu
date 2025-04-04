<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceEditorDeterminations.php');
if($LANG_TAG != 'en' && file_exists($SERVER_ROOT.'/content/lang/collections/editor/batchdeterminations.'.$LANG_TAG.'.php')) include_once($SERVER_ROOT.'/content/lang/collections/editor/batchdeterminations.'.$LANG_TAG.'.php');
else include_once($SERVER_ROOT.'/content/lang/collections/editor/batchdeterminations.en.php');
header('Content-Type: text/html; charset=' . $CHARSET);

if(!$SYMB_UID) header('Location: ../../profile/index.php?refurl=../collections/editor/batchdeterminations.php?'.htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES));

$collid = filter_var(($_REQUEST['collid'] ?? 0), FILTER_SANITIZE_NUMBER_INT);
$formSubmit = array_key_exists('formsubmit',$_POST)?$_POST['formsubmit']:'';

$occManager = new OccurrenceEditorDeterminations();
$occManager->setCollId($collid);
$occManager->getCollMap();

$isEditor = 0;
if($IS_ADMIN || (array_key_exists('CollAdmin', $USER_RIGHTS) && in_array($collid, $USER_RIGHTS['CollAdmin']))){
	$isEditor = 1;
}
elseif(array_key_exists('CollEditor', $USER_RIGHTS) && in_array($collid, $USER_RIGHTS['CollEditor'])){
	$isEditor = 1;
}
$statusStr = '';
if($isEditor){
	if($formSubmit == 'Add New Determinations'){
		$occidArr = $_REQUEST['occid'];
		foreach($occidArr as $k){
			$occManager->setOccId(filter_var($k, FILTER_SANITIZE_NUMBER_INT));
			$occManager->addDetermination($_REQUEST,$isEditor);
		}
		$statusStr = 'SUCCESS: ' . count($occidArr) . ' annotations submitted';
	}
	elseif($formSubmit == 'Adjust Nomenclature'){
		$occidArr = $_REQUEST['occid'];
		foreach($occidArr as $k){
			$occManager->setOccId(filter_var($k, FILTER_SANITIZE_NUMBER_INT));
			$occManager->addNomAdjustment($_REQUEST,$isEditor);
		}
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
<!DOCTYPE html>
<html lang="<?php echo $LANG_TAG ?>">
	<head>
	    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $CHARSET;?>">
		<title><?php echo $DEFAULT_TITLE.' '.$LANG['BATCH_DETERS']; ?></title>
		<link href="<?php echo $CSS_BASE_PATH; ?>/jquery-ui.css" type="text/css" rel="stylesheet">
		<?php
		include_once($SERVER_ROOT.'/includes/head.php');
		?>
		<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-3.7.1.min.js" type="text/javascript"></script>
		<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-ui.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			function initScinameAutocomplete(f){
				$( f.sciname ).autocomplete({
					source: "rpc/getspeciessuggest.php",
					minLength: 3,
					change: function(event, ui) {
					}
				});
			}

			function initDetAutocomplete(f){
				$( f.sciname ).autocomplete({
					source: "rpc/getspeciessuggest.php",
					minLength: 3,
					change: function(event, ui) {
						if(f.sciname.value){
							pauseSubmit = true;
							verifyDetSciName(f);
						}
						else{
							f.scientificnameauthorship.value = "";
							f.family.value = "";
							f.tidtoadd.value = "";
						}
					}
				});
			}

			function submitAccForm(f){
				var workingObj = document.getElementById("workingcircle");
				workingObj.style.display = "inline"
				var allCatNum = 0;
				if(f.allcatnum.checked) allCatNum = 1;

				$.ajax({
					type: "POST",
					url: "rpc/getnewdetitem.php",
					dataType: "json",
					data: {
						catalognumber: f.catalognumber.value,
						allcatnum: allCatNum,
						sciname: f.sciname.value,
						collid: f.collid.value
					}
				}).done(function( retStr ) {
					if(retStr != ""){
						for (var occid in retStr) {
							var occObj = retStr[occid];
							if(f.catalognumber.value && checkCatalogNumber(occid, occObj["cn"])){
								alert("<?php echo $LANG['RECORD_EXISTS']; ?>");
							}
							else{
								var trNode = createNewTableRow(occid, occObj);
								var tableBody = document.getElementById("catrecordstbody");
								tableBody.insertBefore(trNode, tableBody.firstElementChild);
							}
						}
						document.getElementById("accrecordlistdviv").style.display = "block";
					}
					else{
						alert("<?php echo $LANG['NO_RECORDS']; ?>");
					}
				});

				if(f.catalognumber.value != ""){
					f.catalognumber.value = '';
					f.catalognumber.focus();
				}
				workingObj.style.display = "none";
				return false;
			}

			function checkCatalogNumber(catNum){
				var dbElements = document.getElementsByName("occid[]");
				for(i = 0; i < dbElements.length; i++){
					if(dbElements[i].value == catNum) return true;
				}
				return false;
			}

			function createNewTableRow(occid, occObj){
				var trNode = document.createElement("tr");
				var inputNode = document.createElement("input");
				inputNode.setAttribute("type", "checkbox");
				inputNode.setAttribute("name", "occid[]");
				inputNode.setAttribute("value", occid);
				inputNode.setAttribute("checked", "checked");
				var tdNode1 = document.createElement("td");
				tdNode1.appendChild(inputNode);
				trNode.appendChild(tdNode1);
				var tdNode2 = document.createElement("td");
				var anchor1 = document.createElement("a");
				anchor1.setAttribute("href","#");
				anchor1.setAttribute("onclick","openIndPopup("+occid+"); return false;");
				if(occObj["cn"]) anchor1.innerHTML = occObj["cn"];
				else anchor1.innerHTML = "[no catalog number]";
				tdNode2.appendChild(anchor1);
				var anchor2 = document.createElement("a");
				anchor2.setAttribute("href","#");

				tdNode2.appendChild(anchor2);
				trNode.appendChild(tdNode2);
				var tdNode3 = document.createElement("td");
				tdNode3.appendChild(document.createTextNode(occObj["sn"]));
				trNode.appendChild(tdNode3);
				var tdNode4 = document.createElement("td");
				tdNode4.appendChild(document.createTextNode(occObj["coll"]+'; '+occObj["loc"]));
				trNode.appendChild(tdNode4);
				return trNode;
			}

			function clearAccForm(f){
				if(confirm("<?php echo $LANG['CLEAR_FORM_RESETS']; ?>") == true){
					document.getElementById("accrecordlistdviv").style.display = "none";
					document.getElementById("catrecordstbody").innerHTML = '';
					f.catalognumber.value = '';
					f.sciname.value = '';
				}
			}

			function validateSelectForm(f){
				var specNotSelected = true;
				var dbElements = document.getElementsByName("occid[]");
				for(i = 0; i < dbElements.length; i++){
					var dbElement = dbElements[i];
					if(dbElement.checked){
						specNotSelected = false;
						break;
					}
				}
				if(specNotSelected){
					alert("<?php echo $LANG['SELECT_ONE']; ?>");
					return false;
				}

				if(f.sciname.value == ""){
					alert("<?php echo $LANG['SCINAME_NEEDS_VALUE']; ?>");
					return false;
				}
				if(f.identifiedby.value == ""){
					alert("<?php echo $LANG['DETERMINER_NEEDS_VALUE']; ?>");
					return false;
				}
				if(f.dateidentified.value == ""){
					alert("<?php echo $LANG['DET_DATE_NEEDS_VALUE']; ?>");
					return false;
				}
				return true;
			}

			function selectAll(cb){
				boxesChecked = true;
				if(!cb.checked){
					boxesChecked = false;
				}
				var dbElements = document.getElementsByName("occid[]");
				for(i = 0; i < dbElements.length; i++){
					var dbElement = dbElements[i];
					dbElement.checked = boxesChecked;
				}
			}

			function annotationTypeChanged(selectElem){
				var f = selectElem.form;
				if(selectElem.value == "na"){
					f.identificationqualifier.value = "";
					$("#idQualifierDiv").hide();
					f.confidenceranking.value = "";
					$("#codDiv").hide();
					// OSU convention: add this to the Notes rather than the determiner
					f.identificationremarks.value = "Nomenclatural Adjustment";
					f.identifiedby.readonly = true;
					f.makecurrent.checked = true;

					var today = new Date();
					var month = (today.getMonth() + 1);
					var day = today.getDate();
					var year = today.getFullYear();
					if(month < 10) month = '0' + month;
					if(day < 10) day = '0' + day;
					f.dateidentified.value = [year, month, day].join('-');
				}
				else{
					$("#idQualifierDiv").show();
					f.confidenceranking.value = 5;
					$("#codDiv").show();
					f.identifiedby.value = "";
					f.identifiedby.readonly = true;
					f.dateidentified.value = "";
					f.makecurrent.checked = false;
				}
			}

			function verifyDetSciName(f){
				$.ajax({
					type: "POST",
					url: "rpc/verifysciname.php",
					dataType: "json",
					data: { term: f.sciname.value }
				}).done(function( data ) {
					if(data){
						f.scientificnameauthorship.value = data.author;
						f.family.value = data.family;
						f.tidtoadd.value = data.tid;
					}
					else{
						alert("<?php echo $LANG['WARNING_TAXON_NOT_FOUND']; ?>");
						f.scientificnameauthorship.value = "";
						f.family.value = "";
						f.tidtoadd.value = "";
					}
				});
			}

			function openIndPopup(occid){
				openPopup('../individual/index.php?occid=' + occid);
			}

			function openEditorPopup(occid){
				openPopup('occurrenceeditor.php?occid=' + occid);
			}

			function openPopup(urlStr){
				var wWidth = 900;
				if(document.body.offsetWidth) wWidth = document.body.offsetWidth*0.9;
				if(wWidth > 1200) wWidth = 1200;
				newWindow = window.open(urlStr,'popup','scrollbars=1,toolbar=0,resizable=1,width='+(wWidth)+',height=600,left=20,top=20');
				if (newWindow.opener == null) newWindow.opener = self;
				return false;
			}
		</script>
		<style>
			.top-breathing-room-sm-px {
				margin-top: 5px;
			}
			.left-breathing-room-rel-lg {
				margin-left: 2em;
			}
		</style>
	</head>
	<body>
	<?php
	$displayLeftMenu = (isset($collections_batchdeterminationsMenu) ? $collections_batchdeterminationsMenu : false);
	include($SERVER_ROOT . '/includes/header.php');
	?>
	<div class='navpath'>
		<a href='../../index.php'><?php echo htmlspecialchars($LANG['HOME'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?></a> &gt;&gt;
		<a href="../misc/collprofiles.php?collid=<?php echo htmlspecialchars($collid, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?>&emode=1"><?php echo htmlspecialchars($LANG['COLL_MANAGE'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?></a> &gt;&gt;
		<b><?php echo $LANG['BATCH_DETERS']; ?></b>
	</div>
	<!-- This is inner text! -->
	<div role="main" id="innertext">
		<h1 class="page-heading"><?= $LANG['BATCH_DETERS']; ?></h1>
		<?php
		if($isEditor){
			echo '<h2>'.$occManager->getCollName().'</h2>';
			?>
			<div>
				<section class="fieldset-like">
					<h2> <span> <?php echo $LANG['DEFINE_RECORDSET']; ?> </span> </h2>
					<div>
						<?php echo $LANG['RECORDSET_EXPLAIN']; ?>
					</div>
					<div style="margin-top:15px;">
						<form name="accqueryform" action="batchdeterminations.php" method="post" onsubmit="return submitAccForm(this);">
							<section class="flex-form" style="align-items: center; gap:0.5rem; margin-bottom: 1rem">
								<div style="margin: 0; display:flex; align-items: center; gap:0.25rem" title="<?php echo (defined('CATALOGNUMBERTIP') ? CATALOGNUMBERTIP : ''); ?>">
									<label for="catalognumber"><?php echo (defined('CATALOGNUMBERLABEL')?CATALOGNUMBERLABEL:$LANG['CATNUM']); ?>:</label>
									<input style="margin: 0" name="catalognumber" id="catalognumber" type="text" style="border-color:green;width:200px;" />
								</div>
								<div style="margin: 0">
									<input name="allcatnum" id="allcatnum" type="checkbox" checked /> <label for="allcatnum"><?php echo $LANG['TARGET_ALL']; ?></label>
								</div>
							</section>
							<div style="margin-bottom: 1rem; display:flex; align-items: center; gap:0.25rem" title="<?php echo (defined('DETERMINATIONTAXONTIP') ? DETERMINATIONTAXONTIP : ''); ?>">
								<label for="nomsciname"><?php echo $LANG['TAXON']; ?>:</label>
								<input style="margin:0; width:260px;" type="text" id="nomsciname" name="sciname" onfocus="initScinameAutocomplete(this.form)" />
							</div>
							<section class="flex-form">
								<div style="margin: 0">
									<button name="addrecord" type="submit"><?php echo $LANG['ADD_RECORDS']; ?></button>
									<img id="workingcircle" src="../../images/workingcircle.gif" style="display:none;" alt="progress is being made" />
								</div>
								<div style="margin: 0; margin-top: 5px">
									<button name="clearaccform" type="button" onclick='clearAccForm(this.form)'><?php echo $LANG['CLEAR_LIST']; ?></button>
									<input name="collid" type="hidden" value="<?php echo $collid; ?>" />
								</div>
							</section>
						</form>
					</div>
					<div style="margin-top: 1rem">
						* <?php echo $LANG['LIST_LIMIT']; ?><br/>
					</div>
					<?php
					if($statusStr){
						echo '<div style="margin:30px 20px;">';
						echo '<div style="color:orange;font-weight:bold;">'.$statusStr.'</div>';
						echo '<div style="margin-top:10px;"><a href="../reports/annotationmanager.php?collid=' . htmlspecialchars($collid, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE) . '" target="_blank">' . htmlspecialchars($LANG['DISPLAY_QUEUE'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE) . '</a></div>';
						echo '</div>';
					}
					?>
				</section>
				<div id="accrecordlistdviv" style="display:none;">
					<form name="accselectform" id="accselectform" action="batchdeterminations.php" method="post" onsubmit="return validateSelectForm(this);">
						<div style="margin-top: 15px; margin-left: 10px;">
							<input name="accselectall" value="" type="checkbox" onclick="selectAll(this);" checked />
							<?php echo $LANG['SELECT_DESELECT']; ?>
						</div>
						<table class="styledtable">
							<thead>
								<tr>
									<th style="width:25px;text-align:center;">&nbsp;</th>
									<th style="width:125px;text-align:center;"><?php echo (defined('CATALOGNUMBERLABEL')?CATALOGNUMBERLABEL:(isset($LANG['CATNUM'])?$LANG['CATNUM']:'Catalog Number')); ?></th>
									<th style="width:300px;text-align:center;"><?php echo (defined('SCIENTIFICNAMELABEL')?SCIENTIFICNAMELABEL:(isset($LANG['SCINAME'])?$LANG['SCINAME']:'Scientific Name')); ?></th>
									<th style="text-align:center;"><?php echo $LANG['COLLECTOR_LOCALITY']; ?></th>
								</tr>
							</thead>
							<tbody id="catrecordstbody"></tbody>
						</table>
						<div id="newdetdiv" style="">
							<fieldset style="margin: 15px 15px 0px 15px;padding:15px;">
								<legend><b><?php echo $LANG['NEW_DET_DETAILS']; ?></b></legend>
								<div style='margin:3px;position:relative;height:35px' title="<?php echo (defined('ANNOTATIONTYPETIP') ? ANNOTATIONTYPETIP : ''); ?>">
									<div style="float:left;">
										<b><?php echo $LANG['ANNOTATION_TYPE']; ?>:</b>
									</div>
									<div style="float:left;">
										<input name="annotype" type="radio" value="id" onchange="annotationTypeChanged(this)" checked /> <?php echo $LANG['ID_ADJUST']; ?><br/>
										<input name="annotype" type="radio" value="na" onchange="annotationTypeChanged(this)" /> <?php echo $LANG['NOM_ADJUST']; ?>
									</div>
								</div>
								<div style="clear:both;margin:15px 0px"><hr /></div>
								<div id="idQualifierDiv" style='margin:3px;clear:both' title="<?php echo (defined('IDENTIFICATIONQUALIFIERTIP') ? IDENTIFICATIONQUALIFIERTIP : 'e.g. cf, aff, etc'); ?>">
									<b><?php echo (defined('IDENTIFICATIONQUALIFIERLABEL')?IDENTIFICATIONQUALIFIERLABEL:(isset($LANG['ID_QUALIFIER'])?$LANG['ID_QUALIFIER']:'Identification Qualifier')); ?>:</b>
									<input type="text" name="identificationqualifier" title="e.g. cf, aff, etc" />
								</div>
								<div style='margin:3px;' title="<?php echo (defined('SCIENTIFICNAMETIP') ? SCIENTIFICNAMETIP : ''); ?>">
									<b><?php echo (defined('SCIENTIFICNAMELABEL')?SCIENTIFICNAMELABEL:(isset($LANG['SCINAME'])?$LANG['SCINAME']:'Scientific Name')); ?>:</b>
									<input type="text" id="dafsciname" name="sciname" style="background-color:lightyellow;width:350px;" onfocus="initDetAutocomplete(this.form)" />
									<input type="hidden" id="daftidtoadd" name="tidtoadd" value="" />
									<input type="hidden" name="family" value="" />
								</div>
								<div style='margin:3px;' title="<?php echo (defined('SCIENTIFICNAMEAUTHORSHIPTIP') ? SCIENTIFICNAMEAUTHORSHIPTIP : ''); ?>">
									<b><?php echo (defined('SCIENTIFICNAMEAUTHORSHIPLABEL')?SCIENTIFICNAMEAUTHORSHIPLABEL:(isset($LANG['AUTHOR'])?$LANG['AUTHOR']:'Author')); ?>:</b>
									<input type="text" name="scientificnameauthorship" style="width:200px;" />
								</div>
								<div id="codDiv" style='margin:3px;' title="<?php echo (defined('IDCONFIDENCETIP') ? IDCONFIDENCETIP : ''); ?>">
									<b><?php echo (defined('IDCONFIDENCELABEL')?IDCONFIDENCELABEL:(isset($LANG['CONFIDENCE'])?$LANG['CONFIDENCE']:'Confidence of Determination')); ?>:</b>
									<select name="confidenceranking">
										<option value="8"><?php echo $LANG['HIGH']; ?></option>
										<option value="5" selected><?php echo $LANG['MEDIUM']; ?></option>
										<option value="2"><?php echo $LANG['LOW']; ?></option>
									</select>
								</div>
								<div id="identifiedByDiv" style='margin:3px;' title="<?php echo (defined('IDENTIFIEDBYTIP') ? IDENTIFIEDBYTIP : ''); ?>">
									<b><?php echo (defined('IDENTIFIEDBYLABEL')?IDENTIFIEDBYLABEL:(isset($LANG['DETERMINER'])?$LANG['DETERMINER']:'Determiner')); ?>:</b>
									<input type="text" name="identifiedby" id="identifiedby" style="background-color:lightyellow;width:200px;" />
								</div>
								<div id="dateIdentifiedDiv" style='margin:3px;' title="<?php echo (defined('DATEIDENTIFIEDTIP') ? DATEIDENTIFIEDTIP : ''); ?>">
									<b><?php echo (defined('DATEIDENTIFIEDLABEL')?DATEIDENTIFIEDLABEL:(isset($LANG['DATE'])?$LANG['DATE']:'Date')); ?>:</b>
									<input type="text" name="dateidentified" id="dateidentified" style="background-color:lightyellow;" onchange="detDateChanged(this.form);" />
								</div>
								<div style='margin:3px;' title="<?php echo (defined('IDENTIFICATIONREFERENCETIP') ? IDENTIFICATIONREFERENCETIP : ''); ?>">
									<b><?php echo (defined('IDENTIFICATIONREFERENCELABEL')?IDENTIFICATIONREFERENCELABEL:(isset($LANG['REFERENCE'])?$LANG['REFERENCE']:'Reference')); ?>:</b>
									<input type="text" name="identificationreferences" style="width:350px;" />
								</div>
								<div style='margin:3px;' title="<?php echo (defined('IDENTIFICATIONREMARKSTIP') ? IDENTIFICATIONREMARKSTIP : ''); ?>">
									<b><?php echo (defined('IDENTIFICATIONREMARKSLABEL')?IDENTIFICATIONREMARKSLABEL:(isset($LANG['NOTES'])?$LANG['NOTES']:'Notes')); ?>:</b>
									<input type="text" name="identificationremarks" style="width:350px;" />
								</div>
								<div id="makeCurrentDiv" style='margin:3px;'title="<?php echo (defined('MAKECURRENTDETERMINATIONTIP') ? MAKECURRENTDETERMINATIONTIP : ''); ?>">
									<input type="checkbox" name="makecurrent" value="1" checked /> <?php echo $LANG['MAKE_CURRENT']; ?>
								</div>
								<div style='margin:3px;'  title="<?php echo (defined('ANNOTATIONPRINTQUEUETIP') ? ANNOTATIONPRINTQUEUETIP : ''); ?>">
									<input type="checkbox" name="printqueue" value="1" checked /> <?php echo $LANG['ADD_PRINT_QUEUE']; ?>
									<a href="../reports/annotationmanager.php?collid=<?php echo htmlspecialchars($collid, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?>" target="_blank"><img src="../../images/list.png" style="width:1.2em" title="<?php echo htmlspecialchars($LANG['DISPLAY_QUEUE'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE); ?>" /></a>
								</div>
								<div style='margin:15px;'>
									<div style="float:left;">
										<input name="collid" type="hidden" value="<?php echo $collid; ?>" />
										<input name="tabtarget" type="hidden" value="0" />
										<button type="submit" name="formsubmit" value="Add New Determinations"><?php echo $LANG['ADD_DETERS']; ?></button>
									</div>
								</div>
							</fieldset>
						</div>
					</form>
				</div>
			</div>
			<?php
		}
		else{
			?>
			<div style="font-weight:bold;margin:20px;font-weight:150%;">
				<?php echo $LANG['NO_PERMISSIONS']; ?>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	include($SERVER_ROOT.'/includes/footer.php');
	?>
	</body>
</html>
