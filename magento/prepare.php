<?php 
/*	
* -----------------------------------------------------------------
*	File			magento.ship.conf.php
*	Version			1.0
*	Create Date		2014/10/10
*	Skype			hakunamoni
* -----------------------------------------------------------------

*/
 $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', 1);
$string = '';
foreach ($orders as $order) {
       $string .= $order->toString(). ' ';
} 

//How to include external PHP file in Magento?

Mage::getModel('mynamespace/mymodule')->myFunction()
Mage::helper('mymodulefrontname')->myFunction()

protected function _initiateDbConnection()
{
        $configs = array('model' => 'mysql4', 'active' => '1', 'host' => 'localhost', 'username' => '', 'password' => '', 'dbname' => '', 'charset' => 'utf8');         
        return Mage::getSingleton('core/resource')->createConnection('mymodule_read', 'pdo_mysql', $configs);
}

// include mage file

include 'app/Mage.php';

Mage::app();

$model= Mage::getModel('sample_testModule/testModule')->load(1);

echo get_class($model);

?>