<?php

class Ewave_Temando_Model_Observer
{
    
    /**
     * Handles sales_order_place_after.
     */
    public function createTemandoShipment(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        /* @var $order Mage_Sales_Model_Order */

        $__t = explode('_', $order->getShippingMethod());
        if ($__t[0] != 'temando') {
            return;
        }

        if (!Mage::helper('temando')->getConfigData('options/show_name_time')) {
            $order->setShippingDescription(Mage::helper('temando')->getConfigData('options/shown_name'))->save();
        }

        $selected_quote_id = preg_replace('#^[^_]*_#', '', $order->getShippingMethod());
        $selected_options = preg_replace('#^([^_]*_){2}#', '', $order->getShippingMethod());
	
	//check for rule engine
	$exploded = explode('_', $selected_options);
	if(count($exploded) % 2 != 0) {
	    $ruleId = preg_replace('/[^0-9]*/','',$selected_options);
	    $selected_options = preg_replace('#^([^_]*_){3}#', '', $order->getShippingMethod());
	}
	
	$selected_quote = Mage::getModel('temando/quote')->load($selected_quote_id);
	if(!$selected_quote->getId()) {
	    //try loading cheapeast quote
	    try {
		$selected_quote = $this->loadCheapestQuote($order, Mage::helper('temando')->getOrigin());
		/* @var $selected_quote Ewave_Temando_Model_Quote */
	    } catch (Exception $e) {
		$selected_quote = null;
	    }    
	}
	
	$temando_shipment = Mage::getModel('temando/shipment');
	/* @var $temando_shipment Ewave_Temando_Model_Shipment */
	
	if(Mage::helper('temando')->isQuoteDynamic($selected_quote_id)) {
	    //DYNAMIC: carrier quote selected by customer - must have at least 1 available quote
	    $temando_shipment->setCustomerSelectedQuoteId($selected_quote->getId())
			     ->setCustomerSelectedOptions($selected_options);
	    $selected_quote = $temando_shipment->getSelectedQuotePermutation();
	    $temando_shipment
                    ->setCustomerSelectedQuoteDescription($selected_quote->getDescription(true))
                    ->setAdminSelectedQuoteId($selected_quote->getId())
                    ->setAnticipatedCost($selected_quote->getTotalPrice());
		    
	} else {
	    //STATIC: flat rate / free shipping selected by customer
	    $temando_shipment->setCustomerSelectedQuoteDescription('Flat Rate / Free Shipping')
			     ->setCustomerSelectedOptions('insurance_N_carbonoffset_N');
	    
	    if(!is_null($selected_quote)) {
		//set cheapest as admin selected
		$temando_shipment->setAdminSelectedQuoteId($selected_quote->getId())
				 ->setAnticipatedCost($selected_quote->getTotalPrice());
	    }
	}
        
        $email = $order->getShippingAddress()->getEmail();
        if(!$email) {
            $email = $order->getCustomerEmail();
        }
	
	$warehouse_id = null;
	if(Mage::helper('temando')->isVersion2()) {
	    $origin = Mage::helper('temando/v2')->getDynamicOrigin($order->getShippingAddress()->getPostcode(), $order->getStoreId());
	    $warehouse_id = $origin->getId();
	}

        $temando_shipment
            ->setOrderId($order->getId())
	    ->setStoreId($order->getStoreId())
            ->setStatus(Ewave_Temando_Model_System_Config_Source_Shipment_Status::PENDING)
            ->setDestinationContactName($order->getShippingAddress()->getName())
            ->setDestinationCompanyName($order->getShippingAddress()->getCompany())
            ->setDestinationStreet(str_replace("\n", ', ', $order->getShippingAddress()->getStreetFull()))
            ->setDestinationRegion($order->getShippingAddress()->getRegionCode())
            ->setDestinationPhone($order->getShippingAddress()->getTelephone())
            ->setDestinationEmail($email)
            ->setDestinationCountry($order->getShippingAddress()->getCountryId())
            ->setDestinationPostcode($order->getShippingAddress()->getPostcode())
            ->setDestinationCity($order->getShippingAddress()->getCity())
            ->setReadyTime('AM')
	    ->setWarehouseId($warehouse_id)
            ->save();
            
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getFreeShipping()) {
                $has_free = true;
                continue;
            }

            /* @var $order Mage_Sales_Model_Order */
            
            $product = Mage::getModel('catalog/product')
                ->load($item->getProductId());
            /* @var $product Mage_Catalog_Model_Product */

            if ($product->isVirtual()) {
                continue;
            }
	    
	    $packages = array();
	    if(Mage::helper('temando')->isVersion2()) {
		$packages = Mage::helper('temando/v2')->getProductPackages($item, $product);
	    } else {
		Mage::helper('temando')->applyTemandoParamsToProductByItem($item, $product);
	    }
	    
	    $qty = $item->getQty();
	    if(!$qty) $qty = $item->getQtyOrdered();
	    
