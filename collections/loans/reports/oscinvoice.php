<?php
include_once('../../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceLoans.php');
require_once $SERVER_ROOT.'/vendor/phpoffice/phpword/bootstrap.php';

$collId = $_REQUEST['collid'];
$identifier = array_key_exists('identifier',$_REQUEST)?$_REQUEST['identifier']:0;
$loanType = array_key_exists('loantype',$_REQUEST)?$_REQUEST['loantype']:0;
$outputMode = $_POST['outputmode'];
$languageDef = $_POST['languagedef'];

$loanManager = new OccurrenceLoans();
if($collId) $loanManager->setCollId($collId);

$english = ($languageDef == 0 || $languageDef == 1);
$engspan = ($languageDef == 1);
$spanish = ($languageDef == 1 || $languageDef == 2);

$invoiceArr = $loanManager->getInvoiceInfo($identifier,$loanType);
$addressArr = $loanManager->getFromAddress($collId);
$isInternational = true;
if($invoiceArr['country'] == $addressArr['country']) $isInternational = false;

if($loanType == 'exchange'){
	$transType = 0;
	if(($invoiceArr['totalexunmounted'] || $invoiceArr['totalexmounted']) && (!$invoiceArr['totalgift'] && !$invoiceArr['totalgiftdet'])){
		$transType = 'ex';
	}
	elseif(($invoiceArr['totalexunmounted'] || $invoiceArr['totalexmounted']) && ($invoiceArr['totalgift'] || $invoiceArr['totalgiftdet'])){
		$transType = 'both';
	}
	elseif((!$invoiceArr['totalexunmounted'] || !$invoiceArr['totalexmounted']) && ($invoiceArr['totalgift'] || $invoiceArr['totalgiftdet'])){
		$transType = 'gift';
	}
}

$numSpecimens = 0;
if($loanType == 'exchange') $numSpecimens = $loanManager->getExchangeTotal($identifier);
else{
	$specList = $loanManager->getSpecimenList($identifier);
	if($specList){
		if(count($specList) == 1) $numSpecimens = 1;
		else $numSpecimens = count($specList);
	}
	else{
		if($invoiceArr['numspecimens'] == 1){$numSpecimens = 1;}
		else{$numSpecimens = $invoiceArr['numspecimens'];}
	}
}

$numBoxes = 0;
if($loanType == 'exchange'){$numBoxes = $invoiceArr['totalboxes'];}
else{
	if($loanType == 'out'){
		if($invoiceArr['totalboxes'] == 1){$numBoxes = 1;}
		else{$numBoxes = $invoiceArr['totalboxes'];}
	}
	else{
		if($invoiceArr['totalboxesreturned'] == 1){$numBoxes = 1;}
		else{$numBoxes = $invoiceArr['totalboxesreturned'];}
	}
}

