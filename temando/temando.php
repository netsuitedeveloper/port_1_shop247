<?php

/*	
* -----------------------------------------------------------------
*	File			temando.php
*	Version			1.0
*	Create Date		2014/10/15
*	Created By		Hakuna Moni (hakunamoni@gmail.com)
* -----------------------------------------------------------------

*/

require dirname(__FILE__)."/function.php";

$connector		= new temandoconnector;

//$testNum = 'RBCNT77526';

$tableName = 'ship_manifests';

try{
	//$lp_check = $connector->fetch($connector->query("select * from `" . $tableName . "` where `lpNum` = '" . $_POST['lp_number'] . "'"));
	//$lp_check = $connector->fetch($connector->query("select * from `" . $tableName . "` where `lpNum` = 'RBP0005000'"));

	//if ( !$lp_check ){
		//echo "Couldn't Find Matching Ship Manifest.";
		//exit;
		$ci = curl_init();
		//curl_setopt($ci, CURLOPT_URL, "https://forms.na1.netsuite.com/app/site/hosting/scriptlet.nl?script=688&deploy=1&compid=3692243&h=648f9a7970972d7d33ef&lpnum=" . $_POST['lp_number']);
		curl_setopt($ci, CURLOPT_URL, "https://forms.na1.netsuite.com/app/site/hosting/scriptlet.nl?script=688&deploy=1&compid=3692243&h=648f9a7970972d7d33ef&lpnum=RBP0005000");
		curl_setopt($ci, CURLOPT_PORT, 443);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);                                                                                                      
		$result = curl_exec($ci);
		curl_close ($ci);

		if ( $result == 'false' ){
			echo "Couldn't Find Matching Ship Manifest.";
			exit;
		}		

		$result_sm = json_decode($result);

		if ( $result_sm->smobj[0]->columns->custrecord_ship_country != 'AU' ){
			echo "This is only for AU";
			exit;
		}
		
		$consignee			= $result_sm->smobj[0]->columns->custrecord_ship_consignee;
		$contactPhone		= $result_sm->smobj[0]->columns->custrecord_ship_phone;
		$contactPerson		= $result_sm->smobj[0]->columns->custrecord_ship_contactname;
		$shipToAddr1		= $result_sm->smobj[0]->columns->custrecord_ship_addr1;
		$shipToAddr2		= $result_sm->smobj[0]->columns->custrecord_ship_addr2;
		$shipToAddr3		= $result_sm->smobj[0]->columns->custrecord_ship_addr3;
		$shipToCity			= $result_sm->smobj[0]->columns->custrecord_ship_city;
		$shipToState		= $result_sm->smobj[0]->columns->custrecord_ship_state;
		$shipToZip			= $result_sm->smobj[0]->columns->custrecord_ship_zip;
		$shipToCountry		= $result_sm->smobj[0]->columns->custrecord_ship_country;
		$ship_ref			= $result_sm->smobj[0]->columns->custrecord_ship_ref1;
		$packageLength		= $result_sm->smobj[0]->columns->custrecord_ship_length;
		$packageWidth		= $result_sm->smobj[0]->columns->custrecord_ship_width;
		$packageHeight		= $result_sm->smobj[0]->columns->custrecord_ship_height;
		$packageWeight		= $result_sm->smobj[0]->columns->custrecord_ship_pkgwght;
		$packageActWeight	= $result_sm->smobj[0]->columns->custrecord_ship_actwght;
		$orderId			= $result_sm->smobj[0]->columns->custrecord_ship_order->internalid;
		$orderNum			= $result_sm->smobj[0]->columns->custrecord_ship_orderno;
		$cartonId			= $result_sm->smobj[0]->columns->custrecord_ship_contlp;
		$pkgType			= $result_sm->smobj[0]->columns->custrecord_ship_pkgtype;
		$codAmount			= $result_sm->smobj[0]->columns->custrecord_ship_codamount;
		$toEmail			= $result_sm->smobj[0]->columns->custrecord_ship_email;
		$shipMethod			= $result_sm->shipMethod;
		$landPhone			= $result_sm->landlinePhone;
		$mobilePhone		= $result_sm->mobilePhone;
		$alert				= $result_sm->shippingAlert;

		/*$connector->query("insert into `" . $tableName . "` set `lpNum` = '" . $_POST['lp_number'] . "', `internalId` = '" . $result_sm->smobj[0]->id . "', `consignee` = '" . $consignee . "', `contactPhone` = '" . $contactPhone . "', `contactPerson` = '" . $contactPerson . "', `shipToAddr1` = '" . $shipToAddr1 . "', `shipToAddr2` = '" . $shipToAddr2 . "', `shipToAddr3` = '" . $shipToAddr3 . "', `shipToCity` = '" . $shipToCity . "', `shipToState` = '" . $shipToState . "', `shipToZip` = '" . $shipToZip . "', `shipToCountry` = '" . $shipToCountry . "', `Ship_Ref` = '" . $ship_ref . "', `packageLength` = '" . $packageLength . "', `packageWidth` = '" . $packageWidth . "', `packageHeight` = '" . $packageHeight . "', `packageWeight` = '" . $packageWeight . "', `packageActWeight` = '" . $packageActWeight . "', `orderId` = '" . $orderId . "', `orderNum` = '" . $orderNum . "', `cartonId` = '" . $cartonId . "', `pkgType` = '" . $pkgType . "', `codAmount` = '" . $codAmount . "', `toEmail` = '" . $toEmail . "', `shipMethod` = '" . $shipMethod . "', `landPhone` = '" . $landPhone . "', `mobilePhone` = '" . $mobilePhone . "', `alert` = '" . $alert . "'");

	}else{
		$consignee			= $lp_check['consignee'];
		$contactPhone		= $lp_check['contactPhone'];
		$contactPerson		= $lp_check['contactPerson'];
		$shipToAddr1		= $lp_check['shipToAddr1'];
		$shipToAddr2		= $lp_check['shipToAddr2'];
		$shipToAddr3		= $lp_check['shipToAddr3'];
		$shipToCity			= $lp_check['shipToCity'];
		$shipToState		= $lp_check['shipToState'];
		$shipToZip			= $lp_check['shipToZip'];
		$shipToCountry		= $lp_check['shipToCountry'];
		$ship_ref			= $lp_check['Ship_Ref'];
		$packageLength		= $lp_check['packageLength'];
		$packageWidth		= $lp_check['packageWidth'];
		$packageHeight		= $lp_check['packageHeight'];
		$packageWeight		= $lp_check['packageWeight'];
		$packageActWeight	= $lp_check['packageActWeight'];
		$orderId			= $lp_check['orderId'];
		$orderNum			= $lp_check['orderNum'];
		$cartonId			= $lp_check['cartonId'];
		$pkgType			= $lp_check['pkgType'];
		$codAmount			= $lp_check['codAmount'];
		$toEmail			= $lp_check['toEmail'];	
		$shipMethod			= $lp_check['shipMethod'];
		$landPhone			= $lp_check['landPhone'];
		$mobilePhone		= $lp_check['mobilePhone'];
		$alert				= $lp_check['alert'];
	}*/

	if ( $alert != '' && $alert != null ){
		echo 'DO NOT SHIP, RETURN TO MANAGER.<br>"' . $alert . '"';
		exit;
	}

	if ( $packageActWeight > 0 ){
		$weight = $packageActWeight;
	}else{
		$weight = $packageWeight;
	}

	$quote	= $connector->getCheapestQuotes($shipToCity, $shipToZip, $packageLength, $packageWidth, $packageHeight, $packageWeight);

	$validPhone = null;

	if( preg_match("/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/", $contactPhone) ) {
		$validPhone = $contactPhone;
	}else{
		if( preg_match("/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/", $landPhone) ) {
			$validPhone = $landPhone;
		}else{
			if( preg_match("/^[0-9]{3}-[0-9]{4}-[0-9]{4}$/", $mobilePhone) ) {
				$validPhone = $mobilePhone;
			}
		}
	}

	$return	= $connector->makeBookingRequest($shipToCity, $shipToState, $shipToZip, $shipToCountry, $packageLength, $packageWidth, $packageHeight, $packageWeight, $validPhone, $consignee, $shipToAddr1, $toEmail, $quote, $orderNum);

	if ( is_array($return) ){
		file_put_contents( 'label/' . $_POST['lp_number'] . '.pdf', base64_decode($return['labelDocument']) );

		if ( $return['consignmentNumber'] ){

			$trackingNum	= $return['consignmentNumber'];		
			$carrierName	= $quote['carrier']['companyName'];
			$shippingCost	= $quote['totalPrice'];

			$connector->query("update `" . $tableName . "` set `trackingCode` = '" . $trackingNum . "', `carrierName` = '" . $carrierName . "', `shippingCost` = '" . $shippingCost . "', `status` = '5' where `lpNum` = '" . $_POST['lp_number'] . "'");
			
			echo "SUCCESS!<br>Order " . $orderNum . "(" . $_POST['lp_number'] . ")" . " have allcated in Temando, would be update manifest record in NetSuite within a hour";

		}
	}else{
		echo "Error details : " . $return;
	}

	//file_put_contents( 'consignment.pdf', base64_decode($return['consignmentDocument']) );

}catch(Exception $e){
	echo "Caught Exception: ", $e->getMessage(), "\n";
}

?>