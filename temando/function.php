<?php

/*	
* -----------------------------------------------------------------
*	File			function.php
*	Version			1.0
*	Create Date		2014/10/15
*	Created By		Hakuna Moni (hakunamoni@gmail.com)
* -----------------------------------------------------------------
*/

/*
*	temando connector class
*/
class temandoconnector {
	
	

	function __construct() {
		$this->service	= new TemandoWebServices();
    }

	private function service(){
	}

	function getCheapestQuotes($shipToCity, $shipToZip, $packageLength, $packageWidth, $packageHeight, $packageActWeight){

		$request = array ( 'anythings' => array ( 'anything' => array ( 0 => array ('class' => 'General Goods',
																			'subclass' => 'Household Goods',
																			'packaging' => 'Parcel',
																			'qualifierFreightGeneralFragile' => 'N',
																			'weight' => (float)$packageActWeight,
																			'length' => (float)$packageLength * 100,
																			'width' => (float)$packageWidth * 100,
																			'height' => (float)$packageHeight * 100,
																			'distanceMeasurementType' => 'Centimetres',
																			'weightMeasurementType' => 'Kilograms',
																			'quantity' => '1', ), ), ),
					'anywhere' => array ( 	'itemNature' => 'Domestic',
											'itemMethod' => 'Door to Door',
											'originDescription' => $this->originDescription,
											'originCountry' => 'AU',
											'originCode' => $this->originCode,
											'originSuburb' => $this->originSuburb, 
											'originIs' => 'Business', 
											'originBusDock' => 'N',
											'originBusUnattended' => 'N', 
											'originBusForklift' => 'N', 
											'originBusLoadingFacilities' => 'N', 
											'originBusInside' => 'N', 
											'originBusNotifyBefore' => 'N',
											'originBusLimitedAccess' => 'N', 
											'originBusHeavyLift' => 'N', 
											'originBusContainerSwingLifter' => 'N', 
											'originBusTailgateLifter' => 'N', 
											'destinationCountry' => 'AU', 
											'destinationCode' => $shipToZip, 
											'destinationSuburb' => $shipToCity, 
											'destinationIs' => 'Residence', 
											'destinationBusDock' => 'N', 
											'destinationBusPostalBox' => 'N', 
											'destinationBusUnattended' => 'N', 
											'destinationBusForklift' => 'N', 
											'destinationBusLoadingFacilities' => 'N', 
											'destinationBusInside' => 'N', 
											'destinationBusNotifyBefore' => 'N', 
											'destinationBusLimitedAccess' => 'N', 
											'destinationBusHeavyLift' => 'N', 
											'destinationBusContainerSwingLifter' => 'N',
											'destinationBusTailgateLifter' => 'N', ),
					'anytime' => null,
					'clientId' => $this->temando_client_id,
					'general' => null);
		
		$result = $this->service->getQuotesByRequest($request, $this->temando_user, $this->temando_pass, $this->temando_wsdl);

		$index = 0;
		$min_cost_index = 0;

		for ( $i = 0; $i < count($result['quote']); $i++ ){
			if ( $i == 0 ){
				$min_cost = $result['quote'][$i]['totalPrice'];				
			}
			if ( $result['quote'][$i]['totalPrice'] < $min_cost ){
				$min_cost = $result['quote'][$i]['totalPrice'];
				$min_cost_index = $i;
			}
		}

		return $result['quote'][$min_cost_index];
	}

