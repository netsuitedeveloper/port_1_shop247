<?php

require dirname(__FILE__) . '/nusoap.php';

class channeladvisorConnector {	

	

    var $usa_warehouse_arr1 = array("AK"=>"TP","AL"=>"NC","AR"=>"NC","AZ"=>"TP","CA"=>"TP","CO"=>"TP","CT"=>"NC","DE"=>"NC","FL"=>"NC","GA"=>"NC","HI"=>"TP","IA"=>"NC","ID"=>"TP","IL"=>"NC","IN"=>"NC","KS"=>"NC","KY"=>"NC",
                                   "LA"=>"NC","MA"=>"NC","MD"=>"NC","ME"=>"NC","MI"=>"NC","MN"=>"NC","MO"=>"NC","MS"=>"NC","MT"=>"TP","NC"=>"NC","ND"=>"TP","NE"=>"NC","NH"=>"NC","NJ"=>"NC","NM"=>"TP","NV"=>"TP","NY"=>"NC",
                                   "OH"=>"NC","OK"=>"NC","OR"=>"TP","PA"=>"NC","RI"=>"NC","SC"=>"NC","SD"=>"TP","TN"=>"NC","TX"=>"NC","UT"=>"TP","VA"=>"NC","VT"=>"NC","WA"=>"TP","WI"=>"NC","WV"=>"NC","WY"=>"TP","DC"=>"NC");
                                   
    var $usa_warehouse_arr2 = array("alaska"=>"TP","alabama"=>"NC","arkansas"=>"NC","arizona"=>"TP","california"=>"TP","colorado"=>"TP","connecticut"=>"NC","delaware"=>"NC","florida"=>"NC","georgia"=>"NC","hawaii"=>"TP","towa"=>"NC","tdaho"=>"TP",
                                   "illinois"=>"NC","indiana"=>"NC","kansas"=>"NC","kentucky"=>"NC","louisiana"=>"NC","massachusetts"=>"NC","maryland"=>"NC","maine"=>"NC","michigan"=>"NC","minnesota"=>"NC","missouri"=>"NC","mississippi"=>"NC",
                                   "montana"=>"TP","northcarolina"=>"NC","northdakota"=>"TP","nebraska"=>"NC","newhampshire"=>"NC","newjersey"=>"NC","newmexico"=>"TP","nevada"=>"TP","newyork"=>"NC","ohio"=>"NC","oklahoma"=>"NC","oregon"=>"TP",
                                   "pennsylvania"=>"NC","rhodeisland"=>"NC","southcarolina"=>"NC","southdakota"=>"TP","tennessee"=>"NC","texas"=>"NC","utah"=>"TP","virginia"=>"NC","vermont"=>"NC","washington"=>"TP","wisconsin"=>"NC",
                                   "westvirginia"=>"NC","wyoming"=>"TP","districtofcolumbia"=>"NC");
    
