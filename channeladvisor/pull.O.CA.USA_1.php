<?php

error_reporting(E_ALL);

//--------  include part   ------------------------------//

require dirname(__FILE__)."/function.php";
require dirname(__FILE__)."/../library/NetSuiteService.php";

echo "--------------------------------------------------------------------------<br>";
echo "Start Import SalesOrder From CA USA_1 Into NetSuite Script.<br>";
echo "--------------------------------------------------------------------------<br>";

//----------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------

$service = new NetSuiteService();
$service->setSearchPreferences(false, 20);

$connector = new channeladvisorConnector;

$accountID = $connector->accountUSAID1;
$subsidiary = '5';
$mwscompany = '3';		
$salesChannel = '18';
$memo = 'import from ChannelAdvisor Golf Outlets of America Inc. - US';        
$currency = '2';
$asinNumUSCustomField = 'custitem_splc_chanadus';
$asinNumBuyCustomField = 'custitem_splc_buysku';
$accountIndex = '1';

/*$file  = dirname(__FILE__) . "/log/us_1.dat";

$f	   = fopen( $file, 'rb');

$missing_array = array();

if ( $f ){
	while( $line = fgets ($f) )
		$missing_array[] = rtrim($line);
	fclose( $f );  
}*/

//--------------------------------------------------------------------
//--------------------------------------------------------------------

// Define Email Address For Send Alert Message

//--------------------------------------------------------------------
//--------------------------------------------------------------------

$emailAddress = 'hakunamoni@gmail.com';

//--------------------------------------------------------------------
//--------------------------------------------------------------------



$so_array = $connector->getOrderListFromCA($accountID, 1);
$item_Total = intval( $so_array[0]->NumberOfMatches );
if ( $item_Total > 0 ){
	$pageTotal = intval( ( $item_Total - 1 ) / 20 ) + 1;
}else{
	$pageTotal = 0;
}

if ( $pageTotal > 1 ){
	for( $i = 1; $i < $pageTotal; $i++ ){
		$so_array = array_merge($so_array, $connector->getOrderListFromCA($accountID, $i+1));
	}
}




// Check if sales order from CA is exist in ns already, then if yes, skip, if no, import this SO into orderArray.

//------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------------------

for ( $i = 0; $i < count($so_array); $i++ ){
    
    /*$orderId = $so_array[$i]->ClientOrderIdentifier;

    $searchOrder = new TransactionSearch();
    
    $orderNumberField = new SearchTextNumberField();    
    $orderNumberField->searchValue = $orderId;
    $orderNumberField->operator = "equalTo";
    
    
    $searchOrder->basic->otherRefNum = $orderNumberField;                            
    
    $requestSearchOrder = new SearchRequest();
    $requestSearchOrder->searchRecord = $searchOrder; 
    
    $searchResponseOrder = $service->search($requestSearchOrder);
    
    if (!$searchResponseOrder->searchResult->recordList->record['0']->internalId) {
        $orderArray[] = $so_array[$i];
    }*/

	$orderArray[] = $so_array[$i];
}

//------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------------------

// Import sales order into netsuite.

//------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------------------