	function makeBookingRequest($shipToCity, $shipToState, $shipToZip, $shipToCountry, $packageLength, $packageWidth, $packageHeight, $packageActWeight, $contactPhone, $contactPerson, $shipToAddr1, $toEmail, $quote, $order_number){

		if ( !$contactPhone ){
			$contactPhone = '11111111';
		}

		$request = array ( 'anythings' => array ( 'anything' => array ( 0 => array ('class' => 'General Goods',
																			'subclass' => 'Household Goods',
																			'packaging' => 'Parcel',
																			'qualifierFreightGeneralFragile' => 'N',
																			'weight' => (float)$packageActWeight,
																			'length' => (float)$packageLength * 100,
																			'width' => (float)$packageWidth * 100,
																			'height' => (float)$packageHeight * 100,
																			'distanceMeasurementType' => 'Centimetres',
																			'weightMeasurementType' => 'Kilograms',
																			'quantity' => '1', ), ), ),
					'anywhere' => array ( 	'itemNature' => 'Domestic',
											'itemMethod' => 'Door to Door',
											'originDescription' => $this->originDescription,
											'originCountry' => 'AU',
											'originCode' => $this->originCode,
											'originSuburb' => $this->originSuburb, 
											'originIs' => 'Business', 
											'originBusDock' => 'N', 
											'originBusUnattended' => 'N', 
											'originBusForklift' => 'N', 
											'originBusLoadingFacilities' => 'N', 
											'originBusInside' => 'N', 
											'originBusNotifyBefore' => 'N',
											'originBusLimitedAccess' => 'N', 
											'originBusHeavyLift' => 'N', 
											'originBusContainerSwingLifter' => 'N', 
											'originBusTailgateLifter' => 'N', 
											'destinationCountry' => 'AU', 
											'destinationCode' => $shipToZip, 
											'destinationSuburb' => $shipToCity, 
											'destinationIs' => 'Residence', 
											'destinationBusDock' => 'N', 
											'destinationBusPostalBox' => 'N', 
											'destinationBusUnattended' => 'N', 
											'destinationBusForklift' => 'N', 
											'destinationBusLoadingFacilities' => 'N', 
											'destinationBusInside' => 'N', 
											'destinationBusNotifyBefore' => 'N', 
											'destinationBusLimitedAccess' => 'N', 
											'destinationBusHeavyLift' => 'N', 
											'destinationBusContainerSwingLifter' => 'N',
											'destinationBusTailgateLifter' => 'N', ),
					'anytime' => null,					
					'general' => null,
					'origin'  => array ( 	'contactName' => $this->originContactName,
											'companyName' => $this->originCompanyName,
											'street' => $this->originStreet,
											'suburb' => $this->originSuburb, 
											'state' => $this->originState, 
											'code' => $this->originCode, 
											'country' => $this->originCountry, 
											'phone1' => $this->originPhone, 
											'fax' => $this->originFax, 
											'email' => $this->originEmail, ),
					'destination' => array (	'contactName' => $contactPerson,
												'companyName' => $contactPerson,
												'street' => $shipToAddr1,
												'suburb' => $shipToCity, 
												'state' => $shipToState, 
												'code' => $shipToZip, 
												'country' => $shipToCountry,
												'phone1' => $contactPhone,
												'email' => $toEmail, ),
					'quote'		=> array (	'totalPrice' => $quote['totalPrice'],
											'basePrice' => $quote['basePrice'],
											'tax' => $quote['tax'],
											'currency' => $quote['currency'], 
											'deliveryMethod' => $quote['deliveryMethod'], 
											'etaFrom' => $quote['etaFrom'], 
											'etaTo' => $quote['etaTo'], 
											'guaranteedEta' => $quote['guaranteedEta'],
											'carrierId' => $quote['carrier']['id'],
											'extras' => array ( 'extra' => $quote['extras']['extra']),	),
					'payment'	=> array ( 'paymentType' => 'Credit'),
					'clientId' => $this->temando_client_id,
					'reference' => $order_number,
					'labelPrinterType' => 'Standard'); //Thermal

		$result = $this->service->makeBookingByRequest($request, $this->temando_user, $this->temando_pass, $this->temando_wsdl);

		return $result;
	}

	function cancelBRequest( $requestId ){
		$request = array ( 'requestId' => $requestId );
		$result = $this->service->cancelBookingRequest($request, $this->temando_user, $this->temando_pass, $this->temando_wsdl);

		return $result;
	}