    var $country_code_arr = array("AF"=>"_afghanistan","AL"=>"_albania","DZ"=>"_algeria","AS"=>"_americanSamoa","AD"=>"_andorra","AO"=>"_angola","AI"=>"_anguilla","AQ"=>"_antarctica",
                                  "AG"=>"_antiguaAndBarbuda","AR"=>"_argentina","AM"=>"_armenia","AW"=>"_aruba","AU"=>"_australia","AT"=>"_austria","AZ"=>"_azerbaijan","BS"=>"_bahamas",
                                  "BH"=>"_bahrain","BD"=>"_bangladesh","BB"=>"_barbados","BY"=>"_belarus","BE"=>"_belgium","BZ"=>"_belize","BJ"=>"_benin","BM"=>"_bermuda","BT"=>"_bhutan",
                                  "BO"=>"_bolivia","BA"=>"_bosniaAndHerzegovina","BW"=>"_botswana","BV"=>"_bouvetIsland","BR"=>"_brazil","IO"=>"_britishIndianOceanTerritory",
                                  "BN"=>"_bruneiDarussalam","BG"=>"_bulgaria","BF"=>"_burkinaFaso","BI"=>"_burundi","KH"=>"_cambodia","CM"=>"_cameroon","CA"=>"_canada","CV"=>"_capVerde",
                                  "KY"=>"_caymanIslands","CF"=>"_centralAfricanRepublic","TD"=>"_chad","CL"=>"_chile","CN"=>"_chinav","CX"=>"_christmasIsland","CC"=>"_cocosKeelingIslands",
                                  "CO"=>"_colombia","KM"=>"_comoros","CD"=>"_congoDemocraticPeoplesRepublic","CG"=>"_congoRepublicOf","CK"=>"_cookIslands","CR"=>"_costaRica","CI"=>"_coteDIvoire",
                                  "HR"=>"_croatiaHrvatska","CU"=>"_cuba","CY"=>"_cyprus","CZ"=>"_czechRepublic","DK"=>"_denmark","DJ"=>"_djibouti","DM"=>"_dominica","DO"=>"_dominicanRepublic",
                                  "TP"=>"_eastTimor","EC"=>"_ecuador","EG"=>"_egypt","SV"=>"_elSalvador","GQ"=>"_equatorialGuinea","ER"=>"_eritrea","EE"=>"_estonia","ET"=>"_ethiopia",
                                  "FK"=>"_falklandIslandsMalvina","FO"=>"_faroeIslands","FJ"=>"_fiji","FI"=>"_finland","FR"=>"_france","GF"=>"_frenchGuiana","PF"=>"_frenchPolynesia",
                                  "TF"=>"_frenchSouthernTerritories","GA"=>"_gabon","GM"=>"_gambia","GE"=>"_georgia","DE"=>"_germany","GH"=>"_ghana","GI"=>"_gibraltar","GR"=>"_greece",
                                  "GL"=>"_greenland","GD"=>"_grenada","GP"=>"_guadeloupe","GU"=>"_guam","GT"=>"_guatemala","GG"=>"_guernsey","GN"=>"_guinea","GW"=>"_guineaBissau","GY"=>"_guyana",
                                  "HT"=>"_haiti","HM"=>"_heardAndMcDonaldIslands","VA"=>"_holySeeCityVaticanState","HN"=>"_honduras","HK"=>"_hongKong","HU"=>"_hungary","IS"=>"_iceland","IN"=>"_india",
                                  "ID"=>"_indonesia","IR"=>"_iranIslamicRepublicOf","IQ"=>"_iraq","IE"=>"_ireland","IM"=>"_isleOfMan","IL"=>"_israel","IT"=>"_italy","JM"=>"_jamaica","JP"=>"_japan",
                                  "JE"=>"_jersey","JO"=>"_jordan","KZ"=>"_kazakhstan","KE"=>"_kenya","KI"=>"_kiribati","KP"=>"_koreaDemocraticPeoplesRepublic","KR"=>"_koreaRepublicOf","KW"=>"_kuwait",
                                  "KG"=>"_kyrgyzstan","LA"=>"_laoPeoplesDemocraticRepublic","LV"=>"_latvia","LB"=>"_lebanon","LS"=>"_lesotho","LR"=>"_liberia","LY"=>"_libyanArabJamahiriya",
                                  "LI"=>"_liechtenstein","LT"=>"_lithuania","LU"=>"_luxembourg","MO"=>"_macau","MK"=>"_macedonia","MG"=>"_madagascar","MW"=>"_malawi","MY"=>"_malaysia","MV"=>"_maldives",
                                  "ML"=>"_mali","MT"=>"_malta","MH"=>"_marshallIslands","MQ"=>"_martinique","MR"=>"_mauritania","MU"=>"_mauritius","YT"=>"_mayotte","MX"=>"_mexico","FM"=>"_micronesiaFederalStateOf",
                                  "MD"=>"_moldovaRepublicOf","MC"=>"_monaco","MN"=>"_mongolia","ME"=>"_montenegro","MS"=>"_montserrat","MA"=>"_morocco","MZ"=>"_mozambique","MM"=>"_myanmar","NA"=>"_namibia",
                                  "NR"=>"_nauru","NP"=>"_nepal","NL"=>"_netherlands","AN"=>"_netherlandsAntilles","NC"=>"_newCaledonia","NZ"=>"_newZealand","NI"=>"_nicaragua","NE"=>"_niger","NG"=>"_nigeria",
                                  "NU"=>"_niue","NF"=>"_norfolkIsland","MP"=>"_northernMarianaIslands","NO"=>"_norway","OM"=>"_oman","PK"=>"_pakistan","PW"=>"_palau","PS"=>"_palestinianTerritories","PA"=>"_panama",
                                  "PG"=>"_papuaNewGuinea","PY"=>"_paraguay","PE"=>"_peru","PH"=>"_philippines","PN"=>"_pitcairnIsland","PL"=>"_poland","PT"=>"_portugal","PR"=>"_puertoRico","QA"=>"_qatar",
                                  "RE"=>"_reunionIsland","RO"=>"_romania","RU"=>"_russianFederation","RW"=>"_rwanda","KN"=>"_saintKittsAndNevis","LC"=>"_saintLucia","VC"=>"_saintVincentAndTheGrenadines",
                                  "SM"=>"_sanMarino","ST"=>"_saoTomeAndPrincipe","SA"=>"_saudiArabia","SN"=>"_senegal","CS"=>"_serbia","SC"=>"_seychelles","SL"=>"_sierraLeone","SG"=>"_singapore","SK"=>"_slovakRepublic",
                                  "SI"=>"_slovenia","SB"=>"_solomonIslands","SO"=>"_somalia","ZA"=>"_southAfrica","GS"=>"_southGeorgia","ES"=>"_spain","LK"=>"_sriLanka","SH"=>"_stHelena","PM"=>"_stPierreAndMiquelon",
                                  "SD"=>"_sudan","SR"=>"_suriname","SJ"=>"_svalbardAndJanMayenIslands","SZ"=>"_swaziland","SE"=>"_sweden","CH"=>"_switzerland","SY"=>"_syrianArabRepublic","TW"=>"_taiwan","TJ"=>"_tajikistan",
                                  "TZ"=>"_tanzania","TH"=>"_thailand","TG"=>"_togo","TK"=>"_tokelau","TO"=>"_tonga","TT"=>"_trinidadAndTobago","TN"=>"_tunisia","TR"=>"_turkey","TM"=>"_turkmenistan","TC"=>"_turksAndCaicosIslands",
                                  "TV"=>"_tuvalu","UG"=>"_uganda","UA"=>"_ukraine","AE"=>"_unitedArabEmirates","GB"=>"_unitedKingdomGB","US"=>"_unitedStates","UY"=>"_uruguay","UM"=>"_uSMinorOutlyingIslands","UZ"=>"_uzbekistan",
                                  "VU"=>"_vanuatu","VE"=>"_venezuela","VN"=>"_vietnam","VG"=>"_virginIslandsBritish","VI"=>"_virginIslandsUSA","WF"=>"_wallisAndFutunaIslands","EH"=>"_westernSahara","WS"=>"_westernSamoa",
                                  "YE"=>"_yemen","YU"=>"_yugoslavia","ZM"=>"_zambia","ZW"=>"_zimbabwe");

	

