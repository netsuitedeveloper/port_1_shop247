<?php

/*	
* -----------------------------------------------------------------
*	File			function-rakuten.php
*	Create Date		11/05/2014
*	Created By		Xiaoxin Li (nsmastership@gmail.com)
* -----------------------------------------------------------------
*/

class rakutenConnector {

/*
construct function - Load NetSuite WebService
*/
	function __construct() {
		include dirname(__FILE__)."/dom.php";
		//$this->connection();
		
    }

	private function connection() {
		$this->connection = mysql_connect($this->db_host, $this->db_user, $this->db_pass);
		if(!$this->connection) {
			die("Database connection failed: ". mysql_error());
		} else {
			$db_select = mysql_select_db($this->db_base);
			if(!$db_select) die("Database selection failed: ". mysql_error());
		}
		mysql_query("set names utf8") or die("set names utf8 failed");
	}

	public function query($query) {
		$result = mysql_query($query, $this->connection);
		if(!$result && DEBUG)
			die("<div style='margin:3%'>Database query failed: <b>".mysql_error()."</b> <br><div style='border:1px dashed red;width:50%; margin-top:50px; background: #ffdfdf; padding:10px; font:normal 12pt courier new'>".$query."</div></div>");
		return $result;
	}

	function far($query) {
		$a = Array();
		$q = $this->query($query);
		$c = mysql_num_rows($q);
		for ($i = 0; $i < $c; $i++)
			$a[] = $this->fetch($q);
		return $a;
	}

	function fetch($row) {
		return mysql_fetch_assoc($row);
	}

	function getOrderLists(){

		$before_time = microtime(true);
		$micro = sprintf("%03d",($before_time - floor($before_time)) * 1000);
		$before = gmdate('Y-m-d\TH:i:s.', $before_time).$micro.'Z';

		$after_time = strtotime("-20 day");
		$micro = sprintf("%03d",($after_time - floor($after_time)) * 1000);
		$after = gmdate('Y-m-d\TH:i:s.', $after_time).$micro.'Z';

		$query     = Array();
		$query[]   = "marketplaceIdentifier=uk";
		$query[]   = "shopURL=" . $this->shop_url;
		$query[]   = "createdAfter=" . urlencode($after);
		$query[]   = "createdBefore=" . urlencode($before);
		$query[]   = "orderStatus=NotShipped";
		$query[]   = "maxResultsPerPage=50";

	//	curl date('Y-m-dTH:i:s.uZ')

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization : ' . $this->auth_key));
		curl_setopt($ci, CURLOPT_URL, $this->end_point . '/order/list?' . implode("&", $query));
		curl_setopt($ci, CURLOPT_PORT, 443);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($ci, CURLOPT_POST, true);
		//curl_setopt($ci, CURLOPT_POSTFIELDS, implode("&", $query));
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);                                                                                                      
		$result = curl_exec($ci);
		curl_close ($ci);

		$result = json_decode($result);

		if ( $result->totalCount > 0 ){
			return $result->orders;
		}else{
			return false;
		}
	}

	function getOrderDetail($ordernum){

		$query     = Array();
		$query[]   = "marketplaceIdentifier=uk";
		$query[]   = "orderNumber=" . $ordernum;
		$query[]   = "shopURL=" . $this->shop_url;

	//	curl date('Y-m-dTH:i:s.uZ')

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8', 'Authorization : ' . $this->auth_key));
		curl_setopt($ci, CURLOPT_URL, $this->end_point . '/order/get?' . implode("&", $query));
		curl_setopt($ci, CURLOPT_PORT, 443);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ci, CURLOPT_POST, false);
		//curl_setopt($ci, CURLOPT_POSTFIELDS, implode("&", $query));
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);                                                                                                      
		$result = curl_exec($ci);
		curl_close ($ci);

		//echo $result;

		$result = json_decode(utf8_encode($result));

		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				echo ' - No errors';
			break;
			case JSON_ERROR_DEPTH:
				echo ' - Maximum stack depth exceeded';
			break;
			case JSON_ERROR_STATE_MISMATCH:
				echo ' - Underflow or the modes mismatch';
			break;
			case JSON_ERROR_CTRL_CHAR:
				echo ' - Unexpected control character found';
			break;
			case JSON_ERROR_SYNTAX:
				echo ' - Syntax error, malformed JSON';
			break;
			case JSON_ERROR_UTF8:
				echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
			default:
				echo ' - Unknown error';
			break;
		}
		return $result;
	}

}

?>