	    if(!empty($packages)) {
		//version 2 - multi-package
		$part = round($item->getRowTotalInclTax() / count($packages), 2);
		$sub = 0.00; $i=0;
		foreach($packages as $package) {
			$i++;
		    $box = Mage::getModel('temando/box');
		    $box
		        ->setShipmentId($temando_shipment->getId())
			->setComment($package['description'])
			->setQty($qty)
			->setValue($i == count($packages) ? $item->getRowTotalInclTax() - $sub : $part) //TODO: add attribute price - need fraction
			->setLength($package['length'])
			->setWidth($package['width'])
			->setHeight($package['height'])
			->setMeasureUnit(Mage::helper('temando/v2')->getConfigData('units/measure'))
			->setWeight($package['weight'])
			->setWeightUnit(Mage::helper('temando/v2')->getConfigData('units/weight'))
			->setPackaging($package['packaging'])
			->setFragile($package['fragile'])
			->save();
		    $sub += $part;
		}
	    } else {
		$box = Mage::getModel('temando/box');
		/* @var $box Ewave_Temando_Model_Box */
		$box
		    ->setShipmentId($temando_shipment->getId())
		    ->setComment($product->getName())
		    ->setQty($item->getQty())
		    ->setValue($item->getRowTotalInclTax())
		    ->setLength($product->getTemandoLength())
		    ->setWidth($product->getTemandoWidth())
		    ->setHeight($product->getTemandoHeight())
		    ->setMeasureUnit(Mage::helper('temando')->getConfigData('units/measure'))
		    ->setWeight($product->getWeight()*$qty)
		    ->setWeightUnit(Mage::helper('temando')->getConfigData('units/weight'))
		    ->setPackaging($product->getTemandoPackaging())
		    ->setFragile($product->getTemandoFragile())
		    ->save();
	    }
        }
    }
    
    protected function loadQuotes($order, $origin)
    {
        // Load quotes
        $allowed_carriers = explode(',', Mage::getStoreConfig('carriers/temando/allowed_methods'));
        
        $request = Mage::getModel('temando/api_request');
        /* @var $request Ewave_Temando_Model_Api_Request */
        $request
            ->setUsername(Mage::helper('temando')->getConfigData('general/username'))
            ->setPassword(Mage::helper('temando')->getConfigData('general/password'))
            ->setSandbox(Mage::helper('temando')->getConfigData('general/sandbox'))
            ->setOrigin(
                $origin->getCountry(),
                $origin->getPostcode(),
                $origin->getCity(),
                $origin->getType())
            ->setDestination(
                $order->getDestCountryId(),
                $order->getDestPostcode(),
                $order->getDestCity(),
                $order->getDestStreet())
            ->setItems($order->getAllItems())
            ->setReady()
            ->setAllowedCarriers($allowed_carriers);
        
        // reset gets the first element of the returned array
        return reset($request->getCheapestQuotes());
    }
    
    protected function loadCheapestQuote($order, $origin)
    {
        // Load quotes
        $allowed_carriers = explode(',', Mage::getStoreConfig('carriers/temando/allowed_methods'));
        
	if(Mage::helper('temando')->isVersion2()) {
	    $request = Mage::getModel('temando/api_v2_request');
	    $origin = Mage::helper('temando/v2')->getDynamicOrigin($order->getShippingAddress()->getPostcode(), $order->getStoreId());
	} else {
	    $request = Mage::getModel('temando/api_request');
	}
        /* @var $request Ewave_Temando_Model_Api_Request */
        $request
            ->setUsername(Mage::helper('temando')->getConfigData('general/username'))
            ->setPassword(Mage::helper('temando')->getConfigData('general/password'))
            ->setSandbox(Mage::helper('temando')->getConfigData('general/sandbox'))
	    ->setMagentoQuoteId($order->getQuoteId())
            ->setOrigin(
                $origin->getCountry(),
                $origin->getPostcode(),
                $origin->getCity(),
                $origin->getType() ? $origin->getType() : $origin->getLocationType())
            ->setDestination(
                $order->getShippingAddress()->getCountry(),
                $order->getShippingAddress()->getPostcode(),
                $order->getShippingAddress()->getCity())
            ->setItems($order->getAllItems())
            ->setReady()
            ->setAllowedCarriers($allowed_carriers);

	$cheapest = $request->getCheapestQuote();
	return $cheapest;
	
    }
    

    public function hookCartSaveAddress($observer)
    {
        $post = $observer->getControllerAction()->getRequest()->getPost();
        if (Mage::getStoreConfig('carriers/temando/active') && isset($post['country_id']) && ('AU' == $post['country_id']) && isset($post['region_id']) && isset($post['estimate_city']) && isset($post['estimate_postcode']) && isset($post['pcs'])) {
            $data = array(
                'country_id' => $post['country_id'],
                'region_id' => $post['region_id'],
                'city' => $post['estimate_city'],
                'postcode' => $post['estimate_postcode'],
                'pcs' => $post['pcs'],
            );
            Mage::getSingleton('customer/session')->setData('estimate_product_shipping', new Varien_Object($data));
        }
    }
    
}