	function getOrderListFromCA($accountID, $pageNum){
		$client = new nusoap_client('https://api.channeladvisor.com/ChannelAdvisorAPI/v7/OrderService.asmx?WSDL', true );
		
		$err = $client->getError();
		if ($err)
		{
			echo 'Constructor error' . $err . '';
		}
		
		$headers = '<web:APICredentials>
					<web:DeveloperKey>'.$this->developerKey.'</web:DeveloperKey>
					<web:Password>'.$this->password.'</web:Password>
					</web:APICredentials>';
		$bodyxml = '<web:GetOrderList>
					<web:accountID>'.$accountID.'</web:accountID>
					<web:orderCriteria>
					<ord:StatusUpdateFilterBeginTimeGMT>' . date("c", strtotime("+3 hour")) . '</ord:StatusUpdateFilterBeginTimeGMT>
					<ord:DetailLevel>Complete</ord:DetailLevel>
					<ord:OrderStateFilter>Active</ord:OrderStateFilter>
					<ord:PaymentStatusFilter>Cleared</ord:PaymentStatusFilter>
					<ord:CheckoutStatusFilter>Completed</ord:CheckoutStatusFilter>
					<ord:ShippingStatusFilter>Unshipped</ord:ShippingStatusFilter>
					<ord:PageNumberFilter>' . $pageNum . '</ord:PageNumberFilter>
					</web:orderCriteria>
					</web:GetOrderList>';	

		$result = $client->call('GetOrderList', $bodyxml, false, false, $headers);
		$result_orderArray = Array();
        
        if ( is_array($result['GetOrderListResult']['ResultData']) ){
            if ( $result['GetOrderListResult']['ResultData']['OrderResponseItem']['NumberOfMatches'] ){             
                $result_orderArray[0] = $result['GetOrderListResult']['ResultData']['OrderResponseItem'];
            }else if( $result['GetOrderListResult']['ResultData']['OrderResponseItem'][0]['NumberOfMatches'] ){
                $result_orderArray = $result['GetOrderListResult']['ResultData']['OrderResponseItem'];
            }
            return $result_orderArray; 
        }else{
            return FALSE;    
        }
	}