	function confirm_manifests(){
		$today = date('Y-m-d');
		$request = array('startReadyDate' => '2014-11-10',
						 //'endReadyDate' => '2014-11-10',
						 //'startReadyDate' => '2014-11-10',
						 //'startReadyDate' => '2014-11-10',
						 //'startReadyDate' => '2014-11-10',
						 'endReadyDate' => '2014-11-13');
		$result = $this->service->confirmManifest($request, $this->temando_user, $this->temando_pass, $this->temando_wsdl);

		return $result;
	}

	function get_manifests(){
		$today = date('Y-m-d');
		$request = array('type' => 'Confirmed',
						 'readyDate' => '2014-11-12');
						 //'startReadyDate' => '2014-11-10',
						 //'startReadyDate' => '2014-11-10',
						 //'startReadyDate' => '2014-11-10',
						 //'endReadyDate' => '2014-11-13');
		$result = $this->service->getManifest($request, $this->temando_user, $this->temando_pass, $this->temando_wsdl);

		return $result;
	}

	function get_request($reference_num){
		//$request = array('reference' => $reference_num);
		$request = array('requestId' => '15388231',
						 'detail' => 'Detailed');
		$result = $this->service->getRequest($request, $this->temando_user, $this->temando_pass, $this->temando_wsdl);

		return $result;
	}
}

class TemandoWebServices {
	
	public $client;
	
