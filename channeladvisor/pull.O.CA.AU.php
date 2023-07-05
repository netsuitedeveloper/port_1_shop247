<?php

error_reporting(E_ALL);

//--------  include part   ------------------------------//

require dirname(__FILE__)."/function_nu.php";
require dirname(__FILE__)."/../library/NetSuiteService.php";

echo "--------------------------------------------------------------------------<br>";
echo "Start Import SalesOrder From CA AU Into NetSuite Script.<br>";
echo "--------------------------------------------------------------------------<br>";

//----------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------

$service = new NetSuiteService();
$service->setSearchPreferences(false, 20);

$connector = new channeladvisorConnector;

$accountID = $connector->accountAUID;
$subsidiary = '3';
$mwscompany = '4';		
$salesChannel = '17';
$memo = 'import from ChannelAdvisor The Sports HQ - AU';
$currency = '8';
$location = '5';
$itemStocks = '8';
$shipMethod = '17614';
$asinNumCustomField = 'custitem_splc_chanadau';
$accountIndex = '0';

//--------------------------------------------------------------------
//--------------------------------------------------------------------

// Define Email Address For Send Alert Message

//--------------------------------------------------------------------
//--------------------------------------------------------------------

$emailAddress = $connector->emailAddr;

//--------------------------------------------------------------------
//--------------------------------------------------------------------



$priorities = array();

$so_array = $connector->getOrderListFromCA($accountID, 1);


if ($so_array){
    $item_Total = intval( $so_array[0]['NumberOfMatches'] );
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
        
        $orderId = $so_array[$i]['ClientOrderIdentifier'];

        $searchOrder = new TransactionSearch();
        
        $orderNumberField = new SearchTextNumberField();    
        $orderNumberField->searchValue = $orderId;
        $orderNumberField->operator = "equalTo";
        
        
        $searchOrder->basic->otherRefNum = $orderNumberField;                            
        
        $requestSearchOrder = new SearchRequest();
        $requestSearchOrder->searchRecord = $searchOrder; 
        
        $searchResponseOrder = $service->search($requestSearchOrder);
        
        if (!$searchResponseOrder->searchResult->recordList->record['0']->internalId) {
            $orderArray[] = $connector->arrayToObject($so_array[$i]);
            $priorities[] = '-1';
        }
        $orderArray[] = $connector->arrayToObject($so_array[$i]);
        $priorities[] = '-1';

    }
}

$newOrderArray = true;

if ( !isset($orderArray)){
	$newOrderArray = false;
}else{
	if ( count($orderArray) < 5 ){
		$newOrderArray = false;
	}
}

if ( !$newOrderArray ){
    $so_array = $connector->getLowOrderListFromCA($accountID, 1);


    $item_Total = intval( $so_array[0]['NumberOfMatches'] );
    if ( $item_Total > 0 ){
        $pageTotal = intval( ( $item_Total - 1 ) / 100 ) + 1;
    }else{
        $pageTotal = 0;
    }

    if ( $pageTotal > 1 ){
        for( $i = 1; $i < $pageTotal; $i++ ){
            $so_array = array_merge($so_array, $connector->getLowOrderListFromCA($accountID, $i+1));
        }
    }
    $test  = dirname(__FILE__) . "/../nsOrders/ns_chan.dat";
    $f       = fopen( $test, 'r');

    $old_active_array = array();

    if ( $f ){
        while( $line = fgets ($f) )
            $old_active_array[] = rtrim($line);
        fclose( $f );  
    }

    $missingArray = array();
    if ( $so_array ){
        for ( $i = 0; $i < count($so_array); $i++ ){
           $orderId = $so_array[$i]['ClientOrderIdentifier'];
           if (!in_array($orderId, $old_active_array)){
                $missingArray[] = $orderId;
           }   
        }    
    }
    
    if ( $missingArray ){
        $missArr = $connector->getMissingOrderListFromCA($accountID, 1, $missingArray);
        for ( $i = 0; $i < count($missArr); $i++ ){
            $orderArray[] = $connector->arrayToObject($missArr[$i]);
            $priorities[] = '-2';
        }        
    }    
}
if ( !isset($orderArray) ){
    exit();
}
//------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------------------