	function getOrderListFromCATemp($accountID, $pageNum){
		$client = new nusoap_client('https://api.channeladvisor.com/ChannelAdvisorAPI/v7/OrderService.asmx?WSDL', true );
		
		$err = $client->getError();
		if ($err)
		{
			echo 'Constructor error' . $err . '';
		}
		
		$headers = '<web:APICredentials>
					<web:DeveloperKey>'.$this->developerKey.'</web:DeveloperKey>
					<web:Password>'.$this->password.'</web:Password>
					</web:APICredentials>';
		$bodyxml = '<web:GetOrderList>
					<web:accountID>'.$accountID.'</web:accountID>
					<web:orderCriteria>
					<ord:StatusUpdateFilterBeginTimeGMT>' . date("c", strtotime("-5 day")) . '</ord:StatusUpdateFilterBeginTimeGMT>
					<ord:DetailLevel>Complete</ord:DetailLevel>
					<ord:OrderStateFilter>Active</ord:OrderStateFilter>
					<ord:PaymentStatusFilter>Cleared</ord:PaymentStatusFilter>
					<ord:CheckoutStatusFilter>Completed</ord:CheckoutStatusFilter>
					<ord:ShippingStatusFilter>Unshipped</ord:ShippingStatusFilter>
					<ord:PageNumberFilter>' . $pageNum . '</ord:PageNumberFilter>
					</web:orderCriteria>
					</web:GetOrderList>';	

		$result = $client->call('GetOrderList', $bodyxml, false, false, $headers);
		$result_orderArray = Array();
        
        if ( is_array($result['GetOrderListResult']['ResultData']) ){
            if ( $result['GetOrderListResult']['ResultData']['OrderResponseItem']['NumberOfMatches'] ){             
                $result_orderArray[0] = $result['GetOrderListResult']['ResultData']['OrderResponseItem'];
            }else if( $result['GetOrderListResult']['ResultData']['OrderResponseItem'][0]['NumberOfMatches'] ){
                $result_orderArray = $result['GetOrderListResult']['ResultData']['OrderResponseItem'];
            }
            return $result_orderArray; 
        }else{
            return FALSE;    
        }
	}

	function getLowOrderListFromCA($accountID, $pageNum){
		$client = new nusoap_client('https://api.channeladvisor.com/ChannelAdvisorAPI/v7/OrderService.asmx?WSDL', true );
			
		$err = $client->getError();
		if ($err)
		{
			echo 'Constructor error' . $err . '';
		}
		
		$headers = '<web:APICredentials>
					<web:DeveloperKey>'.$this->developerKey.'</web:DeveloperKey>
					<web:Password>'.$this->password.'</web:Password>
					</web:APICredentials>';
		$bodyxml = '<web:GetOrderList>
					<web:accountID>'.$accountID.'</web:accountID>
					<web:orderCriteria>
					<ord:StatusUpdateFilterBeginTimeGMT>' . date("c", strtotime("-12 day UTC")) . '</ord:StatusUpdateFilterBeginTimeGMT>
					<ord:StatusUpdateFilterEndTimeGMT>' . date("c", strtotime("+3 hour UTC")) . '</ord:StatusUpdateFilterEndTimeGMT>
					<ord:DetailLevel>Low</ord:DetailLevel>
					<ord:OrderStateFilter>Active</ord:OrderStateFilter>
					<ord:PaymentStatusFilter>Cleared</ord:PaymentStatusFilter>
					<ord:CheckoutStatusFilter>Completed</ord:CheckoutStatusFilter>
					<ord:ShippingStatusFilter>Unshipped</ord:ShippingStatusFilter>
					<ord:PageNumberFilter>' . $pageNum . '</ord:PageNumberFilter>
					<ord:PageSize>100</ord:PageSize>
					</web:orderCriteria>
					</web:GetOrderList>';	

		$result = $client->call('GetOrderList', $bodyxml, false, false, $headers);
		
		$result_orderArray = Array();
		
		if ( is_array($result['GetOrderListResult']['ResultData']) ){
			if ( $result['GetOrderListResult']['ResultData']['OrderResponseItem']['NumberOfMatches'] ){             
				$result_orderArray[0] = $result['GetOrderListResult']['ResultData']['OrderResponseItem'];
			}else if( $result['GetOrderListResult']['ResultData']['OrderResponseItem'][0]['NumberOfMatches'] ){
				$result_orderArray = $result['GetOrderListResult']['ResultData']['OrderResponseItem'];
			}
			return $result_orderArray; 
		}else{
			return FALSE;    
		}
	}

