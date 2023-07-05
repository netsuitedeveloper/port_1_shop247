<?php
//          Create Date      2014/05/11
//          FileName		 function.php
//          Developer		 Hakuna Moni
//          E-Mail			 hakunamoni@gmail.com

class pixmaniaConnector {

	function __construct() {

		include dirname(__FILE__)."/dom.php";
    
	}
	
	function getPixOrders(){

		$ci = curl_init();

		curl_setopt( $ci, CURLOPT_URL, 'https://pixplace-ws.pixmania.com/index.php?d=webServices_Server&c=ServerRest&rm=exportFile&rf=exportOrdersToDeliver&sl=' . $this->pixSerialKey . '&site_id=1' ); 

		curl_setopt( $ci, CURLOPT_RETURNTRANSFER, 1); 

		curl_setopt( $ci, CURLOPT_USERPWD, $this->pixLogin.':'.$this->pixPassword ); 
		
		curl_setopt( $ci, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
		
		curl_setopt( $ci, CURLOPT_UNRESTRICTED_AUTH, true); 
		
		curl_setopt( $ci, CURLOPT_SSL_VERIFYHOST, 0); 
		
		curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, false); 
		
		curl_setopt( $ci, CURLOPT_HTTPHEADER, array("Expect:") );

		$response = curl_exec($ci);

		if ( $response ){
			$orderLines = explode("\n", $response);

			$orderData = array();
			
			foreach ($orderLines as $line) {
			
				$orderData[] = str_getcsv($line, "\t");
			
			}
			
			return $orderData;
		
		}else{
		
			return false;
		
		}	

	}

	function getLastOrderFile($cookie){
		
		//  loginPixmania

		//  1. Autorization - get cookie 
		
		if (filesize($cookie) == 0) {
			
			$ci     = curl_init();
			
			curl_setopt($ci, CURLOPT_URL, "https://bo-pixplace.pixmania.com/index.php/access/check/");
		   
			curl_setopt($ci, CURLOPT_HEADER, 1);
			
			curl_setopt($ci, CURLOPT_COOKIEJAR, $cookie);
			
			curl_setopt($ci, CURLOPT_POST, true);

			curl_setopt($ci, CURLOPT_PORT, 443);

			curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		   
			curl_setopt($ci, CURLOPT_POSTFIELDS, "email=" . $this->pixLogin . "&password=" . $this->pixPassword);
			
			curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
			
			curl_setopt($ci, CURLOPT_FRESH_CONNECT, 1);
			
			curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1145.0 Safari/537.1");
			
			$result = curl_exec($ci);
			
			curl_close ($ci);
		}

		// get file id 

		$ci     = curl_init();
		
		curl_setopt($ci, CURLOPT_URL, "https://bo-pixplace.pixmania.com/index.php/orderManagement/pastOrdersToShip");
		
		curl_setopt($ci, CURLOPT_COOKIEFILE, $cookie);

		curl_setopt($ci, CURLOPT_PORT, 443);

		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1145.0 Safari/537.1");
		
		$result = curl_exec($ci);
		
		curl_close ($ci);

		$html  = str_get_html($result);
		
		$file_id = preg_replace("/\D+/", "", $html->find("table.classical a.pinkButton", 0)->href);

		// get file

		$ci     = curl_init();
		
		curl_setopt($ci, CURLOPT_URL, "https://bo-pixplace.pixmania.com/index.php/orderManagement/download/shipping/" . $file_id );
		
		curl_setopt($ci, CURLOPT_COOKIEFILE, $cookie);

		curl_setopt($ci, CURLOPT_PORT, 443);

		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1145.0 Safari/537.1");
		
		$response = curl_exec($ci);
		
		curl_close ($ci);

		$orderLines = explode("\n", $response);

		$orderData = array();
		
		foreach ($orderLines as $line) {
		
			$orderData[] = str_getcsv($line, "\t");
		
		}
		
		return $orderData;
		
	}

