<?php

/*	
* -----------------------------------------------------------------
*	File			function_az.php
*	Version			1.0
*	Update Date		2014/08/08
*	Created By		hakunamoni (Skype)
* -----------------------------------------------------------------
*/

/*
*	amazon connector class
*/
class amazonconnector {

/*
*	define variables
*/


	
/*
* -------------------------------------------------------------------------------------------------------------
*	get account infos from account No. function
*
*	UK first  Account	1
*	UK Second Account	2
*	DE Account			3
*	FR Account			4
*	ES Account			5
*	IT Account			6
*	US Account			7
* -------------------------------------------------------------------------------------------------------------	
*/


	/*
	*	get seller id from account No. function
	*
	*/
	function getSellerID($account){
		switch ( $account ) {
			case 0:
				$sellerID	= $this->uk1_seller_id;
				break;
			case 1:
				$sellerID	= $this->uk2_seller_id;
				break;
			case 2:
				$sellerID	= $this->de_seller_id;
				break;
			case 3:
				$sellerID	= $this->fr_seller_id;
				break;
			case 4:
				$sellerID	= $this->es_seller_id;
				break;
			case 5:
				$sellerID	= $this->it_seller_id;
				break;
			case 6:
				$sellerID	= $this->us_seller_id;
				break;
		}
		return $sellerID;
	}

	/*
	*	get aws key from account No. function
	*
	*/
	function getAWSKey($account){
		switch ( $account ) {
			case 0:
				$aws		= $this->uk1_aws;
				break;
			case 1:
				$aws		= $this->uk2_aws;
				break;
			case 2:
				$aws		= $this->de_aws;
				break;
			case 3:
				$aws		= $this->fr_aws;
				break;
			case 4:
				$aws		= $this->es_aws;
				break;
			case 5:
				$aws		= $this->it_aws;
				break;
			case 6:
				$aws		= $this->us_aws;
				break;
		}
		return $aws;
	}

	/*
	*	get security key from account No. function
	*
	*/
	function getSecurityKey($account){
		switch ( $account ) {
			case 0:
				$secret		= $this->uk1_secret;
				break;
			case 1:
				$secret		= $this->uk2_secret;
				break;
			case 2:
				$secret		= $this->de_secret;
				break;
			case 3:
				$secret		= $this->fr_secret;
				break;
			case 4:
				$secret		= $this->es_secret;
				break;
			case 5:
				$secret		= $this->it_secret;
				break;
			case 6:
				$secret		= $this->us_secret;
				break;
		}
		return $secret;
	}

	/*
	*	get host url from account No. function
	*
	*/
	function getHostUrl($account){
		switch ( $account ) {
			case 0:
				$host		= 'mws.amazonservices.co.uk';
				break;
			case 1:
				$host		= 'mws.amazonservices.co.uk';
				break;
			case 2:
				$host		= 'mws.amazonservices.de';
				break;
			case 3:
				$host		= 'mws.amazonservices.fr';
				break;
			case 4:
				$host		= 'mws.amazonservices.es';
				break;
			case 5:
				$host		= 'mws.amazonservices.it';
				break;
			case 6:
				$host		= 'mws.amazonservices.com';
				break;
		}
		return $host;
	}

	/*
	*	get marketplace id from account No. function
	*
	*/
	function getMarketPlaceID($account){
		switch ( $account ) {
			case 0:
				$marketID	= $this->uk1_market_id;
				break;
			case 1:
				$marketID	= $this->uk2_market_id;
				break;
			case 2:
				$marketID	= $this->de_market_id;
				break;
			case 3:
				$marketID	= $this->fr_market_id;
				break;
			case 4:
				$marketID	= $this->es_market_id;
				break;
			case 5:
				$marketID	= $this->it_market_id;
				break;
			case 6:
				$marketID	= $this->us_market_id;
				break;
		}
		return $marketID;
	}	

	/***
	*	submit shipping confirmation feed function
	***/