// Import sales order into netsuite.

//------------------------------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------------------

for ( $j = 0; $j < count($orderArray); $j++ ){

	//-------------------------------------------------------------------------------------------------------------------------------------

// Search product in netsuite.
//-------------------------------------------------------------------------------------------------------------------------------------

    $nsItemArray = array();

	$continue = false;
    
	$items_count = 0;            
    if( isset($orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->SKU) ){
        $orderLineItemArray = $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem;
    }else{
        $orderLineItemArray = (array)$orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem;
    }
    if ( count($orderLineItemArray) > 1 ){
		$priority = '2';
		foreach ( $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem as $item ){
			$ebayUserId = $item->BuyerUserID;
			$searchItem = new ItemSearch();
			$asinCustomField = new SearchStringCustomField();
			$asinCustomField->searchValue = $item->SKU;
			$asinCustomField->internalId = $asinNumCustomField;
			$asinCustomField->operator = "is";    
			$searchItem->basic->customFieldList->customField = array($asinCustomField);
			$requestItemsearch = new SearchRequest();
			$requestItemsearch->searchRecord = $searchItem;
			$searchItemResponse = $service->search($requestItemsearch);
			if ($searchItemResponse->searchResult->status->isSuccess) {
				if ( isset($searchItemResponse->searchResult->recordList->record['0']->internalId) ){
					$nsItemArray[$items_count]['internal_id'] = $searchItemResponse->searchResult->recordList->record['0']->internalId;
					$nsItemArray[$items_count]['unitPrice'] = $item->UnitPrice * $item->Quantity + $item->TaxCost + $item->ShippingCost + $item->ShippingTaxCost + $item->GiftWrapCost + $item->GiftWrapTaxCost;
					$nsItemArray[$items_count]['quantity'] = $item->Quantity;
				}else{					
					$searchItem = new ItemSearch();
					$asinCustomField = new SearchStringCustomField();
					$asinCustomField->searchValue = $item->SKU;
					$asinCustomField->internalId = $asinNumCustomField;
					$asinCustomField->operator = "contains";    
					$searchItem->basic->customFieldList->customField = array($asinCustomField);
					$requestItemsearch = new SearchRequest();
					$requestItemsearch->searchRecord = $searchItem;
					$searchItemResponse = $service->search($requestItemsearch);
					if ($searchItemResponse->searchResult->status->isSuccess) {
						if ( isset($searchItemResponse->searchResult->recordList->record['0']->internalId) ){
							foreach ( $searchItemResponse->searchResult->recordList->record as $rec ){
								foreach ( $rec->customFieldList->customField as $custField ){
									if ( $custField->internalId == $asinNumCustomField ){
										$multiASINVal	= $custField->value;
										$eachASINVal	= str_replace(' ', '', explode(",", $multiASINVal));
										foreach ( $eachASINVal as $asinValue ){
											if ( $asinValue == $item->SKU ){
												$nsItemArray[$items_count]['internal_id'] = $rec->internalId;
												$nsItemArray[$items_count]['unitPrice'] = $item->UnitPrice * $item->Quantity + $item->TaxCost + $item->ShippingCost + $item->ShippingTaxCost + $item->GiftWrapCost + $item->GiftWrapTaxCost;
												$nsItemArray[$items_count]['quantity'] = $item->Quantity;
												break;
											}
										}
										if( isset($nsItemArray[$items_count]['internal_id']) ){
											break;
										}
									}
								}									
								if( isset($nsItemArray[$items_count]['internal_id']) ){
									break;
								}
							}
						}else{
							$nsItemArray[$items_count]['internal_id'] = '12085';
							$nsItemArray[$items_count]['unitPrice'] = $item->UnitPrice * $item->Quantity + $item->TaxCost + $item->ShippingCost + $item->ShippingTaxCost + $item->GiftWrapCost + $item->GiftWrapTaxCost;
							$nsItemArray[$items_count]['quantity'] = $item->Quantity;
						}
					}else{
						$continue = true;
					}
				}
			}else{
				$continue = true;
			}
			$items_count++;
		}
	}else{
		$ebayUserId = $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->BuyerUserID;
		$priority = '1';
		$searchItem = new ItemSearch();
		$asinCustomField = new SearchStringCustomField();
		$asinCustomField->searchValue = $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->SKU;
		$asinCustomField->internalId = $asinNumCustomField;
		$asinCustomField->operator = "is";    
		$searchItem->basic->customFieldList->customField = array($asinCustomField);
		$requestItemsearch = new SearchRequest();
		$requestItemsearch->searchRecord = $searchItem;
		$searchItemResponse = $service->search($requestItemsearch);
		if ($searchItemResponse->searchResult->status->isSuccess) {
			if ( isset($searchItemResponse->searchResult->recordList->record['0']->internalId) ){
				$nsItemArray[0]['internal_id'] = $searchItemResponse->searchResult->recordList->record['0']->internalId;
                $nsItemArray[0]['unitPrice'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->UnitPrice * $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->TaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingTaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapTaxCost;
				$nsItemArray[0]['quantity'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity;
			}else{
				$searchItem = new ItemSearch();
				$asinCustomField = new SearchStringCustomField();
				$asinCustomField->searchValue = $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->SKU;
				$asinCustomField->internalId = $asinNumCustomField;
				$asinCustomField->operator = "contains";    
				$searchItem->basic->customFieldList->customField = array($asinCustomField);
				$requestItemsearch = new SearchRequest();
				$requestItemsearch->searchRecord = $searchItem;
				$searchItemResponse = $service->search($requestItemsearch);
				if ($searchItemResponse->searchResult->status->isSuccess) {
					if ( isset($searchItemResponse->searchResult->recordList->record['0']->internalId) ){
						foreach ( $searchItemResponse->searchResult->recordList->record as $rec ){
							foreach ( $rec->customFieldList->customField as $custField ){
								if ( $custField->internalId == $asinNumCustomField ){
									$multiASINVal	= $custField->value;
									$eachASINVal	= str_replace(' ', '', explode(",", $multiASINVal));
									foreach ( $eachASINVal as $asinValue ){
										if ( $asinValue == $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->SKU ){
											$nsItemArray[0]['internal_id'] = $rec->internalId;
											$nsItemArray[0]['unitPrice'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->UnitPrice * $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->TaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingTaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapTaxCost;
											$nsItemArray[0]['quantity'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity;
											break;
										}
									}
									if( isset($nsItemArray[0]['internal_id']) ){
										break;
									}
								}
							}									
							if( isset($nsItemArray[0]['internal_id']) ){
								break;
							}
						}
					}else{
						$nsItemArray[0]['internal_id'] = 12085;
						$nsItemArray[0]['unitPrice'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->UnitPrice * $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->TaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->ShippingTaxCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapCost + $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->GiftWrapTaxCost;
						$nsItemArray[0]['quantity'] =  $orderArray[$j]->ShoppingCart->LineItemSKUList->OrderLineItemItem->Quantity;
					}
				}else{
					$continue = true;
				}
			}
		}else{
			$continue = true;
		}
	}

	if ( $continue ) {
		continue;
	}

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
        
        if ( $customer->firstName == '' ){
            $customer->firstName = "unknown";
        }

		if (strlen($customer->firstName) > 32){
            $customer->firstName = substr($customer->firstName, 0, 29) . '...';
        }

        if ( $customer->lastName == '' ){
            $customer->lastName = "unknown";
        }
        
		if (strlen($customer->lastName) > 32){
            $customer->lastName = substr($customer->lastName, 0, 29) . '...';
        }

        $customer->isPerson = "individual";        
        if ( filter_var($orderArray[$j]->BuyerEmailAddress, FILTER_VALIDATE_EMAIL) ){			
			if ( substr($orderArray[$j]->BuyerEmailAddress, -2) != 'mm' || substr($orderArray[$j]->BuyerEmailAddress, -3) != 'con' || substr($orderArray[$j]->BuyerEmailAddress, -2) != 'ed' ){
				$customer->email = $orderArray[$j]->BuyerEmailAddress;
			}else{
				$customer->email = 'noemail@golfoutletsusa.com';
			}
		}else{
            $customer->email = 'noemail@golfoutletsusa.com';
        }
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
			$phoneValue											= $orderArray[$j]->ShippingInfo->PhoneNumberDay;
			$phoneValue											= $orderArray[$j]->ShippingInfo->PhoneNumberDay;
		}else if ( $orderArray[$j]->ShippingInfo->PhoneNumberEvening != '' && $orderArray[$j]->ShippingInfo->PhoneNumberEvening != null ){
			$phoneValue											= $orderArray[$j]->ShippingInfo->PhoneNumberEvening;
			$phoneValue											= $orderArray[$j]->ShippingInfo->PhoneNumberEvening;
		}
		$phoneValue = str_replace(' ', '', $phoneValue);
		if (strlen($phoneValue) > 22){
            $phoneValue = substr($phoneValue, 0, 22);
        }
		$customer->phone									= $phoneValue;
		$customer->addressbookList->addressbook['phone']	= $phoneValue;
		$customer->addressbookList->replaceAll = 1;

// ------------------------------------------------------------  -----------  -------------------------------------------------------------

		$customEbayUserIDField = new StringCustomFieldRef();
		$customEbayUserIDField->internalId = 'custentity_splc_ebay_user_id';
		$customEbayUserIDField->value = $ebayUserId;

		$customer->customFieldList->customField = array($customEbayUserIDField);

        $requestCustomerInsert = new AddRequest();
        $requestCustomerInsert->record = $customer;

        $responseCustomerInsert = $service->add($requestCustomerInsert);

        if (!$responseCustomerInsert->writeResponse->status->isSuccess) {
            echo "--------------------------------------------------------------------------<br>";
            echo "ADD CUSTOMER ERROR<br>";
            echo "--------------------------------------------------------------------------<br>";
            $connector->addCustomerErrorAlert($emailAddress, $orderArray[$j]->ClientOrderIdentifier, json_encode($responseCustomerInsert->writeResponse), "AU");
			$customerInternalId = 'error';
        } else {
            $customerInternalId = $responseCustomerInsert->writeResponse->baseRef->internalId;
        }
    }else{
        $customerInternalId = $searchResponseCustomer->searchResult->recordList->record['0']->internalId;
		$customerRec = $searchResponseCustomer->searchResult->recordList->record['0'];

		$customer->firstName = $orderArray[$j]->ShippingInfo->FirstName;
        $customer->lastName  = $orderArray[$j]->ShippingInfo->LastName;
        
        if ( $customer->firstName == '' ){
            $customer->firstName = "unknown";
        }

		if (strlen($customer->firstName) > 32){
            $customer->firstName = substr($customer->firstName, 0, 29) . '...';
        }

        if ( $customer->lastName == '' ){
            $customer->lastName = "unknown";
        }
        
		if (strlen($customer->lastName) > 32){
            $customer->lastName = substr($customer->lastName, 0, 29) . '...';
        }

		if ( $orderArray[$j]->ShippingInfo->CompanyName != '' ){
			$addressee = $customer->firstName . ' ' . $customer->lastName;
		}else{
			$addressee = $orderArray[$j]->ShippingInfo->CompanyName;
		}

		$addr1	= $orderArray[$j]->ShippingInfo->AddressLine1;                              
        $addr2	= $orderArray[$j]->ShippingInfo->AddressLine2;                               
        $city	= $orderArray[$j]->ShippingInfo->City;                               
        $country= $connector->country_code_arr[$orderArray[$j]->ShippingInfo->CountryCode];
        $state	= $orderArray[$j]->ShippingInfo->Region;                               
        $zip	= $orderArray[$j]->ShippingInfo->PostalCode;
		if ( $orderArray[$j]->ShippingInfo->PhoneNumberDay != '' && $orderArray[$j]->ShippingInfo->PhoneNumberDay != null ){
			$phone = $orderArray[$j]->ShippingInfo->PhoneNumberDay;
		}else if ( $orderArray[$j]->ShippingInfo->PhoneNumberEvening != '' && $orderArray[$j]->ShippingInfo->PhoneNumberEvening != null ){
			$phone = $orderArray[$j]->ShippingInfo->PhoneNumberEvening;
		}

		$phone = str_replace(' ', '', $phone);
		if (strlen($phone) > 22){
            $phone = substr($phone, 0, 22);
        }

		$i = 0;
		$matchAddrLine = '-1';
		if ( count($customerRec->addressbookList->addressbook) > 0 ){
			foreach( $customerRec->addressbookList->addressbook as $addressBook ){
				if( $addressBook->addressee == $addressee && $addressBook->phone == $phone && $addressBook->addr1 == $addr1 && $addressBook->addr2 == $addr2 && $addressBook->country == $country && $addressBook->city == $city && $addressBook->zip == $zip && $addressBook->state == $state ){
					$matchAddrLine = (int)$i;
					break;
				}
				$i++;
			}
		}

		$updateCustomer = new Customer();

		if( $matchAddrLine != '-1' && count($customerRec->addressbookList->addressbook) == 1 ){
			
		}else if ( $matchAddrLine == '-1' ){
			$matchAddrLine = count($customerRec->addressbookList->addressbook);
			$updateCustomer->internalId = $customerRec->internalId;
			$updateCustomer->addressbookList = $customerRec->addressbookList;
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->addressee = $addressee;                               
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->addr1 = $addr1;                               
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->addr2 = $addr2;                               
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->city = $city;                               
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->country = $country;
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->state = $state;                               
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->zip = $zip;
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->defaultShipping = true;
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->defaultBilling = true;
			if ( strlen((string)$orderArray[$j]->ShippingAddress->Phone) > 9 ){
				$updateCustomer->addressbookList->addressbook[$matchAddrLine]->phone = $phone;
			}
			$requestCustomerUpdate = new UpdateRequest();
			$requestCustomerUpdate->record = $updateCustomer;

			$responseCustomerUpdate = $service->update($requestCustomerUpdate);
		}else{
			$updateCustomer->internalId = $customerRec->internalId;
			$updateCustomer->addressbookList = $customerRec->addressbookList;
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->defaultShipping = true;
			$updateCustomer->addressbookList->addressbook[$matchAddrLine]->defaultBilling = true;
			$requestCustomerUpdate = new UpdateRequest();
			$requestCustomerUpdate->record = $updateCustomer;

			$responseCustomerUpdate = $service->update($requestCustomerUpdate);
			if (!$responseCustomerUpdate->writeResponse->status->isSuccess) {
				echo "--------------------------------------------------------------------------<br>";
				echo "UPDATE CUSTOMER ERROR<br>";
				echo "--------------------------------------------------------------------------<br>";
				$connector->updateCustomerErrorAlert($emailAddress, $orderArray[$j]->ClientOrderIdentifier, json_encode($responseCustomerInsert->writeResponse), "AU");
				$customerInternalId = 'error';
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

	$customPhoneLandlineField = new StringCustomFieldRef();
	$customPhoneLandlineField->internalId = 'custbody_splc_mobile_phone';
	$customPhoneLandlineField->value = $orderArray[$j]->ShippingInfo->PhoneNumberDay;

	$customPhoneMobileField = new StringCustomFieldRef();
	$customPhoneMobileField->internalId = 'custbody_splc_landline_hone';
	$customPhoneMobileField->value = $orderArray[$j]->ShippingInfo->PhoneNumberEvening;

	$customPriorityField = new SelectCustomFieldRef();
	$customPriorityField->internalId = 'custbody_nswmspriority';
	$customPriorityField->value = new ListOrRecordRef();
	$customPriorityField->value->typeId = 'Order Priority';
	if ( $priorities[$j] == '-2' ){
		$customPriorityField->value->internalId = '4';
	}else{
		$customPriorityField->value->internalId = $priority;	
	}

	$customEbayUserIDField = new StringCustomFieldRef();
	$customEbayUserIDField->internalId = 'custbody_splc_ebay_user_id';
	$customEbayUserIDField->value = $ebayUserId;

	$so->customFieldList->customField = array($customCompanyField, $customSecReferenceField, $customPriorityField, $customAccountIndexField, $customEbayUserIDField, $customPhoneLandlineField, $customPhoneMobileField);
	
	$shippingCost = 0.00;

	$so->shipMethod->internalId = $shipMethod;	

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
			$soi->location->internalId = $location;

			$itemStatus = new SelectCustomFieldRef();
				
			$itemStatus->internalId = 'custcol_ebiznet_item_status';
			$itemStatus->value = new ListOrRecordRef();
			$itemStatus->value->typeId = 'Item Status';
			$itemStatus->value->internalId = $itemStocks;

			$create_fulfillment_order = new BooleanCustomFieldRef();

			$create_fulfillment_order->internalId = 'custcol_create_fulfillment_order';
			$create_fulfillment_order->value = '1';
			
			$soi->customFieldList->customField = array($itemStatus, $create_fulfillment_order);

			
			$soi->grossAmt = floatval($itemArray['unitPrice']);
			
			$so->itemList->item[] = $soi;
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
		$delivery->location->internalId = $location;
		
		$itemStatus = new SelectCustomFieldRef();
			
		$itemStatus->internalId = 'custcol_ebiznet_item_status';
		$itemStatus->value = new ListOrRecordRef();
		$itemStatus->value->typeId = 'Item Status';
		$itemStatus->value->internalId = $itemStocks;

		$create_fulfillment_order = new BooleanCustomFieldRef();

		$create_fulfillment_order->internalId = 'custcol_create_fulfillment_order';
		$create_fulfillment_order->value = '1';
		
		$delivery->customFieldList->customField = array($itemStatus, $create_fulfillment_order);

		$delivery->grossAmt = $shippingCost;

		$so->itemList->item[] = $delivery;
	}

	$so->shippingCost = 0.00;

	$so_amount = 0.00;
		
	foreach($so->itemList->item as $orderItemsCreatedSalesOrder){
		$so_amount += floatval($orderItemsCreatedSalesOrder->grossAmt);
	}

	$requestImport = new AddRequest();

	$requestImport->record = $so;

	$responseImportSO = $service->add($requestImport);

	if (!$responseImportSO->writeResponse->status->isSuccess) {
		echo "--------------------------------------------------------------------------<br>";
		echo "Add Sales Order Error<br>";
		echo "--------------------------------------------------------------------------<br>";
		print_r($responseImportSO);
		if ( !$responseImportSO->writeResponse->status->statusDetail[0]->code == 'JS_EXCEPTION' ){
			$connector->sendMailWithAlert($emailAddress, $orderArray[$j]->ClientOrderIdentifier, $responseImportSO->writeResponse->status->statusDetail[0]->message, "chanAU");
		}
	} else {
		$soInternalId = $responseImportSO->writeResponse->baseRef->internalId;
		
		echo "<br>--------------------------------------------------------------------------<br>";
		echo "Add Sales Order Success, id = " . $soInternalId . "<br>";
		echo "<br>--------------------------------------------------------------------------<br>";
		
		$cuDepoCustomer		= $customerInternalId;
		$cuDepoAmount		= $so_amount;
		$cuDepoLocation		= $so->location->internalId;
		$cuDepoCurrency		= $currency;
		$cuDepoMemo			= $memo;
		$cuDepoChannel		= $salesChannel;
		$cuDepoPayment		= $orderArray[$j]->PaymentInfo->PaymentType;
		$cuDepoPaymentId	= $orderArray[$j]->PaymentInfo->PaymentTransactionID;

		// create customer deposit record list
		$customerDepositRecArr[] = $connector->customerDepositModule( $soInternalId, $cuDepoCustomer, $cuDepoAmount, $cuDepoLocation, $cuDepoCurrency, $cuDepoMemo, $cuDepoChannel, $cuDepoPayment, $cuDepoPaymentId);
	}
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
		echo "ADD CUSTOMER DEPOSIT RECORD ERROR. CA AU ID: " .$orderArray[$t]->ClientOrderIdentifier ."<br>";
		if ( $response && $response->status->statusDetail[0]->code != 'JS_EXCEPTION' ){
			$connector->sendMailWithAlert($emailAddress, $orderArray[$t]->ClientOrderIdentifier, json_encode($response), "chanAU");
		}
		echo "<br>--------------------------------------------------------------------------<br>";
	}
	$t++;
}
?>