<?php

/*	
* -----------------------------------------------------------------
*	File			function_magento.php
*	Version			1.0
*	Create Date		2014/09/24
*	Skype			hakunamoni
* -----------------------------------------------------------------
*/

/*
*	magento connector class
*/
class magentoconnector {



/*
construct function - Load Magento WebService
*/
	function __construct() {
		//include dirname(__FILE__)."/dom.php";
		//$this->connection();
		// magento soap service
		$this->client	= new SoapClient( $this->soap_wsdl, array('trace' => 1, "connection_timeout" => 120));
		$this->session	= $this->client->login( $this->soap_user, $this->soap_pass );
    }

	private function client(){
	}
    
    private function session(){
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

/***
*	get sales order list from magento
***/
	function getSalesOrderList(){
		
		$params		= array(
            'complex_filter' => array(
                array(
                    'key' => 'status',
                    'value' => array(
                        'key' => 'nin',
                        'value' => 'ns_exported'
                    )
                )
            )
        );

		$result		= $this->client->salesOrderList( $this->session );
		return $result;
	}
/***
*	get sales order info
***/
	function getSalesOrder($id){
		$result		= $this->client->salesOrderInfo( $this->session, $id );
		return $result;
	}

	function updateSalesOrderStatus($id, $status){
		$result		= $this->client->salesOrderAddComment( $this->session, $id, $status);
		return $result;
	}

	function unholdSalesOrder($id){
		$result		= $this->client->salesOrderUnhold( $this->session, $id);
		return $result;
	}

	function updateNSFields($id){
		$url = $this->suitelet_url . '&cust_order_id=' . $id . '&cust_order_comment=sent';

		$ci     = curl_init();
		curl_setopt($ci, CURLOPT_URL, $url);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_PORT, 443);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1145.0 Safari/537.1");
		$result = curl_exec($ci);

		return $result; 
	}

	function createShipment($id){
		
		//$orderitems = array();

		//for ( $i = 0; $i < count($items_qty); $i++ ){
		//	$orderitems[] = array('order_item_id' => $items_qty[$i][0], 'qty' => $items_qty[$i][1]);
		//}

		$result = $this->client->salesOrderShipmentList( $this->session );

		print_r($result);

		$result = $this->client->salesOrderShipmentGetCarriers( $this->session, $id );

		print_r($result);

		$result	= $this->client->salesOrderShipmentCreate( $this->session, $id, array(), 'shipment create', false, false );

		return $result;

	}

	function addTrackShipment($shipment_id, $carrier, $tracking_num){
		$result = $this->client->salesOrderShipmentAddTrack( $this->session, $shipment_id, $carrier, null, $tracking_num);
		return $result;
	}

	function getShipmentInfo($shipment_id){
		$result = $this->client->salesOrderShipmentInfo( $this->session, $shipment_id);
		return $result;
	}

/***
*	get inventory stock list
***/
	function getInventoryStock($sku_array){
		$result		= $this->client->catalogInventoryStockItemList( $this->session, $sku_array);
		//$result	= $this->client->catalogInventoryStockItemList( $this->session, array('274', '259'));
		return $result;
	}

	function updateInventoryStock($product_id, $qty){

		$in_stock = ( (int)$qty > 0 ) ? 1 : 0;

		$update_data = array( 'qty' => $qty, 'is_in_stock' => $in_stock );

		$result = $this->client->catalogInventoryStockItemUpdate( $this->session, $product_id, $update_data );

		return $result;
	}

}

?>