for ( $j = 0; $j < count($orderArray); $j++ ){

// Check if customer is exist in netsuite already, then if yes, skip, if no, create customer record in netsuite.
//-------------------------------------------------------------------------------------------------------------------------------------

    $searchCustomer = new CustomerSearch();
    
    $searchCustomer->basic->email->operator = "contains";               
    $searchCustomer->basic->email->searchValue = (string)$orderArray[$j]->BuyerEmailAddress;

    $requestSearchCustomer = new SearchRequest();
    $requestSearchCustomer->searchRecord = $searchCustomer;

    $searchResponseCustomer = $service->search($requestSearchCustomer);

    if (!$searchResponseCustomer->searchResult->recordList->record['0']->internalId) {
        
        $customer = new Customer();
       
        $customer->firstName = $orderArray[$j]->ShippingInfo->FirstName;
        $customer->lastName  = $orderArray[$j]->ShippingInfo->LastName;
        
        if ( $customer->firstName == null || $customer->firstName == '' ){
            $customer->firstName = "unknown";
        }

		if (strlen($customer->firstName) > 32){
            $customer->firstName = substr($customer->firstName, 0, 29) . '...';
        }

        if ( $customer->lastName == null || $customer->lastName == '' ){
            $customer->lastName = "unknown";
        }
        
		if (strlen($customer->lastName) > 32){
            $customer->lastName = substr($customer->lastName, 0, 29) . '...';
        }
        
        $customer->isPerson = "individual";        
        $customer->email = $orderArray[$j]->BuyerEmailAddress;
        $customer->subsidiary = new RecordRef();
        $customer->subsidiary->internalId = $subsidiary;
		$customer->currency = new RecordRef();
		$customer->currency->internalId = $currency;

// ------------------------------------------------------------  AddressBook  -------------------------------------------------------------

        if ( $orderArray[$j]->ShippingInfo->CompanyName == null || $orderArray[$j]->ShippingInfo->CompanyName == '' ){
			$customer->addressbookList->addressbook['addressee'] = $customer->firstName . ' ' . $customer->lastName;
		}else{
			$customer->addressbookList->addressbook['addressee'] = $orderArray[$j]->ShippingInfo->CompanyName;
		}                           
        $customer->addressbookList->addressbook['addr1']		= $orderArray[$j]->ShippingInfo->AddressLine1;                              
        $customer->addressbookList->addressbook['addr2']		= $orderArray[$j]->ShippingInfo->AddressLine2;                               
        $customer->addressbookList->addressbook['city']			= $orderArray[$j]->ShippingInfo->City;                               
        $customer->addressbookList->addressbook['country']		= $connector->country_code_arr[$orderArray[$j]->ShippingInfo->CountryCode];
        $customer->addressbookList->addressbook['state']		= $orderArray[$j]->ShippingInfo->Region;                               
        $customer->addressbookList->addressbook['zip']			= $orderArray[$j]->ShippingInfo->PostalCode;
		if ( $orderArray[$j]->ShippingInfo->PhoneNumberDay != '' && $orderArray[$j]->ShippingInfo->PhoneNumberDay != null ){
			$customer->phone									= $orderArray[$j]->ShippingInfo->PhoneNumberDay;
			$customer->addressbookList->addressbook['phone']	= $orderArray[$j]->ShippingInfo->PhoneNumberDay;
		}else if ( $orderArray[$j]->ShippingInfo->PhoneNumberEvening != '' && $orderArray[$j]->ShippingInfo->PhoneNumberEvening != null ){
			$customer->phone									= $orderArray[$j]->ShippingInfo->PhoneNumberEvening;
			$customer->addressbookList->addressbook['phone']	= $orderArray[$j]->ShippingInfo->PhoneNumberEvening;
		}        
        $customer->addressbookList->replaceAll = 1;

// ------------------------------------------------------------  -----------  -------------------------------------------------------------

        $requestCustomerInsert = new AddRequest();
        $requestCustomerInsert->record = $customer;

        $responseCustomerInsert = $service->add($requestCustomerInsert);

        if (!$responseCustomerInsert->writeResponse->status->isSuccess) {
            echo "--------------------------------------------------------------------------<br>";
            echo "ADD CUSTOMER ERROR<br>";
            echo "--------------------------------------------------------------------------<br>";
            $connector->sendMailWithAlert($emailAddress, $orderArray[$j]->ClientOrderIdentifier, $responseCustomerInsert->writeResponse->status->statusDetail[0]->message, "USA-1");
            exit();
        } else {
            $customerInternalId = $responseCustomerInsert->writeResponse->baseRef->internalId;
        }
    }else{
        $customerInternalId = $searchResponseCustomer->searchResult->recordList->record['0']->internalId;
    }

//-------------------------------------------------------------------------------------------------------------------------------------

// Search product in netsuite.
//-------------------------------------------------------------------------------------------------------------------------------------

    $nsItemArray = array();
    
	$items_count = 0;            
	
	if ( count($orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem) > 1 ){
		$priority = '2';
		foreach ( $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem as $item ){
			$searchItem = new ItemSearch();
			$asinCustomField = new SearchStringCustomField();
			$asinCustomField->searchValue = $item->SKU;
			$asinCustomField->internalId = $asinNumUSCustomField;
			$asinCustomField->operator = "contains";    
			$searchItem->basic->customFieldList->customField = array($asinCustomField);
			$requestItemsearch = new SearchRequest();
			$requestItemsearch->searchRecord = $searchItem;
			$searchItemResponse = $service->search($requestItemsearch);
			if ($searchItemResponse->searchResult->status->isSuccess) {
				if ( $searchItemResponse->searchResult->recordList->record['0']->internalId ){
					$nsItemArray[$items_count]['internal_id'] = $searchItemResponse->searchResult->recordList->record['0']->internalId;
					$nsItemArray[$items_count]['unitPrice'] = $item->UnitPrice * $item->Quantity + $item->TaxCost + $item->ShippingCost + $item->ShippingTaxCost + $item->GiftWrapCost + $item->GiftWrapTaxCost;
					$nsItemArray[$items_count]['quantity'] = $item->Quantity;
					$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
					if ( $warehouseFromState == null ){
						$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
					}
					if ( $warehouseFromState == 'NC' ){
						if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
							$nsItemArray[$items_count]['warehouse'] = '3';
						}else{
							$nsItemArray[$items_count]['warehouse'] = '4';
						}
					}else if ( $warehouseFromState == 'TP' ){
						if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
							$nsItemArray[$items_count]['warehouse'] = '4';
						}else{
							$nsItemArray[$items_count]['warehouse'] = '3';
						}
					}
				}else{
					$searchItem = new ItemSearch();
					$asinCustomField = new SearchStringCustomField();
					$asinCustomField->searchValue = $item->SKU;
					$asinCustomField->internalId = $asinNumBuyCustomField;
					$asinCustomField->operator = "contains";    
					$searchItem->basic->customFieldList->customField = array($asinCustomField);
					$requestItemsearch = new SearchRequest();
					$requestItemsearch->searchRecord = $searchItem;
					$searchItemResponse = $service->search($requestItemsearch);
					if ($searchItemResponse->searchResult->status->isSuccess) {
						if ( $searchItemResponse->searchResult->recordList->record['0']->internalId ){
							$nsItemArray[$items_count]['internal_id'] = $searchItemResponse->searchResult->recordList->record['0']->internalId;
							$nsItemArray[$items_count]['unitPrice'] = $item->UnitPrice * $item->Quantity + $item->TaxCost + $item->ShippingCost + $item->ShippingTaxCost + $item->GiftWrapCost + $item->GiftWrapTaxCost;
							$nsItemArray[$items_count]['quantity'] = $item->Quantity;
							$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
							if ( $warehouseFromState == null ){
								$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
							}
							if ( $warehouseFromState == 'NC' ){
								if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
									$nsItemArray[$items_count]['warehouse'] = '3';
								}else{
									$nsItemArray[$items_count]['warehouse'] = '4';
								}
							}else if ( $warehouseFromState == 'TP' ){
								if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
									$nsItemArray[$items_count]['warehouse'] = '4';
								}else{
									$nsItemArray[$items_count]['warehouse'] = '3';
								}
							}
						}else{
							$nsItemArray[$items_count]['internal_id'] = 12085;
							$nsItemArray[$items_count]['unitPrice'] = $item->UnitPrice * $item->Quantity + $item->TaxCost + $item->ShippingCost + $item->ShippingTaxCost + $item->GiftWrapCost + $item->GiftWrapTaxCost;
							$nsItemArray[$items_count]['quantity'] = $item->Quantity;
							$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
							if ( $warehouseFromState == null ){
								$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
							}
							if ( $warehouseFromState == 'NC' ){
								if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
									$nsItemArray[$items_count]['warehouse'] = '3';
								}else{
									$nsItemArray[$items_count]['warehouse'] = '4';
								}
							}else if ( $warehouseFromState == 'TP' ){
								if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
									$nsItemArray[$items_count]['warehouse'] = '4';
								}else{
									$nsItemArray[$items_count]['warehouse'] = '3';
								}
							}
						}
					}else{
						$nsItemArray[$items_count]['internal_id'] = 12085;
						$nsItemArray[$items_count]['unitPrice'] = $item->UnitPrice * $item->Quantity + $item->TaxCost + $item->ShippingCost + $item->ShippingTaxCost + $item->GiftWrapCost + $item->GiftWrapTaxCost;
						$nsItemArray[$items_count]['quantity'] = $item->Quantity;
						$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
						if ( $warehouseFromState == null ){
							$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
						}
						if ( $warehouseFromState == 'NC' ){
							if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
								$nsItemArray[$items_count]['warehouse'] = '3';
							}else{
								$nsItemArray[$items_count]['warehouse'] = '4';
							}
						}else if ( $warehouseFromState == 'TP' ){
							if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
								$nsItemArray[$items_count]['warehouse'] = '4';
							}else{
								$nsItemArray[$items_count]['warehouse'] = '3';
							}
						}
					}
				}
			}else{
				$nsItemArray[$items_count]['internal_id'] = 12085;
				$nsItemArray[$items_count]['unitPrice'] = $item->UnitPrice * $item->Quantity + $item->TaxCost + $item->ShippingCost + $item->ShippingTaxCost + $item->GiftWrapCost + $item->GiftWrapTaxCost;
				$nsItemArray[$items_count]['quantity'] = $item->Quantity;
				$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
				if ( $warehouseFromState == null ){
					$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
				}
				if ( $warehouseFromState == 'NC' ){
					if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
						$nsItemArray[$items_count]['warehouse'] = '3';
					}else{
						$nsItemArray[$items_count]['warehouse'] = '4';
					}
				}else if ( $warehouseFromState == 'TP' ){
					if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
						$nsItemArray[$items_count]['warehouse'] = '4';
					}else{
						$nsItemArray[$items_count]['warehouse'] = '3';
					}
				}
			}
			$items_count++;
		}
	}else{
		$priority = '1';
		$searchItem = new ItemSearch();
		$asinCustomField = new SearchStringCustomField();
		$asinCustomField->searchValue = $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->SKU;
		$asinCustomField->internalId = $asinNumUSCustomField;
		$asinCustomField->operator = "contains";    
		$searchItem->basic->customFieldList->customField = array($asinCustomField);
		$requestItemsearch = new SearchRequest();
		$requestItemsearch->searchRecord = $searchItem;
		$searchItemResponse = $service->search($requestItemsearch);
		if ($searchItemResponse->searchResult->status->isSuccess) {
			if ( $searchItemResponse->searchResult->recordList->record['0']->internalId ){
				$nsItemArray[0]['internal_id'] = $searchItemResponse->searchResult->recordList->record['0']->internalId;
				$nsItemArray[0]['unitPrice']   =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->UnitPrice * $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity +				$orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->TaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingTaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapTaxCost;
				$nsItemArray[0]['quantity'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity;
				$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
				if ( $warehouseFromState == null ){
					$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
				}
				if ( $warehouseFromState == 'NC' ){
					if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
						$nsItemArray[0]['warehouse'] = '3';
					}else{
						$nsItemArray[0]['warehouse'] = '4';
					}
				}else if ( $warehouseFromState == 'TP' ){
					if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
						$nsItemArray[0]['warehouse'] = '4';
					}else{
						$nsItemArray[0]['warehouse'] = '3';
					}
				}
			}else{
				$searchItem = new ItemSearch();
				$asinCustomField = new SearchStringCustomField();
				$asinCustomField->searchValue = $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->SKU;
				$asinCustomField->internalId = $asinNumBuyCustomField;
				$asinCustomField->operator = "contains";    
				$searchItem->basic->customFieldList->customField = array($asinCustomField);
				$requestItemsearch = new SearchRequest();
				$requestItemsearch->searchRecord = $searchItem;
				$searchItemResponse = $service->search($requestItemsearch);
				if ($searchItemResponse->searchResult->status->isSuccess) {
					if ( $searchItemResponse->searchResult->recordList->record['0']->internalId ){
						$nsItemArray[0]['internal_id'] = $searchItemResponse->searchResult->recordList->record['0']->internalId;
						$nsItemArray[0]['unitPrice']   =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->UnitPrice * $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity +				$orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->TaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingTaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapTaxCost;
						$nsItemArray[0]['quantity'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity;
						$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
						if ( $warehouseFromState == null ){
							$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
						}
						if ( $warehouseFromState == 'NC' ){
							if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
								$nsItemArray[0]['warehouse'] = '3';
							}else{
								$nsItemArray[0]['warehouse'] = '4';
							}
						}else if ( $warehouseFromState == 'TP' ){
							if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
								$nsItemArray[0]['warehouse'] = '4';
							}else{
								$nsItemArray[0]['warehouse'] = '3';
							}
						}
					}else{
						$nsItemArray[0]['internal_id'] = 12085;
						$nsItemArray[0]['unitPrice']   =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->UnitPrice * $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity +				$orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->TaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingTaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapTaxCost;
						$nsItemArray[0]['quantity'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity;
						$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
						if ( $warehouseFromState == null ){
							$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
						}
						if ( $warehouseFromState == 'NC' ){
							if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
								$nsItemArray[0]['warehouse'] = '3';
							}else{
								$nsItemArray[0]['warehouse'] = '4';
							}
						}else if ( $warehouseFromState == 'TP' ){
							if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
								$nsItemArray[0]['warehouse'] = '4';
							}else{
								$nsItemArray[0]['warehouse'] = '3';
							}
						}
					}
				}else{
					$nsItemArray[0]['internal_id'] = 12085;
					$nsItemArray[0]['unitPrice']   =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->UnitPrice * $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity +				$orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->TaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingTaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapTaxCost;
					$nsItemArray[0]['quantity'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity;
					$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
					if ( $warehouseFromState == null ){
						$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
					}
					if ( $warehouseFromState == 'NC' ){
						if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
							$nsItemArray[0]['warehouse'] = '3';
						}else{
							$nsItemArray[0]['warehouse'] = '4';
						}
					}else if ( $warehouseFromState == 'TP' ){
						if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
							$nsItemArray[0]['warehouse'] = '4';
						}else{
							$nsItemArray[0]['warehouse'] = '3';
						}
					}
				}
			}
		}else{
			$nsItemArray[0]['internal_id'] = 12085;
			$nsItemArray[0]['unitPrice']   =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->UnitPrice * $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity +				$orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->TaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingTaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapTaxCost;
			$nsItemArray[0]['quantity'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity;
			$warehouseFromState = $connector->usa_warehouse_arr1[(string)strtoupper($orderArray[$j]->ShippingInfo->Region)];
			if ( $warehouseFromState == null ){
				$warehouseFromState = $connector->usa_warehouse_arr2[strtolower(str_replace(' ', '', (string)$orderArray[$j]->ShippingInfo->Region))];
			}
			if ( $warehouseFromState == 'NC' ){
				if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[6]->quantityOnHand ){
					$nsItemArray[0]['warehouse'] = '3';
				}else{
					$nsItemArray[0]['warehouse'] = '4';
				}
			}else if ( $warehouseFromState == 'TP' ){
				if ( $searchItemResponse->searchResult->recordList->record['0']->locationsList->locations[10]->quantityOnHand ){
					$nsItemArray[0]['warehouse'] = '4';
				}else{
					$nsItemArray[0]['warehouse'] = '3';
				}
			}
		}
	}
    
    $so = new SalesOrder();
    
    $so->entity = new RecordRef();
    if ( $customerInternalId ){
       $so->entity->internalId = $customerInternalId; 
    }
    
	$so->customForm = new RecordRef();
    $so->customForm->internalId = '102';
    
	$so->class = new RecordRef();

	if ( $orderArray[$j]->PaymentInfo->PaymentType == 'Sears' ){
		$salesChannel = '48';
	}else if ( $orderArray[$j]->PaymentInfo->PaymentType == 'Newegg' ){
		$salesChannel = '50';
	}else if ( $orderArray[$j]->PaymentInfo->PaymentType == 'Buy.com' ){
		$salesChannel = '49';
	}

	$so->class->internalId = $salesChannel;
    
	
	$so->subsidiary = new RecordRef();
    $so->subsidiary->internalId = $subsidiary;
	
	$so->currency = new RecordRef();
    $so->currency->internalId = $currency;
    
	$so->memo = $memo;

    $so->otherRefNum = $orderArray[$j]->ClientOrderIdentifier;
    
	$customCompanyField = new SelectCustomFieldRef();
    $customCompanyField->internalId = 'custbody_nswms_company';
    $customCompanyField->value = new ListOrRecordRef();
    $customCompanyField->value->typeId = 'company';
    $customCompanyField->value->internalId = $mwscompany;

	$customAccountIndexField = new StringCustomFieldRef();
	$customAccountIndexField->internalId = 'custbody_splc_chanad_account';
	$customAccountIndexField->value = (string)$accountIndex;

	$customSecReferenceField = new StringCustomFieldRef();
	$customSecReferenceField->internalId = 'custbody_splc_sec_reference';
	$customSecReferenceField->value = $orderArray[$j]->OrderID;

	$customPriorityField = new SelectCustomFieldRef();
	$customPriorityField->internalId = 'custbody_nswmspriority';
	$customPriorityField->value = new ListOrRecordRef();
	$customPriorityField->value->typeId = 'Order Priority';
	$customPriorityField->value->internalId = $priority;

	$so->customFieldList->customField = array($customCompanyField, $customSecReferenceField, $customPriorityField, $customAccountIndexField);
	
	$shippingCost = 0.00;

	$so->shipMethod->internalId = 13789;
	
	if ( $salesChannel == '48' ){
		$so->shippingTaxCode = new RecordRef();
		$so->shippingTaxCode->internalId = -8;
	}

	foreach( $nsItemArray as $itemArray )
	{
		if ( isset($itemArray['internal_id']) && !empty($itemArray['internal_id']) ){
			$soi = new SalesOrderItem();
			$soi->item = new RecordRef();
			if ( $itemArray['internal_id'] ) {   
				$soi->item->internalId = $itemArray['internal_id'];                                
			}                            
			$soi->quantity = $itemArray['quantity'];
			$soi->price = new RecordRef();
			$soi->price->internalId = -1;

			$soi->location = new RecordRef();
			$soi->location->internalId = $itemArray['warehouse'];

			if ( $salesChannel == '48' ){
				$soi->taxCode = new RecordRef();
				$soi->taxCode->internalId = -8;
			}

			$itemStatus = new SelectCustomFieldRef();
				
			$itemStatus->internalId = 'custcol_ebiznet_item_status';
			$itemStatus->value = new ListOrRecordRef();
			$itemStatus->value->typeId = 'Item Status';
			$itemStatus->value->internalId = $connector->itemStatusMatch($itemArray['warehouse']);

			$create_fulfillment_order = new BooleanCustomFieldRef();

			$create_fulfillment_order->internalId = 'custcol_create_fulfillment_order';
			$create_fulfillment_order->value = '1';
			
			$soi->customFieldList->customField = array($itemStatus, $create_fulfillment_order);

			
			$soi->amount = floatval($itemArray['unitPrice']);
			
			$so->itemList->item[] = $soi;
			$p++;
		}                                                      
	}
    for ( $p = 1; $p < count($orderArray[$j]->ShoppingCart->LineItemInvoiceList->OrderLineItemInvoice); $p++ ){
		$shippingCost += floatval($orderArray[$j]->ShoppingCart->LineItemInvoiceList->OrderLineItemInvoice[$p]->UnitPrice);
	}

	if ( $shippingCost > 0.00 ) {
		$delivery = new SalesOrderItem();
		
		$delivery->item = new RecordRef();
		$delivery->item->internalId = '18954';                                
									
		$delivery->quantity = 1;    

		$delivery->price = new RecordRef();
		$delivery->price->internalId = -1;
						
		$delivery->location = new RecordRef();
		$delivery->location->internalId = $itemArray['warehouse'];
		
		$itemStatus = new SelectCustomFieldRef();
			
		$itemStatus->internalId = 'custcol_ebiznet_item_status';
		$itemStatus->value = new ListOrRecordRef();
		$itemStatus->value->typeId = 'Item Status';
		$itemStatus->value->internalId = $connector->itemStatusMatch($itemArray['warehouse']);

		$create_fulfillment_order = new BooleanCustomFieldRef();

		$create_fulfillment_order->internalId = 'custcol_create_fulfillment_order';
		$create_fulfillment_order->value = '1';
		
		$delivery->customFieldList->customField = array($itemStatus, $create_fulfillment_order);

		$delivery->amount = $shippingCost;

		$so->itemList->item[] = $delivery;
	}

	$so->shippingCost = 0.00;

	$so_amount = 0.00;
		
	foreach($so->itemList->item as $orderItemsCreatedSalesOrder){
		$so_amount += floatval($orderItemsCreatedSalesOrder->amount);
	}

	$soRecArray[] = $so;
	
	$customerDepositRecArray[$j]['customer'] = $customerInternalId;
	$customerDepositRecArray[$j]['amount']   = $so_amount;
	$customerDepositRecArray[$j]['location'] = $so->location->internalId;
	$customerDepositRecArray[$j]['currency'] = $currency;
	$customerDepositRecArray[$j]['memo']	 = $memo;
	$customerDepositRecArray[$j]['channel']	 = $salesChannel;
	$customerDepositRecArray[$j]['payment']  = $orderArray[$j]->PaymentInfo->PaymentType;
		
		
		
}