	function getQuotesByRequest($requestParameter, $uname, $password, $wsdl){ 
		ini_set("soap.wsdl_cache_enabled", "0");
		try{
			$this->client = new SoapClient($wsdl, array("features" => SOAP_SINGLE_ELEMENT_ARRAYS));
			$this->buildSoapHeader($uname, $password);
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
		try{
			$response = $this->client->__soapCall('getQuotesByRequest', array($requestParameter));
				if( !isset($response) && empty($response) ){
				throw new exception("Unable to Connect to the Temando Services. For Further Details Contact Admin");
			} else {
				return obj2array($response);
			}
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
	}
	
	function makeBookingByRequest($requestParameter, $uname, $password,$wsdl){ 
		ini_set("soap.wsdl_cache_enabled", "0");		
		try{
			$this->client = new SoapClient($wsdl);	
			$this->buildSoapHeader($uname, $password);
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
		try{
			$response = $this->client->__soapCall('makeBookingByRequest', array($requestParameter));
			if( !isset($response) && empty($response) ){
				throw new exception("Unable to Connect to the Temando Services. For Further Details Contact Admin");
			} else {
				return obj2array($response);
			}
		}catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
	}

	function confirmManifest($requestParameter, $uname, $password, $wsdl){ 
		ini_set("soap.wsdl_cache_enabled", "0");		
		try{
			$this->client = new SoapClient($wsdl);	
			$this->buildSoapHeader($uname, $password);
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
		try{
			$response = $this->client->__soapCall('confirmManifest', array($requestParameter));
			if( !isset($response) && empty($response) ){
				throw new exception("Unable to Connect to the Temando Services. For Further Details Contact Admin");
			} else {
				return obj2array($response);
			}
		}catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
	}

	function getManifest($requestParameter, $uname, $password, $wsdl){ 
		ini_set("soap.wsdl_cache_enabled", "0");		
		try{
			$this->client = new SoapClient($wsdl);	
			$this->buildSoapHeader($uname, $password);
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
		try{
			$response = $this->client->__soapCall('getManifest', array($requestParameter));
			if( !isset($response) && empty($response) ){
				throw new exception("Unable to Connect to the Temando Services. For Further Details Contact Admin");
			} else {
				return obj2array($response);
			}
		}catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
	}

	function getRequest($requestParameter, $uname, $password, $wsdl){ 
		ini_set("soap.wsdl_cache_enabled", "0");		
		try{
			$this->client = new SoapClient($wsdl);	
			$this->buildSoapHeader($uname, $password);
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
		try{
			$response = $this->client->__soapCall('getRequest', array($requestParameter));
			if( !isset($response) && empty($response) ){
				throw new exception("Unable to Connect to the Temando Services. For Further Details Contact Admin");
			} else {
				return obj2array($response);
			}
		}catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
	}

	function cancelBookingRequest($requestParameter, $uname, $password, $wsdl){ 
		ini_set("soap.wsdl_cache_enabled", "0");		
		try{
			$this->client = new SoapClient($wsdl);	
			$this->buildSoapHeader($uname, $password);
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
		try{
			$response = $this->client->__soapCall('cancelRequest', array($requestParameter));
				print_r($this->client);
			if( !isset($response) && empty($response) ){
				throw new exception("Unable to Connect to the Temando Services. For Further Details Contact Admin");
			} else {
				return obj2array($response);
			}
		}catch( exception $e){
				print_r($this->client);
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
	}
	
	function createClient($requestParameter, $uname, $password, $wsdl){ 
		ini_set("soap.wsdl_cache_enabled", "0");		
		try{
			$this->client = new SoapClient($wsdl);	
			$this->buildSoapHeader($uname, $password);
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
		try{
			$response = $this->client->__soapCall('createClient', array($requestParameter));
			if( !isset($response) && empty($response) ){
				throw new exception("Unable to Connect to the Temando Services. For Further Details Contact Admin");
			} else {
				return obj2array($response);
			}
		}catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
	}
	
	function getClient($requestParameter, $uname, $password, $wsdl){ 
		ini_set("soap.wsdl_cache_enabled", "0");		
		try{
			$this->client = new SoapClient($wsdl);	
			$this->buildSoapHeader($uname, $password);
		} catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
		try{
			$response = $this->client->__soapCall('getClient', array($requestParameter));
			if( !isset($response) && empty($response) ){
				throw new exception("Unable to Connect to the Temando Services. For Further Details Contact Admin");
			} else {
				return obj2array($response);
			}
		}catch( exception $e){
			$error_msg = $e->getmessage();
			return obj2array($error_msg);
		}
	}
	
	function buildSoapHeader($uname, $password){   
		$kaeSoapHeader_NameSpace = "http://schemas.xmlsoap.org/ws/2003/06/secext"; 
		$kaeUsername = new SoapVar($uname, XSD_STRING, null, null, 'Username', $kaeSoapHeader_NameSpace);
		$kaePassword = new SoapVar($password, XSD_STRING, null, null, 'Password', $kaeSoapHeader_NameSpace); 
		$kaeUPCombo = new UPCombo($kaeUsername, $kaePassword); 
		$kaeUsernameToken = new UPToken($kaeUPCombo); 
		$kaeHdr = new SoapVar($kaeUsernameToken, SOAP_ENC_OBJECT, null, null, 'UsernameToken', $kaeSoapHeader_NameSpace); 
		$kaeSoapHeader_Name = "Security"; 
		$kaeSoapHeader_Data = $kaeHdr; 
		$kaeSoapHeader_MustUnderstand = false; 
		$kaeSoapHeader = new SoapHeader($kaeSoapHeader_NameSpace, $kaeSoapHeader_Name, $kaeSoapHeader_Data, $kaeSoapHeader_MustUnderstand); 
		$this->client->__setSoapHeaders(array($kaeSoapHeader));
	}
	
	function wdays($strTime = NULL){
		$arrWeekend = array("6","7"); // saturday and sunday
		$strTime = (is_null($strTime)) ? strtotime("+10 days") : $strTime;
		if(in_array(date("N", $strTime), $arrWeekend)){
			return $this->wdays(strtotime("+1 day", $strTime));
		} else {
			return $strTime;
		}
	
	}
		
}

class UPCombo { 
	public function __construct($NewUsername, $NewPassword){ 
		$this->Username = $NewUsername; 
		$this->Password = $NewPassword; 
	} 
} 

class UPToken { 
	private $UsernameToken;
	public function __construct($NewUPCombo){ 
		$this->UsernameToken = $NewUPCombo; 
	}
}


function obj2array($obj) {
  $out = array();
  if(!is_object($obj) && !is_array($obj)){ return $obj; }
  foreach ($obj as $key => $val) {
    switch(true) {
        case is_object($val):
         $out[$key] = obj2array($val);
         break;
      case is_array($val):
         $out[$key] = obj2array($val);
         break;
      default:
        $out[$key] = $val;
    }
  }
  return $out;
}