	function array2xml($arrData, $level) {
	   $xml = '';
	   foreach( $arrData as $key => $value ) {
		   $spacer = '';
		   for ($i = 0; $i < $level; $i++) { $spacer .= "\t"; }
		   if ( is_array( $value ) ) {
			   if (preg_match("/^(.+)___\d+$/", $key, $m)) {
					   $xml .= $spacer . "<" . $m[1] . ">\n" . $this->array2xml($value, $level+1) . $spacer . "</" . $m[1] . ">\n";
			   } else {
					   $xml .= $spacer . "<" . $key . ">\n" . $this->array2xml($value, $level+1) . $spacer . "</" . $key . ">\n";
			   }
		   } else {
			   $xml .= $spacer . "<" . $key . ">" . $value . "</" . $key . ">\n";
		   }
	   }
	   return $xml;
	}

	function getMissingOrderListFromCA($accountID, $pageNum, $orderID_array){

		$client = new nusoap_client('https://api.channeladvisor.com/ChannelAdvisorAPI/v7/OrderService.asmx?WSDL', true );
				
		$err = $client->getError();
		if ($err)
		{
			echo 'Constructor error' . $err . '';
		}
		
		$headers = '<web:APICredentials>
					<web:DeveloperKey>'.$this->developerKey.'</web:DeveloperKey>
					<web:Password>'.$this->password.'</web:Password>
					</web:APICredentials>';
       
		$bodyxml = '<web:GetOrderList>
					<web:accountID>'.$accountID.'</web:accountID>
                    <web:orderCriteria>
                    <ord:DetailLevel>Complete</ord:DetailLevel>                                                              
                    <ord:ClientOrderIdentifierList>';  
		$q = 0;
		$orderCriteriaListxml = '';
		while ( $q < count($orderID_array) ){
			$orderCriteriaListxml .='
								<ord:string>' . $orderID_array[$q] . '</ord:string>';						
			$q++;
		}
		$bodyxml .= $orderCriteriaListxml . '
				</ord:ClientOrderIdentifierList> 
                <ord:PageNumberFilter>1</ord:PageNumberFilter>
                </web:orderCriteria>
                </web:GetOrderList>';
        
		$result = $client->call('GetOrderList', $bodyxml, false, false, $headers);
        
		$result_orderArray = Array();
		
		if ( $result['GetOrderListResult']['ResultData'] != NULL ){
			if ( $result['GetOrderListResult']['ResultData']['OrderResponseItem']['NumberOfMatches'] != NULL ){             
				$result_orderArray[0] = $result['GetOrderListResult']['ResultData']['OrderResponseItem'];
			}else if( $result['GetOrderListResult']['ResultData']['OrderResponseItem'][0]['NumberOfMatches'] != NULL ){
				$result_orderArray = $result['GetOrderListResult']['ResultData']['OrderResponseItem'];
			}
			return $result_orderArray; 
		}else{
			return FALSE;    
		}
		
	
	}

	function customerDepositModule( $soId, $cuId, $amounts, $location, $currency, $memo, $salesChannel, $paymentMethod, $paymentid){
        
        $customerDepositRecord = new CustomerDeposit();
		$customerDepositRecord->customForm = new RecordRef();
        $customerDepositRecord->customForm->internalId = '67';
        $customerDepositRecord->customer = new RecordRef();
        $customerDepositRecord->customer->internalId = $cuId;
		$customerDepositRecord->salesOrder = new RecordRef();
        $customerDepositRecord->salesOrder->internalId = $soId;
        $customerDepositRecord->payment = $amounts;
        $customerDepositRecord->status = 'Deposited';
        $customerDepositRecord->location = new RecordRef();
		$customerDepositRecord->location->internalId = $location;
		$customerDepositRecord->paymentMethod = new RecordRef();
		if ( $salesChannel == '18' ){
			$customerDepositRecord->paymentMethod->internalId = $this->paymentMethodUSMatching($paymentMethod);
		}else if ( $salesChannel == '17' ){
			$customerDepositRecord->paymentMethod->internalId = $this->paymentMethodAUMatching($paymentMethod);
		}else{
			$customerDepositRecord->paymentMethod->internalId = $this->paymentMethodMatching($paymentMethod);
		}	
		$customerDepositRecord->class = new RecordRef();
		$customerDepositRecord->class->internalId = $salesChannel;
		$customerDepositRecord->memo = $memo;
		$customerDepositRecord->checkNum = $paymentid;

		return $customerDepositRecord;

    }

