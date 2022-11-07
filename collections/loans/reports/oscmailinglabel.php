<?php
include_once('../../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceLoans.php');
require_once $SERVER_ROOT.'/vendor/phpoffice/phpword/bootstrap.php';

$collId = $_REQUEST['collid'];
$outputMode = $_POST['outputmode'];
$identifier = array_key_exists('identifier',$_REQUEST)?$_REQUEST['identifier']:0;
$loanType = array_key_exists('loantype',$_REQUEST)?$_REQUEST['loantype']:0;
$institution = array_key_exists('institution',$_POST)?$_POST['institution']:0;
$accountNum = array_key_exists('mailaccnum',$_POST)?$_POST['mailaccnum']:0;

$loanManager = new OccurrenceLoans();
if($collId) $loanManager->setCollId($collId);

if($institution){
	$invoiceArr = $loanManager->getToAddress($institution);
}
else{
	$invoiceArr = $loanManager->getInvoiceInfo($identifier,$loanType);
}
$addressArr = $loanManager->getFromAddress($collId);
$isInternational = true;
if($invoiceArr['country'] == $addressArr['country']) $isInternational = false;

if($outputMode == 'doc'){
	$phpWord = new \PhpOffice\PhpWord\PhpWord();
	$phpWord->addParagraphStyle('fromAddress', array('align'=>'left','lineHeight'=>1.0,'spaceAfter'=>0,'keepNext'=>true,'keepLines'=>true));
	$phpWord->addFontStyle('fromAddressFont', array('size'=>10,'name'=>'Arial'));
	$phpWord->addParagraphStyle('toAddress', array('align'=>'left','indent'=>2,'lineHeight'=>1.0,'spaceAfter'=>0,'keepNext'=>true,'keepLines'=>true));
	$phpWord->addFontStyle('toAddressFont', array('size'=>14,'name'=>'Arial'));

	$section = $phpWord->addSection(array('pageSizeW'=>12240,'pageSizeH'=>15840,'marginLeft'=>360,'marginRight'=>360,'marginTop'=>360,'marginBottom'=>360,'headerHeight'=>0,'footerHeight'=>0));

	$textrun = $section->addTextRun('fromAddress');
	$textrun->addText(htmlspecialchars($addressArr['institutionname'].' ('.$addressArr['institutioncode'].')'),'fromAddressFont');
	$textrun->addTextBreak(1);
	if($addressArr['institutionname2']){
		$textrun->addText(htmlspecialchars($addressArr['institutionname2']),'fromAddressFont');
		$textrun->addTextBreak(1);
	}
	if($addressArr['address1']){
		$textrun->addText(htmlspecialchars($addressArr['address1']),'fromAddressFont');
		$textrun->addTextBreak(1);
	}
	if($addressArr['address2']){
		$textrun->addText(htmlspecialchars($addressArr['address2']),'fromAddressFont');
		$textrun->addTextBreak(1);
	}
	$textrun->addText(htmlspecialchars($addressArr['city'].', '.$addressArr['stateprovince'].' '.$addressArr['postalcode']),'fromAddressFont');
	if($isInternational){
		$textrun->addTextBreak(1);
		$textrun->addText(htmlspecialchars($addressArr['country']),'fromAddressFont');
	}
	if($accountNum){
		$textrun->addTextBreak(1);
		$textrun->addText(htmlspecialchars('(Acct. #'.$accountNum.')'),'fromAddressFont');
	}
	$section->addTextBreak(2);
	$textrun = $section->addTextRun('toAddress');
	$textrun->addText(htmlspecialchars($invoiceArr['contact']),'toAddressFont');
	$textrun->addTextBreak(1);
	$textrun->addText(htmlspecialchars($invoiceArr['institutionname'].' ('.$invoiceArr['institutioncode'].')'),'toAddressFont');
	$textrun->addTextBreak(1);
	if($invoiceArr['institutionname2']){
		$textrun->addText(htmlspecialchars($invoiceArr['institutionname2']),'toAddressFont');
		$textrun->addTextBreak(1);
	}
	if($invoiceArr['address1']){
		$textrun->addText(htmlspecialchars($invoiceArr['address1']),'toAddressFont');
		$textrun->addTextBreak(1);
	}
	if($invoiceArr['address2']){
		$textrun->addText(htmlspecialchars($invoiceArr['address2']),'toAddressFont');
		$textrun->addTextBreak(1);
	}
	$textrun->addText(htmlspecialchars($invoiceArr['city'].', '.$invoiceArr['stateprovince'].' '.$invoiceArr['postalcode']),'toAddressFont');
	if($isInternational){
		$textrun->addTextBreak(1);
		$textrun->addText(htmlspecialchars($invoiceArr['country']),'toAddressFont');
	}

	$targetFile = $SERVER_ROOT.'/temp/report/'.$PARAMS_ARR['un'].'_mailing_label.docx';
	$phpWord->save($targetFile, 'Word2007');

	header('Content-Description: File Transfer');
	header('Content-type: application/force-download');
	header('Content-Disposition: attachment; filename='.basename($targetFile));
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: '.filesize($targetFile));
	readfile($targetFile);
	unlink($targetFile);
}
else{
	?>
	<html>
		<head>
			<title>Mailing Label</title>
			<?php
			$activateJQuery = false;
			if(file_exists($SERVER_ROOT.'/includes/head.php')){
				include_once($SERVER_ROOT.'/includes/head.php');
			}
			else{
				echo '<link href="'.$CLIENT_ROOT.'/css/jquery-ui.css" type="text/css" rel="stylesheet" />';
				echo '<link href="'.$CLIENT_ROOT.'/css/base.css?ver=1" type="text/css" rel="stylesheet" />';
				echo '<link href="'.$CLIENT_ROOT.'/css/main.css?ver=1" type="text/css" rel="stylesheet" />';
			}
			?>
			<style type="text/css">

				p.printbreak {page-break-after:always;}

				
				/* Basic page view formatting */

				.body {
					width: 800px;
					margin-left: auto;
					margin-right: auto;
				}

				/* US-letter size */

				.letter {
					width: 8.5in;
					height: 11in;
				}

				.label {
					height: 5in;
					overflow-y: hidden;
					font-family: Arial, Helvetica, sans-serif;
					font-size: 22pt;
				}

				.controls {
					width: 800px;
					margin: 0px auto;
					padding-bottom: 30px;
				}

				.logo {
					float: left; 
					margin-right: 10px;
				}

				#shippingaddress {
					margin-left: 140px; 
					margin-top: 65px;
				}

				/* Print formatting */

				@media print {
				 	.controls {
				    	display: none;
					}

					.body {
					    width: auto;
					    height: 95vh;  
					    margin-left: 0.25in;
					    margin-right: 0.25in;
					    margin-top: 0.5in;
					    margin-bottom: 0in;
					}
				  	div {
					    page-break-before: auto;
					    break-before: auto;
					    page-break-inside: avoid;
					    break-inside: avoid;
				  	}
				}

			</style>


			<script language="javascript">

				// Function to toggle editing of the invoice on or off
				function toggleEdits() {
					var labels = document.getElementById('mailinglabel');
					let isEditable = labels.contentEditable === 'true';
					if (isEditable) {
						labels.contentEditable = 'false';
						document.querySelector('#edit').innerText = 'Edit Labels';
						labels.style.border = 'none';
					} else {
						labels.contentEditable = 'true';
						document.querySelector('#edit').innerText = 'Save';
						labels.style.border = '2px solid #03fc88';
					}
				}

			</script>

		</head>
		<body style="background-color:#ffffff;">
			<div class="controls">
				<button id="edit" style="font-weight: bold;" onclick="toggleEdits();">Edit Labels</button>
				<button id="print" style="margin-left: 30px; font-weight: bold;" onclick="window.print();">Print Labels</button>
				<div style="display: inline;">Print on Avery 5526 waterproof labels</div>
			</div>
			<div id="mailinglabel" class="body letter">
				<div id="labelone" class="label">
					<div id="returnaddress">
						<div class="logo"><img src="<?php echo $addressArr['icon']; ?>" width="128px;"></img></div>
						<div>
							<?php
							if ($addressArr['institutionname2']) {
								echo '<div style="font-weight: bold">' . $addressArr['institutionname2'] . '</div>';
								echo '<div>' . $addressArr['institutionname'] . ' ('. $addressArr['institutioncode'] . ')</div>';
							} else {
								echo '<div style="font-weight: bold">' . $addressArr['institutionname'] . ' ('. $addressArr['institutioncode'] . ')</div>';
							}

							if($addressArr['address1']){
								echo '<div>' . $addressArr['address1'] . '</div>';
							}

							if($addressArr['address2']){
								echo '<div>' . $addressArr['address2']. '</div>';
							}

							echo '<div>' . $addressArr['city'].', '.$addressArr['stateprovince'].' '.$addressArr['postalcode']. '</div>';
							
							if($isInternational){
								echo '<div>' . $addressArr['country']. '</div>';
							}

							if($accountNum){
								echo '<div>' . '(Acct. #'.$accountNum.')</div>';
							}
							?>
						</div>
					</div>
					<div id="shippingaddress">
						<div>To:</div>
						<?php
						echo '<div>' . $invoiceArr['contact']. '</div>';
						echo '<div>' . $invoiceArr['institutionname'].' ('.$invoiceArr['institutioncode'].')</div>';

						if($invoiceArr['institutionname2']){
							echo '<div>' . $invoiceArr['institutionname2']. '</div>';
						}

						if($invoiceArr['address1']){
							echo '<div>' . $invoiceArr['address1']. '</div>';
						}

						if($invoiceArr['address2']){
							echo '<div>' . $invoiceArr['address2']. '</div>';
						}

						echo '<div>' . $invoiceArr['city'].', '.$invoiceArr['stateprovince'].' '.$invoiceArr['postalcode']. '</div>';
						
						if($isInternational){
							echo '<div>' . $invoiceArr['country']. '</div>';
						}
						?>
					</div>
				</div>
				<div id="labeltwo" class="label" style="margin-top: 0.25in;">
					<div id="returnaddress">
						<div class="logo"><img src="<?php echo $addressArr['icon']; ?>" width="128px;"></img></div>
						<div>
							<?php
							if ($addressArr['institutionname2']) {
								echo '<div style="font-weight: bold">' . $addressArr['institutionname2'] . '</div>';
								echo '<div>' . $addressArr['institutionname'] . ' ('. $addressArr['institutioncode'] . ')</div>';
							} else {
								echo '<div style="font-weight: bold">' . $addressArr['institutionname'] . ' ('. $addressArr['institutioncode'] . ')</div>';
							}

							if($addressArr['address1']){
								echo '<div>' . $addressArr['address1'] . '</div>';
							}

							if($addressArr['address2']){
								echo '<div>' . $addressArr['address2']. '</div>';
							}

							echo '<div>' . $addressArr['city'].', '.$addressArr['stateprovince'].' '.$addressArr['postalcode']. '</div>';
							
							if($isInternational){
								echo '<div>' . $addressArr['country']. '</div>';
							}

							if($accountNum){
								echo '<div>' . '(Acct. #'.$accountNum.')</div>';
							}
							?>
						</div>
					</div>
					<div id="shippingaddress">
						<div>To:</div>
						<?php
						echo '<div>' . $invoiceArr['contact']. '</div>';
						echo '<div>' . $invoiceArr['institutionname'].' ('.$invoiceArr['institutioncode'].')</div>';

						if($invoiceArr['institutionname2']){
							echo '<div>' . $invoiceArr['institutionname2']. '</div>';
						}

						if($invoiceArr['address1']){
							echo '<div>' . $invoiceArr['address1']. '</div>';
						}

						if($invoiceArr['address2']){
							echo '<div>' . $invoiceArr['address2']. '</div>';
						}

						echo '<div>' . $invoiceArr['city'].', '.$invoiceArr['stateprovince'].' '.$invoiceArr['postalcode']. '</div>';
						
						if($isInternational){
							echo '<div>' . $invoiceArr['country']. '</div>';
						}
						?>
					</div>
				</div>
			</div>
		</body>
	</html>
	<?php
}
?>