if ( count($soRecArray) > 0 ){
	$requestImport = new AddListRequest();

	$requestImport->record = $soRecArray;

	$responseImportSO = $service->addList($requestImport);

	$customerDepositRecArr = array();

	$t = 0;

	foreach ( $responseImportSO->writeResponseList->writeResponse as $response ){
		if ( $response->status->isSuccess ){
			$customerDepositRecArr[] = $connector->customerDepositModule( $response->baseRef->internalId, $customerDepositRecArray[$t]['customer'],$customerDepositRecArray[$t]['amount'],$customerDepositRecArray[$t]['location'], $customerDepositRecArray[$t]['currency'], $customerDepositRecArray[$t]['memo'], $customerDepositRecArray[$t]['channel'], $customerDepositRecArray[$t]['payment']);
			echo "<br>--------------------------------------------------------------------------<br>";
			echo "ADD SALES ORDER SUCCESS, id " . $response->baseRef->internalId . "<br>";
			echo "<br>--------------------------------------------------------------------------<br>";
		}else {
			echo "--------------------------------------------------------------------------<br>";
			echo "ADD SALESORDER ERROR. CA USA_1 ID: " .$orderArray[$t]->ClientOrderIdentifier ."<br>";
			$connector->sendMailWithAlert($emailAddress, $orderArray[$t]->ClientOrderIdentifier, $response->status->statusDetail[0]->message, "USA-1");
			echo "<br>--------------------------------------------------------------------------<br>";
		}
		$t++;
	}

	$requestImport = new AddListRequest();

	$requestImport->record = $customerDepositRecArr;

	$responseImportSO = $service->addList($requestImport);

	$t = 0;

	foreach ( $responseImportSO->writeResponseList->writeResponse as $response ){
		if ( $response->status->isSuccess ){
			echo "<br>--------------------------------------------------------------------------<br>";
			echo "ADD CUSTOMER DEPOSIT RECORD SUCCESS, id " . $response->baseRef->internalId . "<br>";
			echo "<br>--------------------------------------------------------------------------<br>";
		}else {
			echo "--------------------------------------------------------------------------<br>";
			echo "ADD CUSTOMER DEPOSIT RECORD ERROR. CA USA_1 ID: " .$orderArray[$t]->ClientOrderIdentifier ."<br>";
			$connector->sendMailWithAlert($emailAddress, $orderArray[$t]->ClientOrderIdentifier, $response->status->statusDetail[0]->message, "USA-1");
			echo "<br>--------------------------------------------------------------------------<br>";
		}
		$t++;
	}

}

echo "--------------------------------------------------------------------------<br>";
echo "End Import SalesOrder From CA USA_1 Into NetSuite Script.<br>";
echo "--------------------------------------------------------------------------<br>";


?>