	function paymentMethodMatching($paymentMethod){
		switch ($paymentMethod) {
			case 'PayPal':
				$internalID = 7;
				break;
			case 'Pixmania':
				$internalID = 13;
				break;
			case 'Sears':
				$internalID = 14;
				break;
			case 'Buy.com':
				$internalID = 15;
				break;
			case 'Newegg':
				$internalID = 16;
				break;
		}
		return $internalID;
	}

	function paymentMethodUSMatching($paymentMethod){
		switch ($paymentMethod) {
			case 'PayPal':
				$internalID = 41;
				break;
			case 'Pixmania':
				$internalID = 13;
				break;
			case 'Sears':
				$internalID = 14;
				break;
			case 'Buy.com':
				$internalID = 15;
				break;
			case 'Newegg':
				$internalID = 16;
				break;
		}
		return $internalID;
	}

	function paymentMethodAUMatching($paymentMethod){
		switch ($paymentMethod) {
			case 'PayPal':
				$internalID = 42;
				break;
		}
		return $internalID;
	}
	
	function itemStatusMatch($location_id){
		switch ($location_id) {
			case 1:
				$return_val = 3;
				break;
			case 2:
				$return_val = 1;
				break;
			case 3:
				$return_val = 304;
				break;
			case 4:
				$return_val = 305;
				break;
		}
		return $return_val;
	}

	function sendMailWithAlert($emailAddr, $orderNumber, $message, $account){
		
		if (strtoupper(substr(PHP_OS,0,3)=='WIN')) {
			$eol="\r\n";
		} elseif (strtoupper(substr(PHP_OS,0,3)=='MAC')) {
			$eol="\r";
		} else {
			$eol="\n";
		}

		$to      = $emailAddr;
		$subject = 'Failed Order to import into NetSuite. ChannelAdvisor Order ID: # ' . $orderNumber;
		$message = 'It is occured problem to import sales order from ChannelAdvisor' . $account . ' into NetSuite, so that failed to import.' . $eol . $eol . $message . $eol . 'please fix it.' . $eol . $eol . 'CA order id:' . $orderNumber;
		$headers = 'from amazon-netsuite integration script' . $eol .
		'Reply-To: No Reply' . $eol .
		'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);
	}

	function addCustomerErrorAlert($emailAddr, $orderNumber, $message, $account){
		
		if (strtoupper(substr(PHP_OS,0,3)=='WIN')) {
			$eol="\r\n";
		} elseif (strtoupper(substr(PHP_OS,0,3)=='MAC')) {
			$eol="\r";
		} else {
			$eol="\n";
		}

		$to      = $emailAddr;
		$subject = 'Error Add Customer ChanAd Order ID: #' . $orderNumber;
		$message = 'It is occured problem to import sales order from ChannelAdvisor' . $account . ' into NetSuite, so that failed to import.' . $eol . $eol . $message . $eol . 'please fix it.' . $eol . $eol . 'CA order id:' . $orderNumber;
		$headers = 'from amazon-netsuite integration script' . $eol .
		'Reply-To: No Reply' . $eol .
		'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);
	}

	function updateCustomerErrorAlert($emailAddr, $orderNumber, $message, $account){
		
		if (strtoupper(substr(PHP_OS,0,3)=='WIN')) {
			$eol="\r\n";
		} elseif (strtoupper(substr(PHP_OS,0,3)=='MAC')) {
			$eol="\r";
		} else {
			$eol="\n";
		}

		$to      = $emailAddr;
		$subject = 'Error Update Customer ChanAd Order ID: # ' . $orderNumber;
		$message = 'It is occured problem to import sales order from ChannelAdvisor' . $account . ' into NetSuite, so that failed to import.' . $eol . $eol . $message . $eol . 'please fix it.' . $eol . $eol . 'CA order id:' . $orderNumber;
		$headers = 'from amazon-netsuite integration script' . $eol .
		'Reply-To: No Reply' . $eol .
		'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);
	}
    
    function arrayToObject($array) {
        if (!is_array($array)) {
            return $array;
        }
        
        $object = new stdClass();
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $name=>$value) {
                $name = trim($name);
                if (strlen($name) > 0) {
                    if (!is_array($value)){
                        $object->$name = $this->arrayToObject(utf8_encode($value));
                    }else{
                        $object->$name = $this->arrayToObject($value);
                    }                
                }
            }
            return $object; 
        }
        else {
            return FALSE;
        }
    }

	
}


?>