	function submitPriceFeed($account, $xml){

	//	get accounts credentials

		$sellerID	= $this->getSellerID($account);
		$marketID	= $this->getMarketPlaceID($account);
		$host		= $this->getHostUrl($account);
		$aws		= $this->getAWSKey($account);
		$secret		= $this->getSecurityKey($account);
		$reportType	= "_POST_PRODUCT_PRICING_DATA_";

	//	parameter query

		$query     = Array();
		$query[]   = "AWSAccessKeyId=" . $aws;
		$query[]   = "Action=SubmitFeed";
		$query[]   = "FeedType=" . $reportType;
		$query[]   = "MarketplaceIdList.Id.1=" . $marketID;
		$query[]   = "Merchant=" . $sellerID;
		$query[]   = "PurgeAndReplace=false";
		$query[]   = "SignatureMethod=HmacSHA256";
		$query[]   = "SignatureVersion=2";
		$query[]   = "Timestamp=" . urlencode(date("c"));
		$query[]   = "Version=2009-01-01";

		sort($query);
		$signature = "POST\n" . $host . "\n/\n" . implode("&", $query);
		$query[]   = "Signature=" . urlencode(base64_encode(hash_hmac('sha256', $signature, $secret, true)));


		$httpHeader = array(
			'Content-Type: text/xml; charset=UTF-8',
			'Content-Length: ' . strlen($xml),
			'Content-MD5: ' . base64_encode(md5($xml, true))
		);

		//	curl

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_URL, "https://" . $host . "/?" . implode("&", $query));
		curl_setopt($ci, CURLOPT_PORT, 443);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ci, CURLOPT_USERAGENT, "Getting Product From Amazon/1.0 (Language=PHP/5.3.5; Platform=Windows NT/i586/6.1; MWSClientVersion=2012-09-28)");
		curl_setopt($ci, CURLOPT_POST, true);
		curl_setopt($ci, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ci, CURLOPT_HEADER, true);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_HTTPHEADER, $httpHeader);
		curl_setopt($ci, CURLOPT_ENCODING, 'UTF-8' );
		
		$result = curl_exec($ci);

		$header_size = curl_getinfo($ci, CURLINFO_HEADER_SIZE);

		$body = substr($result, $header_size);
		curl_close ($ci);
		
		if ( $body ){
			$response_array = $this->XMLtoArray($body);
			$feed_id = $response_array['SUBMITFEEDRESPONSE']['SUBMITFEEDRESULT']['FEEDSUBMISSIONINFO']['FEEDSUBMISSIONID'];
			return $feed_id;
		}else{
			return false;
		}

	}

	/***
	*	convert xml to array
	***/

	function XMLtoArray($XML)
	{
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $XML, $vals);
		xml_parser_free($xml_parser);
		$_tmp = '';
		
		foreach ($vals as $xml_elem) {
			
			$x_tag = $xml_elem['tag'];
			$x_level = $xml_elem['level'];
			$x_type = $xml_elem['type'];

			if ($x_level != 1 && $x_type == 'close') {
				if (isset($multi_key[$x_tag][$x_level]))
					$multi_key[$x_tag][$x_level] =  1;
				else
					$multi_key[$x_tag][$x_level] = 0;
			}
			
			if ($x_level != 1 && $x_type == 'complete') {
				if ($_tmp == $x_tag)
					$multi_key[$x_tag][$x_level] = 1;
				$_tmp = $x_tag;
			}

		}

		foreach ($vals as $xml_elem) {

			$x_tag = $xml_elem['tag'];
			$x_level = $xml_elem['level'];
			$x_type = $xml_elem['type'];
			
			if ($x_type == 'open')
				$level[$x_level] = $x_tag;
			
			$start_level = 1;
			$php_stmt = '$xml_array';
			
			if ($x_type == 'close' && $x_level!=1)
				$multi_key[$x_tag][$x_level]++;
			
			while ($start_level < $x_level) {
				$php_stmt .= '[$level['.$start_level.']]';
				if (isset($multi_key[$level[$start_level]][$start_level]) && $multi_key[$level[$start_level]][$start_level])
					$php_stmt .= '['.($multi_key[$level[$start_level]][$start_level]-1).']';
				$start_level++;
			}

			$add = '';
			
			if (isset($multi_key[$x_tag][$x_level]) && $multi_key[$x_tag][$x_level] && ($x_type == 'open' || $x_type == 'complete')) {
				if (!isset($multi_key2[$x_tag][$x_level]))
					$multi_key2[$x_tag][$x_level] = 0;
				else
					$multi_key2[$x_tag][$x_level]++;
				$add = '['.$multi_key2[$x_tag][$x_level].']';
			}

			if (isset($xml_elem['value']) && trim($xml_elem['value']) != '' && !array_key_exists('attributes', $xml_elem)) {
				if ($x_type == 'open')
					$php_stmt_main = $php_stmt.'[$x_type]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
				else
					$php_stmt_main = $php_stmt.'[$x_tag]'.$add.' = $xml_elem[\'value\'];';
				eval($php_stmt_main);
			}

			if (array_key_exists('attributes', $xml_elem)) {
				if (isset($xml_elem['value'])) {
					$php_stmt_main = $php_stmt.'[$x_tag]'.$add.'[\'content\'] = $xml_elem[\'value\'];';
					eval($php_stmt_main);
				}
				foreach ($xml_elem['attributes'] as $key=>$value) {
					$php_stmt_att = $php_stmt.'[$x_tag]'.$add.'[$key] = $value;';
					eval($php_stmt_att);
				}
			}
		}
		return $xml_array;
	}

	function makeAzXMLFeed($arr, $account, $currency){
		
		$report = $this->getReportList($account);

		$item_arr = $this->getReportInfo($account, $report);		
		
		$num = 0;
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		$xml .= '<Header>';
		$xml .= '<DocumentVersion>1.01</DocumentVersion>';
		$xml .= '<MerchantIdentifier>';
		$xml .= $this->getSellerID($account);
		$xml .= '</MerchantIdentifier>';
		$xml .= '</Header>';
		$xml .= '<MessageType>Price</MessageType>';	
		while( $num < count($arr) ){
			$index = $num / 2 + 1;
			$sku = 'no_found_sku';
			for ( $j = 0; $j < count($item_arr); $j++ ){
					
				if ( $item_arr[$j][1] == $arr[$num] ){
					$sku = $item_arr[$j][0];
					break;
				}

			}
			$xml .= '<Message>';
			$xml .= '<MessageID>';
			$xml .= $index;
			$xml .= '</MessageID>';
			$xml .= '<Price>';
			$xml .= '<SKU>';
			$xml .= $sku;
			$xml .= '</SKU>';
			$xml .= '<StandardPrice currency="';
			$xml .= $currency;
			$xml .= '">';
			$xml .= $arr[$num + 1];
			$xml .= '</StandardPrice>';
			$xml .= '</Price>';
			$xml .= '</Message>';
			$num += 2;
		}
		$xml .= '</AmazonEnvelope>';
		return $xml;
	}

	/***
*	get report lists from amazon function
***/

	function getReportList($account){

	//	get accounts credentials

		$sellerID	= $this->getSellerID($account);
		$marketID	= $this->getMarketPlaceID($account);
		$host		= $this->getHostUrl($account);
		$aws		= $this->getAWSKey($account);
		$secret		= $this->getSecurityKey($account);
		$reportType	= "_GET_FLAT_FILE_OPEN_LISTINGS_DATA_";

	//	parameter query

		$query     = Array();
		$query[]   = "AWSAccessKeyId=" . $aws;
		$query[]   = "Action=GetReportList";
		$query[]   = "MaxCount=5";
		$query[]   = "ReportTypeList.Type.1=" . $reportType;
		$query[]   = "Merchant=" . $sellerID;
		$query[]   = "SignatureMethod=HmacSHA256";
		$query[]   = "SignatureVersion=2";
		$query[]   = "Timestamp=" . urlencode(date("c"));
		$query[]   = "Version=2009-01-01";

		sort($query);
		$signature = "POST\n" . $host . "\n/\n" . implode("&", $query);
		$query[]   = "Signature=".urlencode(base64_encode(hash_hmac('sha256', $signature, $secret, true)));

	//	curl

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_URL, "https://" . $host);
		curl_setopt($ci, CURLOPT_PORT, 443);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ci, CURLOPT_USERAGENT, "Getting Product From Amazon/1.0 (Language=PHP/5.3.5; Platform=Windows NT/i586/6.1; MWSClientVersion=2012-09-28)");
		curl_setopt($ci, CURLOPT_POST, true);
		curl_setopt($ci, CURLOPT_POSTFIELDS, implode("&", $query));
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);       
		$result = curl_exec($ci);
		curl_close ($ci);

		if ( $result ){		
			$response_array = $this->XMLtoArray($result);
			foreach($response_array['GETREPORTLISTRESPONSE']['GETREPORTLISTRESULT']['REPORTINFO'] as $key => $report){
				if ( $report['REPORTTYPE'] == $reportType ){
					$reportid = $report['REPORTID'];
					break;
				}
			}
			return $reportid;
		}else{
			$this->getReportList($account);
		}

	}

	/***
	*	get report info from amazon function
	***/

	function getReportInfo($account, $reportid){

	//	get accounts credentials

		$sellerID	= $this->getSellerID($account);
		$marketID	= $this->getMarketPlaceID($account);
		$host		= $this->getHostUrl($account);
		$aws		= $this->getAWSKey($account);
		$secret		= $this->getSecurityKey($account);

	//	parameter query

		$query     = Array();
		$query[]   = "AWSAccessKeyId=" . $aws;
		$query[]   = "Action=GetReport";
		$query[]   = "Merchant=" . $sellerID;
		$query[]   = "ReportId=" . $reportid;
		$query[]   = "SignatureMethod=HmacSHA256";
		$query[]   = "SignatureVersion=2";
		$query[]   = "Timestamp=" . urlencode(date("c"));
		$query[]   = "Version=2009-01-01";

		sort($query);
		$signature = "POST\n" . $host . "\n/\n" . implode("&", $query);
		$query[]   = "Signature=".urlencode(base64_encode(hash_hmac('sha256', $signature, $secret, true)));

	//	curl

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_URL, "https://" . $host);
		curl_setopt($ci, CURLOPT_PORT, 443);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ci, CURLOPT_USERAGENT, "Getting Product From Amazon/1.0 (Language=PHP/5.3.5; Platform=Windows NT/i586/6.1; MWSClientVersion=2012-09-28)");
		curl_setopt($ci, CURLOPT_POST, true);
		curl_setopt($ci, CURLOPT_POSTFIELDS, implode("&", $query));
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ci);
		curl_close ($ci);

		if ( $result ){
			$listing_arr = explode("\n", $result);
			foreach($listing_arr as $key => $item){
				$item_attr[] = explode("\t", $item);
			}
			return $item_attr;
		}else{
			return false;
		}	
	}

	function getValidDateTime($str){
		$datetime_arr = explode('/', $str);
		return date("Y-m-d", mktime(0, 0, 0, $datetime_arr[1], $datetime_arr[0], $datetime_arr[2])) . 'T00:00:00.000-08:00';
	}

	function writeLog($content, $file){
		$f	   = fopen( $file, 'a');
		fwrite($f, $content);
		fclose($f);
	}

	function getLogPath($account){
		switch ( $account ) {
			case 0:
				$directory	= 'uk1';
				break;
			case 1:
				$directory	= 'uk2';
				break;
			case 2:
				$directory	= 'de';
				break;
			case 3:
				$directory	= 'fr';
				break;
			case 4:
				$directory	= 'es';
				break;
			case 5:
				$directory	= 'it';
				break;
			case 6:
				$directory	= 'us';
				break;
		}
		if (!is_dir(dirname(__FILE__) . "/log/" . $directory)) {
			mkdir(dirname(__FILE__) . "/log/" . $directory, 0777, true);
		}
		if (!is_dir(dirname(__FILE__) . "/log/" . $directory . "/" . date('Ymd', time()))) {
			mkdir(dirname(__FILE__) . "/log/" . $directory . "/" . date('Ymd', time()), 0777, true);
		}
		$fileLog	= dirname(__FILE__) . "/log/" . $directory . "/" . date('Ymd', time()) . "/" . date('H_i_s', time()) . ".dat";
		return $fileLog;
	}

}

?>