	function test($cookie){

		if (filesize($cookie) == 0) {
			
			$ci     = curl_init();
			
			curl_setopt($ci, CURLOPT_URL, "https://bo-pixplace.pixmania.com/index.php/access/check/");
		   
			curl_setopt($ci, CURLOPT_HEADER, 1);
			
			curl_setopt($ci, CURLOPT_COOKIEJAR, $cookie);
			
			curl_setopt($ci, CURLOPT_POST, true);

			curl_setopt($ci, CURLOPT_PORT, 443);

			curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		   
			curl_setopt($ci, CURLOPT_POSTFIELDS, "email=" . $this->pixLogin . "&password=" . $this->pixPassword);
			
			curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
			
			curl_setopt($ci, CURLOPT_FRESH_CONNECT, 1);
			
			curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1145.0 Safari/537.1");
			
			$result = curl_exec($ci);
			
			curl_close ($ci);
		}

		// get file

		$ci     = curl_init();
		
		curl_setopt($ci, CURLOPT_URL, "https://bo-pixplace.pixmania.com/index.php/orderManagement/download/shipping/96807071" );
		
		curl_setopt($ci, CURLOPT_COOKIEFILE, $cookie);

		curl_setopt($ci, CURLOPT_PORT, 443);

		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1145.0 Safari/537.1");
		
		$response = curl_exec($ci);
		
		curl_close ($ci);

		$orderLines = explode("\n", $response);

		$orderData = array();
		
		foreach ($orderLines as $line) {
		
			$orderData[] = str_getcsv($line, "\t");
		
		}
		
		return $orderData;
	}

	function makeCSVFile(){
		
	}

	function postSubmitFile(){

		//$path = realpath('confirm.csv');
		
		$param = array ( 'rm' => 'importFileContr' 
						, 'rf' => 'importDeliveredOrders' 
						, 'sl' => $this->pixSerialKey 
						, 'FILENAME' => '@D:\\Work\\importOrder\\pixmania\\import\\confirm.csv' );

		$ci = curl_init();		
		 
		curl_setopt( $ci, CURLOPT_URL, 'https://pixplace-ws.pixmania.com/index.php?d=webServices_Server&c=ServerRest&FILE_VERSION=2' ); 
		 
		curl_setopt( $ci, CURLOPT_RETURNTRANSFER, 1); 
		
		curl_setopt( $ci, CURLOPT_USERPWD, $this->pixLogin.':'.$this->pixPassword ); 
		
		curl_setopt( $ci, CURLOPT_HTTPAUTH, CURLAUTH_ANY); 
		
		curl_setopt( $ci, CURLOPT_UNRESTRICTED_AUTH, true); 
		
		curl_setopt( $ci, CURLOPT_SSL_VERIFYHOST, 0); 
		
		curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, false); 
		
		curl_setopt( $ci, CURLOPT_POST, 1); 
		
		curl_setopt( $ci, CURLOPT_HTTPHEADER, array("Expect:") ); 
		
		curl_setopt( $ci, CURLOPT_POSTFIELDS, $param ); 
		
		$response = curl_exec( $ci );

		curl_close ($ci);

		return $response;
		
	}

	function getCountryCode($country){
		
		switch ( $country ){
		
			case 'SPAIN':
				
				$key = 'ES';

				break;

			case 'FRANCE':
				
				$key = 'FR';

				break;

			case 'PORTUGAL':
				
				$key = 'PT';

				break;
			
			case 'BELGIUM':
				
				$key = 'BE';

				break;
			
			case 'GERMANY':
				
				$key = 'DE';

				break;
			
			case 'ITALY':
				
				$key = 'IT';

				break;
			
			case 'UNITED-KINGDOM':
				
				$key = 'UK';

				break;
			
			default:
				$key = $country;
		}

		return $key;

	}

	// reference : https://system.na1.netsuite.com/help/helpcenter/en_US/Output/Help/AccountSetup/DataManagement/CountryNamesCSVImport.html?NS_VER=2014.1.0

}
?>