if($outputMode == 'doc'){
	$phpWord = new \PhpOffice\PhpWord\PhpWord();
	$phpWord->addParagraphStyle('header', array('align'=>'center','lineHeight'=>1.0,'spaceAfter'=>450));
	$phpWord->addFontStyle('headerFont', array('size'=>12,'bold'=>true,'name'=>'Arial'));
	$phpWord->addParagraphStyle('toAddress', array('align'=>'left','lineHeight'=>1.0,'spaceBefore'=>0,'spaceAfter'=>0));
	$phpWord->addFontStyle('toAddressFont', array('size'=>10,'name'=>'Arial'));
	$phpWord->addParagraphStyle('identifier', array('align'=>'right','lineHeight'=>1.0,'spaceBefore'=>0,'spaceAfter'=>0));
	$phpWord->addFontStyle('identifierFont', array('size'=>10,'bold'=>true,'name'=>'Arial'));
	$phpWord->addParagraphStyle('sendwhom', array('align'=>'left','lineHeight'=>1.0,'spaceBefore'=>0,'spaceAfter'=>0));
	$phpWord->addFontStyle('sendwhomFont', array('size'=>10,'name'=>'Arial'));
	$phpWord->addParagraphStyle('returnamtdue', array('align'=>'left','lineHeight'=>1.0,'spaceBefore'=>0,'spaceAfter'=>0));
	$phpWord->addFontStyle('returnamtdueFont', array('size'=>10,'bold'=>true,'name'=>'Arial'));
	$phpWord->addParagraphStyle('other', array('align'=>'left','lineHeight'=>1.0,'spaceBefore'=>0,'spaceAfter'=>0));
	$phpWord->addFontStyle('otherFont', array('size'=>10,'name'=>'Arial'));
	$tableStyle = array('width'=>100);
	$colRowStyle = array('cantSplit'=>true);
	$phpWord->addTableStyle('headTable',$tableStyle,$colRowStyle);
	$cellStyle = array('valign'=>'top');

	$section = $phpWord->addSection(array('pageSizeW'=>12240,'pageSizeH'=>15840,'marginLeft'=>1080,'marginRight'=>1080,'marginTop'=>1080,'marginBottom'=>0,'headerHeight'=>0,'footerHeight'=>600));

	$textrun = $section->addTextRun('header');
	$textrun->addText(htmlspecialchars($addressArr['institutionname'].' ('.$addressArr['institutioncode'].')'),'headerFont');
	$textrun->addTextBreak(1);
	if($addressArr['institutionname2']){
		$textrun->addText(htmlspecialchars($addressArr['institutionname2']),'headerFont');
		$textrun->addTextBreak(1);
	}
	if($addressArr['address1']){
		$textrun->addText(htmlspecialchars($addressArr['address1']),'headerFont');
		$textrun->addTextBreak(1);
	}
	if($addressArr['address2']){
		$textrun->addText(htmlspecialchars($addressArr['address2']),'headerFont');
		$textrun->addTextBreak(1);
	}
	$textrun->addText(htmlspecialchars($addressArr['city'].', '.$addressArr['stateprovince'].' '.$addressArr['postalcode'].($isInternational?' '.$addressArr['country']:'')),'headerFont');
	$textrun->addTextBreak(1);
	$textrun->addText(htmlspecialchars($addressArr['phone']),'headerFont');
	$textrun->addTextBreak(2);
	$textrun->addText(htmlspecialchars(($english?'SHIPPING INVOICE':'').($engspan?' / ':'').($spanish?'FACTURA DE REMESA':'')),'headerFont');
	$section->addTextBreak(1);
	$table = $section->addTable('headTable');
	$table->addRow();
	$cell = $table->addCell(5000,$cellStyle);
	$textrun = $cell->addTextRun('toAddress');
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
	$cell = $table->addCell(5000,$cellStyle);
	$textrun = $cell->addTextRun('identifier');
	$textrun->addText(htmlspecialchars(date('l').', '.date('F').' '.date('j').', '.date('Y')),'identifierFont');
	$textrun->addTextBreak(1);
	if($loanType == 'out'){
		$textrun->addText(htmlspecialchars($addressArr['institutioncode'].' Loan ID: '.$invoiceArr['loanidentifierown']),'identifierFont');
	}
	elseif($loanType == 'in'){
		$textrun->addText(htmlspecialchars($addressArr['institutioncode'].' Loan-in ID: '.$invoiceArr['loanidentifierborr']),'identifierFont');
	}
	elseif($loanType == 'exchange'){
		$textrun->addText(htmlspecialchars($addressArr['institutioncode'].' Transaction ID: '.$invoiceArr['identifier']),'identifierFont');
	}
	$section->addTextBreak(1);
	$textrun = $section->addTextRun('sendwhom');
	if($english){
		$textrun->addText(htmlspecialchars('We are sending you '.($numBoxes == 1?'1 box ':$numBoxes.' boxes ')),'sendwhomFont');
		$textrun->addText(htmlspecialchars('containing '.($numSpecimens == 1?'1 occurrence. ':$numSpecimens.' occurrences. ')),'sendwhomFont');
		if(($loanType == 'in' && $invoiceArr['shippingmethodreturn']) || $invoiceArr['shippingmethod']){
			$textrun->addText(htmlspecialchars(($numBoxes == 1?'This package was ':'These packages were ').'delivered via '.($loanType == 'in'?$invoiceArr['shippingmethodreturn']:$invoiceArr['shippingmethod']).'. '),'sendwhomFont');
		}
		$textrun->addText(htmlspecialchars('Upon arrival of the shipment, kindly verify its contents and acknowledge '),'sendwhomFont');
		$textrun->addText(htmlspecialchars('receipt by signing and returning the duplicate invoice to us.'),'sendwhomFont');
	}
	if($engspan){
		$textrun->addTextBreak(2);
	}
	if($spanish){
		$textrun->addText(htmlspecialchars('Estámos remitiendo a Uds. '.($numBoxes == 1?'1 caja ':$numBoxes.' cajas ')),'sendwhomFont');
		$textrun->addText(htmlspecialchars('de '.($numSpecimens == 1?'1 ejemplar. ':$numSpecimens.' ejemplares. ')),'sendwhomFont');
		if(($loanType == 'in' && $invoiceArr['shippingmethodreturn']) || $invoiceArr['shippingmethod']){
			$textrun->addText(htmlspecialchars(($numBoxes == 1?'Esta remesa hubiera enviado ':'Estas remesas hubieran enviado ').'por '.($loanType == 'in'?$invoiceArr['shippingmethodreturn']:$invoiceArr['shippingmethod']).'. '),'sendwhomFont');
		}
		$textrun->addText(htmlspecialchars('Al llegar la remesa, por favor verifique los contenidos y sírvase acusar '),'sendwhomFont');
		$textrun->addText(htmlspecialchars('recibo de esta remesa firmiendo una de las copias y devolviéndo la por correo.'),'sendwhomFont');
	}
	if($loanType == 'out'){
		$textrun->addTextBreak(2);
		if($english){
			$textrun->addText(htmlspecialchars('This shipment is a LOAN for study by '.$invoiceArr['forwhom']),'sendwhomFont');
		}
		if($engspan){
			$textrun->addTextBreak(2);
		}
		if($spanish){
			$textrun->addText(htmlspecialchars('Esta remesa es un PRESTAMO para el estudio de '.$invoiceArr['forwhom']),'sendwhomFont');
		}
		$textrun = $section->addTextRun('returnamtdue');
		$textrun->addTextBreak(1);
		if($english){
			$textrun->addText(htmlspecialchars('Loans are made for a period of 2 years. This loan will be due '.$invoiceArr['datedue'].'.'),'returnamtdueFont');
		}
		if($engspan){
			$textrun->addTextBreak(2);
		}
		if($spanish){
			$textrun->addText(htmlspecialchars('Los préstamos se extienden por un periodo de 2 años. Este préstamo tiene una fecha límite de '.$invoiceArr['datedue'].'.'),'returnamtdueFont');
		}
		$textrun->addTextBreak(2);
		if($english){
			$textrun->addText(htmlspecialchars('When circumstances warrant, the loan period may be extended. Specimens should be returned by '),'otherFont');
			$textrun->addText(htmlspecialchars('insured parcel post or by prepaid express. All material of this loan should be returned at the same time. Notes or '),'otherFont');
			$textrun->addText(htmlspecialchars('changes should be written on annotation labels. Reprints dealing with taxonomic groups will be appreciated.'),'otherFont');
		}
		if($engspan){
			$textrun->addTextBreak(2);
		}
		if($spanish){
			$textrun->addText(htmlspecialchars('Siempre y cuando las circunstancias se permiten, se puede pedir un prórroga de la fecha límite de este '),'otherFont');
			$textrun->addText(htmlspecialchars('préstamo. Todo material del préstamo debe devolverse en el mismo envío. Notas y cambios de identificación se '),'otherFont');
			$textrun->addText(htmlspecialchars('deben indicar con notas de anotación. Además, le pedimos mandar separatas de cualquier publicación '),'otherFont');
			$textrun->addText(htmlspecialchars('proveniente del uso de este material.'),'otherFont');
		}
	}
	elseif($loanType == 'in'){
		$section->addTextBreak(1);
		$textrun = $section->addTextRun('returnamtdue');
		if($english){
			$textrun->addText(htmlspecialchars('This shipment is a return of '.$invoiceArr['institutioncode'].' '),'returnamtdueFont');
			$textrun->addText(htmlspecialchars('loan '.$invoiceArr['loanidentifierown'].', received '.$invoiceArr['datereceivedborr']),'returnamtdueFont');
		}
		if($engspan){
			$textrun->addTextBreak(2);
		}
		if($spanish){
			$textrun->addText(htmlspecialchars('En esta remesa se devuelve el prestamo '.$invoiceArr['loanidentifierown'].' '),'returnamtdueFont');
			$textrun->addText(htmlspecialchars('de '.$invoiceArr['institutioncode'].', recibido '.$invoiceArr['datereceivedborr']),'returnamtdueFont');
		}
	}
	elseif($loanType == 'exchange'){
		if($transType == 'ex' || $transType == 'both'){
			$exchangeValue = $loanManager->getExchangeValue($identifier);
			$section->addTextBreak(1);
			$textrun = $section->addTextRun('returnamtdue');
			if($english){
				$textrun->addText(htmlspecialchars('This shipment is an EXCHANGE, consisting of '.($invoiceArr['totalexunmounted']?$invoiceArr['totalexunmounted'].' unmounted ':'')),'returnamtdueFont');
				$textrun->addText(htmlspecialchars((($invoiceArr['totalexunmounted'] && $invoiceArr['totalexmounted'])?'and ':'').($invoiceArr['totalexmounted']?$invoiceArr['totalexmounted'].' mounted ':'')),'returnamtdueFont');
				$textrun->addText(htmlspecialchars('specimens, for an exchange value of '.$exchangeValue.'. Please note that mounted specimens count as two.'),'returnamtdueFont');
			}
			if($engspan){
				$textrun->addTextBreak(2);
			}
			if($spanish){
				$textrun->addText(htmlspecialchars('Este envío es un INTERCAMBIO, consistiendo en '.($invoiceArr['totalexunmounted']?$invoiceArr['totalexunmounted'].' ejemplares no montados ':'')),'returnamtdueFont');
				$textrun->addText(htmlspecialchars((($invoiceArr['totalexunmounted'] && $invoiceArr['totalexmounted'])?'y ':'').($invoiceArr['totalexmounted']?$invoiceArr['totalexmounted'].' ejemplares montados ':'')),'returnamtdueFont');
				$textrun->addText(htmlspecialchars('con un valor de intercambio de '.$exchangeValue.'. Favor de notarse que las ejemplares montados son de valor 2.'),'returnamtdueFont');
			}
			if($transType == 'both'){
				$textrun->addTextBreak(2);
				if($english){
					$textrun->addText(htmlspecialchars('This shipment also contains '),'returnamtdueFont');
					if($invoiceArr['totalgift']){
						$textrun->addText(htmlspecialchars(($invoiceArr['totalgift'] == 1?'1 gift specimen':$invoiceArr['totalgift'].' gift')),'returnamtdueFont');
					}
					if($invoiceArr['totalgift'] == 1 && !$invoiceArr['totalgiftdet']){
						$textrun->addText(htmlspecialchars('.'),'returnamtdueFont');
					}
					if($invoiceArr['totalgift'] && $invoiceArr['totalgiftdet']){
						$textrun->addText(htmlspecialchars(' and '),'returnamtdueFont');
					}
					if($invoiceArr['totalgiftdet']){
						$textrun->addText(htmlspecialchars(($invoiceArr['totalgiftdet'] == 1?'1 gift-for-det specimen.':$invoiceArr['totalgiftdet'].' gift-for-det')),'returnamtdueFont');
					}
					if($invoiceArr['totalgift'] > 1 || $invoiceArr['totalgiftdet'] > 1){
						$textrun->addText(htmlspecialchars(' specimens.'),'returnamtdueFont');
					}
				}
				if($engspan){
					$textrun->addTextBreak(2);
				}
				if($spanish){
					$textrun->addText(htmlspecialchars('Esta remesa también contiene '),'returnamtdueFont');
					if($invoiceArr['totalgift']){
						$textrun->addText(htmlspecialchars(($invoiceArr['totalgift'] == 1?'1 ejemplar de regalo':$invoiceArr['totalgift'].' ejemplares de regalo')),'returnamtdueFont');
					}
					if($invoiceArr['totalgift'] && $invoiceArr['totalgiftdet']){
						$textrun->addText(htmlspecialchars(' y '),'returnamtdueFont');
					}
					if($invoiceArr['totalgiftdet']){
						$textrun->addText(htmlspecialchars(($invoiceArr['totalgiftdet'] == 1?'1 ejemplar de regalo para identificación':$invoiceArr['totalgiftdet'].' ejemplares de regalo para identificación')),'returnamtdueFont');
					}
					$textrun->addText(htmlspecialchars('.'),'returnamtdueFont');
				}
			}
			$textrun->addTextBreak(2);
			if($english){
				$textrun->addText(htmlspecialchars('Our records show a balance of '.abs($invoiceArr['invoicebalance']).' specimens '),'otherFont');
				$textrun->addText(htmlspecialchars('in '.($invoiceArr['invoicebalance']>0?'our':'your').' favor. Please contact us if your records differ significantly.'),'otherFont');
			}
			if($engspan){
				$textrun->addTextBreak(2);
			}
			if($spanish){
				$textrun->addText(htmlspecialchars('Nuestros registros muestran un balance de '.abs($invoiceArr['invoicebalance']).' ejemplares '),'otherFont');
				$textrun->addText(htmlspecialchars('a '.($invoiceArr['invoicebalance']>0?'nuestro':'su').' favor. Favor de contactarnos si sus '),'otherFont');
				$textrun->addText(htmlspecialchars('registros se dífieren de una manera apreciable.'),'otherFont');
			}
		}
		elseif($transType == 'gift'){
			$section->addTextBreak(1);
			$textrun = $section->addTextRun('returnamtdue');
			if($english){
				$textrun->addText(htmlspecialchars('This shipment is a '),'returnamtdueFont');
				if($invoiceArr['totalgift'] && !$invoiceArr['totalgiftdet']){
					$textrun->addText(htmlspecialchars('GIFT.'),'returnamtdueFont');
				}
				if($invoiceArr['totalgift'] && $invoiceArr['totalgiftdet']){
					$textrun->addText(htmlspecialchars('GIFT and GIFT-FOR-DET.'),'returnamtdueFont');
				}
				if(!$invoiceArr['totalgift'] && $invoiceArr['totalgiftdet']){
					$textrun->addText(htmlspecialchars('GIFT-FOR-DET.'),'returnamtdueFont');
				}
			}
			if($engspan){
				$textrun->addTextBreak(2);
			}
			if($spanish){
				$textrun->addText(htmlspecialchars('Este envío es un '),'returnamtdueFont');
				if($invoiceArr['totalgift'] && !$invoiceArr['totalgiftdet']){
					$textrun->addText(htmlspecialchars('REGALO.'),'returnamtdueFont');
				}
				if($invoiceArr['totalgift'] && $invoiceArr['totalgiftdet']){
					$textrun->addText(htmlspecialchars('REGALO y un REGALO PARA IDENTIFICACIÓN.'),'returnamtdueFont');
				}
				if(!$invoiceArr['totalgift'] && $invoiceArr['totalgiftdet']){
					$textrun->addText(htmlspecialchars('REGALO PARA IDENTIFICACIÓN.'),'returnamtdueFont');
				}
			}
		}
	}
	$section->addTextBreak(1);
	$textrun = $section->addTextRun('returnamtdue');
	$textrun->addText(htmlspecialchars(($english?'DESCRIPTION OF THE SPECIMENS':'').($engspan?' / ':'').($spanish?'DESCRIPCIÓN DE LOS EJEMPLARES':'').':'),'returnamtdueFont');
	$textrun->addTextBreak(2);
	$textrun->addText(htmlspecialchars(($invoiceArr['description']?$invoiceArr['description']:'')),'otherFont');
	$textrun->addTextBreak(2);
	if(array_key_exists('invoicemessage',$invoiceArr) || array_key_exists('invoicemessageown',$invoiceArr) || array_key_exists('invoicemessageborr',$invoiceArr)){
		if($loanType == 'exchange'){
			$textrun->addText(htmlspecialchars(($invoiceArr['invoicemessage']?$invoiceArr['invoicemessage']:'')),'otherFont');
		}
		elseif($loanType == 'out'){
			$textrun->addText(htmlspecialchars(($invoiceArr['invoicemessageown']?$invoiceArr['invoicemessageown']:'')),'otherFont');
		}
		elseif($loanType == 'in'){
			$textrun->addText(htmlspecialchars(($invoiceArr['invoicemessageborr']?$invoiceArr['invoicemessageborr']:'')),'otherFont');
		}
		$textrun->addTextBreak(2);
	}
	$textrun->addText(htmlspecialchars(($english?'Sincerely':'').($engspan?' / ':'').($spanish?'Sinceramente':'').','),'otherFont');
	$footer = $section->addFooter();
	$textrun = $footer->addTextRun('other');
	$textrun->addLine(array('weight'=>1,'width'=>670,'height'=>0,'dash'=>'dash'));
	$textrun->addTextBreak(1);
	if($english){
		$textrun->addText(htmlspecialchars('PLEASE SIGN AND RETURN ONE COPY UPON RECEIPT OF THIS SHIPMENT.'),'otherFont');
	}
	if($engspan){
		$textrun->addTextBreak(2);
	}
	if($spanish){
		$textrun->addText(htmlspecialchars('POR FAVOR FIRME Y DEVUELVE UNA COPIA AL LLEGAR ESTA REMESA.'),'otherFont');
	}
	$textrun->addTextBreak(2);
	$textrun->addText(htmlspecialchars(($english?'The above specimens were received in good condition':'').($engspan?' / ':'').($spanish?'Recibido en buenas condiciones':'').'.'),'otherFont');
	$textrun->addTextBreak(2);
	$textrun->addText(htmlspecialchars(($english?'Signed':'').($engspan?'/':'').($spanish?'Firma':'').':______________________________________  '.($english?'Date':'').($engspan?'/':'').($spanish?'Fecha':'').':______________'),'otherFont');

	$targetFile = $SERVER_ROOT.'/temp/report/'.$identifier.'_invoice.docx';
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
			<title><?php echo $identifier; ?> Invoice</title>
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

				/* Basic page view formatting */

				.body {
					width: 800px;
					margin-left: auto;
					margin-right: auto;
					font-family: "Times New Roman", Times, serif;
					font-size: 12pt;
				}

				/* US-letter size */

				.letter {
					width: 8.5in;
					height: 11in;
					margin-top: 0;
				}

				.controls {
					width: 800px;
					margin: 0px auto;
					padding-bottom: 30px;
				}

				#addressheader {
					height: 120px;
				}

				.address {
					float: right;
					font-family: Arial, Helvetica, sans-serif;
					font-size: 10pt;
					position: relative;
					top: 50%;
					transform: translateY(-50%);
				}

				#notice {
					text-align: center;
					margin: 20px auto;
					border-style: double;
					font-size: 20px;
					width: 240px;
				}

				hr.single {
					margin: 15px 0px;
					height: 0px;
					background-color: white;
					border-top: 2px solid black;
				}

				hr.double {
					margin: 15px 0px;
					height: 2px;
					background-color: white;
					border-top: 2px solid black;
					border-bottom: 2px solid black;
				}

				.blank {
					display: inline-block; 
					text-align: center; 
					border-bottom: 1px solid; 
					font-weight: bold;
				}

				.box {
					width: 30px;
					height: 1.2em;
					border:  1px solid gray;
					font-weight:  bold;
					padding: 0px 8px;
					margin: auto 5px;
					display:  inline-block;
					vertical-align: middle;
				}

				#contentstable td {
					padding:  2px 0px;
				}

				#regulations {
					margin: 10px 0px;
				}

				.description {
					text-decoration: underline;
					margin-bottom: 10px;
				}

				#signaturefooter {
					position: fixed;
					bottom: 50px;
					line-height: 220%;
				}

				/* Print formatting */

				@media print {
				 	.controls {
				    	display: none;
					}

					.body {
					    width: auto;
					    margin-left: 0.75in;
					    margin-right: 0.75in;
					    margin-top: 0.5in;
					    margin-bottom: 0.5in;
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
					var invoice = document.getElementById('invoice');
					let isEditable = invoice.contentEditable === 'true';
					if (isEditable) {
						invoice.contentEditable = 'false';
						document.querySelector('#edit').innerText = 'Edit Invoice';
						invoice.style.border = 'none';

						// Show exchange balance div if there's an exchange
						if(document.getElementById('exchangetrue').innerText == "X") {
							document.getElementById('exchangebalance').style.display="block";
						} else {
							document.getElementById('exchangebalance').style.display="none";
						}

					} else {
						invoice.contentEditable = 'true';
						document.querySelector('#edit').innerText = 'Save';
						invoice.style.border = '2px solid #03fc88';
					}
				}

				// Run when the page loads to potentially show the exchange balance div
				window.onload = function() {
					// Show exchange balance div if there's an exchange
					if(document.getElementById('exchangetrue').innerText == "X") {
						document.getElementById('exchangebalance').style.display="block";
					}
				};

			</script>
		</head>
		<body style="background-color:#ffffff;">
			<div class="controls">
				<button id="edit" style="font-weight: bold;" onclick="toggleEdits();">Edit Invoice</button>
				<button id="print" style="margin-left: 30px; font-weight: bold;" onclick="window.print();">Print/Save PDF</button>
			</div>
			<div id="invoice" class="body letter" contenteditable="false">
				<div id="addressheader">
					<div style="float: left;"><img src="../../../images/OSC-Logo.png" width="280px"></img></div>
					<div class="address">

						<?php
						if ($addressArr['institutionname2']) {
							echo '<div style="font-size: 12pt; font-weight: bold">' . $addressArr['institutionname2'] . '</div>';
							echo '<div>' . $addressArr['institutionname'] . ' ('. $addressArr['institutioncode'] . ')</div>';
						} else {
							echo '<div style="font-size: 12pt; font-weight: bold">' . $addressArr['institutionname'] . ' ('. $addressArr['institutioncode'] . ')</div>';
						}

						if($addressArr['address1']){
							echo '<div>' . $addressArr['address1'] . '</div>';
						}

						if($addressArr['address2']){
							echo '<div>' . $addressArr['address2']. '</div>';
						}

						echo '<div>' . $addressArr['city'].', '.$addressArr['stateprovince'].' '.$addressArr['postalcode']. '</div>';
						
						if($addressArr['phone']){
							echo '<div><strong>Tel: </strong>' . $addressArr['phone']. '</div>';
						}
						if($addressArr['url']){
							echo '<div>' . $addressArr['url']. '</div>';
						}
						?>
					</div>
				</div>
				<div id="notice">SHIPPING NOTICE</div>
				<div id="outgoinginfo">
					<table style="width: 100%;">
						<tr>
							<td style="width: 5%">To: </td>
							<td style="width: 65%;"><?php echo $invoiceArr['contact']; ?></td>
							<td style="width: 30%;">Date: <?php echo date('F j, Y', 
								isset($invoiceArr['datesent']) ? strtotime($invoiceArr['datesent']) : time());?></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<?php
								echo $invoiceArr['institutionname'].' ('.$invoiceArr['institutioncode'].')<br />';
								if($invoiceArr['institutionname2']) echo $invoiceArr['institutionname2'].'<br />';
								if($invoiceArr['address1']) echo $invoiceArr['address1'].'<br />';
								if($invoiceArr['address2']) echo $invoiceArr['address2'].'<br />';
								echo $invoiceArr['city'].', '.$invoiceArr['stateprovince'].' '.$invoiceArr['postalcode'];
								if($isInternational) echo '<br />'.$invoiceArr['country'];
								?>
							</td>
							<td></td>
						</tr>
					</table>
				</div>
				<hr class="single">
				<div id="shippingcontents">
					<table id="contentstable">
						<tr>
							<td colspan="2">
								We are sending you <?php echo $numBoxes == 1 | $numBoxes == 0 ? '1 box' : $numBoxes . ' boxes'; ?> by 
								<div class="blank" style="width: 150px;">
									<?php echo ($loanType == 'in' ? ($invoiceArr['shippingmethodreturn'] ? $invoiceArr['shippingmethodreturn'] : 'Library Rate') : ($invoiceArr['shippingmethod'] ? $invoiceArr['shippingmethod'] : 'Library Rate')); ?>
								</div>
								containing <?php echo $numSpecimens == 0 ? 'the specimens listed below. ' : ($numSpecimens == 1 ? '1 specimen. ' : $numSpecimens . ' specimens. '); ?>
							</td>
						</tr>
						<tr>
							<td style="width: 50%;">
								<div id="exchangetrue" class="box"><?php if($loanType == "exchange" && $invoiceArr['totalexmounted'] + $invoiceArr['totalexunmounted'] > 0) echo 'X';?></div>
								Exchange
							</td>
							<td>
								<?php if($loanType == "exchange" && $invoiceArr['totalexmounted'] + $invoiceArr['totalexunmounted'] > 0) echo $addressArr['institutioncode'].' Transaction ID: <strong>'.$invoiceArr['identifier'] . '</strong>'; ?>
							</td>
						</tr>
						<tr>
							<td>
								<div class="box"><?php if($loanType == "exchange" && $invoiceArr['totalgift'] + $invoiceArr['totalgiftdet'] > 0) echo 'X'; ?></div>
								Gift
							</td>
							<td>
								<?php if($loanType == "exchange" && $invoiceArr['totalexmounted'] + $invoiceArr['totalexunmounted'] == 0) echo $addressArr['institutioncode'].' Transaction ID: <strong>'.$invoiceArr['identifier'] . '</strong>'; ?>
							</td>
						</tr>
						<tr>
							<td>
								<div class="box"><?php if($loanType == "out") echo 'X'; ?></div>
								Loan
							</td>
							<td>
								<?php 
								if($loanType == "out") {
									echo $addressArr['institutioncode'] . ' ID: <strong>' . $invoiceArr['loanidentifierown'] . '</strong>';
									if($invoiceArr['datedue']) echo ' | Due back: <strong>' . $invoiceArr['datedue'] . '</strong>';
								}
								?>
							</td>
						</tr>
						<tr>
							<td>
								<div class="box"><?php if($loanType == "in") echo 'X'; ?></div>
								Return of Borrowed Specimens
							</td>
							<td>
								<?php 
								if($loanType == "in") echo $invoiceArr['institutioncode'] . ' ID: <strong>' . $invoiceArr['loanidentifierown'] . '</strong> | ' . $addressArr['institutioncode'] . ' ID: <strong>' . $invoiceArr['loanidentifierborr'] . '</strong>';
								?>
							</td>
						</tr>
					</table>
				</div>
				<div id="exchangebalance" style="margin-top: 10px; display: none;">
					<?php 
					echo 'Our records show a balance of <div class="blank" style="width: 60px;">' . 
						(isset($invoiceArr['invoicebalance']) ? abs($invoiceArr['invoicebalance']) : '0' ) . 
						'</div> specimens in <div class="blank" style="width: 50px;">' .
						(isset($invoiceArr['invoicebalance']) ? ($invoiceArr['invoicebalance'] > 0 ? 'Our' : 'Your') : 'Our'). '</div> favor. Please notify us if this figure is incorrect.';
					?>
				</div>
				<div id="regulations">
					Please advise us if specimens are not received in good order. Borrowers will please observe the <em>Recommendations on Desirable Procedures in Herbarium Practice and Ethics</em> (Brittonia 10: 93-95. 1958).
				</div>
				<hr class="double">
				<div id="description">
					<div class="description">DESCRIPTION OF SPECIMENS</div>
					<?php

					
					// Description
					if($invoiceArr['description']) {
						
						// Auto-format asterisks as bullets
						if(strpos($invoiceArr['description'], "* ") !== false) $invoiceArr['description'] =  
							str_replace(array(" * ", "* "), array("</li><li>", "<ul><li>"), $invoiceArr['description']) . '</li></ul>';

						// Add description
						echo '<div>' . $invoiceArr['description'] . '</div>';
					}

					// Loan recipient
					if(array_key_exists('forwhom',$invoiceArr) && $invoiceArr['forwhom']) echo '<div>Loan for study by ' . $invoiceArr['forwhom'] . '</div>';

					// Additional message
					if(array_key_exists('invoicemessage',$invoiceArr) || array_key_exists('invoicemessageown',$invoiceArr) || array_key_exists('invoicemessageborr',$invoiceArr)){
						echo '<div>';
						if($loanType == 'exchange'){
							echo ($invoiceArr['invoicemessage']?$invoiceArr['invoicemessage']:'');
						}
						elseif($loanType == 'out'){
							echo ($invoiceArr['invoicemessageown']?$invoiceArr['invoicemessageown']:'');
						}
						elseif($loanType == 'in'){
							echo ($invoiceArr['invoicemessageborr']?$invoiceArr['invoicemessageborr']:'');
						}
						echo '</div>';
					}
					?>
				</div>
				<div id="signaturefooter">
					<div style="display: flex; justify-content: flex-end;">Director: Aaron Liston</div>
					<div style="font-size: 11pt;">Upon arrival of the specimens, please verify their receipt by returning one copy of this shipping notice.</div>
					<div style="font-size:  11pt;">
						<div style="display: inline; float: left;">Signature: <div class="blank" style="width: 300px"></div></div>
						<div style="display: inline; float: right;">Date: <div class="blank" style="width: 120px"></div></div>
					</div>
				</div>
			</div>
		</body>
	</html>
	<?php
}
?>