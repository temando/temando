<?php

class Ewave_Temando_Model_Shipping_Carrier_Temando extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    
    const ERR_INVALID_COUNTRY = 'To and From addresses must be within Australia';
    const ERR_INVALID_DEST    = 'Please enter a delivery address to view available shipping methods';
    const ERR_NO_METHODS      = 'No shipping methods available';
    
    protected $_code = 'temando';

    protected static $_errors_map = array(
        "The 'destinationCountry', 'destinationCode' and 'destinationSuburb' elements (within the 'Anywhere' type) do not contain valid values.  These values must match with the predefined settings in the Temando system."
                => "Invalid suburb / postcode combination."
    );
    
    /**
     * @var Mage_Shipping_Model_Rate_Request
     */
    protected $_rate_request;
    
    /**
     * @var Ewave_Temando_Helper_Data
     */
    protected $_helper;
    
    protected $_pricing_method;
    protected $_username;
    protected $_password;
    protected $_sandbox;
    
    protected $_origin;
    
    public function isTrackingAvailable()
    {
        return true;
    }
    
    
    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::helper('temando');
        
        $this->_pricing_method = $this->getConfigData('pricing/method');
        $this->_username = $this->getConfigData('general/username');
        $this->_password = $this->getConfigData('general/password');
        $this->_sandbox = $this->getConfigData('general/sandbox');
    }
    
    /**
     * Checks if the to and from addresses are within Australia.
     *
     * @return boolean
     */
    protected function _isInAustralia()
    {
        $origCountry = $this->getConfigData('origin/country');
        return ($origCountry == "AU" && $this->_rate_request->getDestCountryId() == "AU");
    }
    
    /**
     * Creates the flat rate method, with the price set in the config. An
     * optional parameter allows the price to be overridden.
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getFlatRateMethod($price = false, $free = false)
    {
        if (true === $this->_rate_request->getFreeShipping()) {
            $price = 0;
            $free = true;
        }

        if ($price === false) {
            $cost = $this->getConfigData('pricing/shipping_fee');
            $price = $this->getFinalPriceWithHandlingFee($cost);
        } else {
            $cost = $price;
        }


        $title = $this->getConfigData('options/shown_name');
        if ($this->getConfigData('options/show_name_time')) {
            $title = $free ? 'Free Shipping' : 'Flat Rate';
        }

        $method = Mage::getModel('shipping/rate_result_method')
            ->setCarrier($this->_code)
            ->setCarrierTitle($this->getConfigData('carriers/temando/title'))
            ->setMethodTitle($free ? 'Free Shipping' : $title)
            ->setMethod($free ? '10000' : '10001')
            ->setPrice($price)
            ->setCost($cost);
            
        return $method;
    }

    protected function _getErrorMethod($errorText)
    {
        $error = Mage::getModel('shipping/rate_result_error');
        $error->setCarrier('temando');
        $error->setCarrierTitle($this->getConfigData('carriers/temando/title'));
        if (isset(self::$_errors_map[$errorText])) {
            $errorText = self::$_errors_map[$errorText];
        }

        $error->setErrorMessage($errorText);

        return $error;
    }
    
    /**
     * Creates a rate method based on a Temando API quote.
     *
     * @param Mage_Shipping_Model_Rate_Result_Method the quote from the
     * Temando API.
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getRateMethodFromQuote($quote, $method_id)
    {
        $carrier = $quote->getCarrier();
        $price = $this->getFinalPriceWithHandlingFee($quote->getTotalPrice());
        $title = $this->getConfigData('options/shown_name');
        if (Mage::getSingleton('admin/session')->isLoggedIn() || $this->getConfigData('options/show_name_time')) {
            $title = $quote->getDescription($this->getConfigData('options/show_carrier_names'));
        }

        $method = Mage::getModel('shipping/rate_result_method')
            ->setCarrier($this->_code)
            ->setCarrierTitle($this->getConfigData('carriers/temando/title'))
            ->setMethodTitle($title)
            ->setMethod($method_id)
            ->setPrice($price)
            ->setCost($quote->getTotalPrice());
        
        return $method;
    }
    
    /**
     * Creates a string describing the applicable elements of a rate request.
     *
     * This is used to determine if the quotes fetched last time should be
     * refreshed, or if they can remain valid.
     *
     * @param Mage_Shipping_Model_Rate_Request $rate_request
     *
     * @return boolean
     */
    protected function _createRequestString(Mage_Shipping_Model_Rate_Request $rate_request)
    {
        $request_string = Mage::getModel('checkout/session')
            ->getQuote()->getId() . '|';
        foreach ($rate_request->getAllItems() as $item) {
            $request_string .= $item->getProductId() . 'x' . $item->getQty();
        }
        
        $request_string .= '|' . $rate_request->getDestCity();
        $request_string .= '|' . $rate_request->getDestCountryId();
        $request_string .= '|' . $rate_request->getDestPostcode();
        $request_string .= '|' . $rate_request->getDestStreet();
        return $request_string;
    }
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $rate_request)
    {
	
	$isProductPage = (("etemando" == Mage::app()->getRequest()->getModuleName()) && ("pcs" == Mage::app()->getRequest()->getControllerName()));
	$isCartPage = (("checkout" == Mage::app()->getRequest()->getModuleName()) && ("cart" == Mage::app()->getRequest()->getControllerName()));
	
        $this->_rate_request = $rate_request;
        $result = Mage::getModel('shipping/rate_result');
	/* @var $result Mage_Shipping_Model_Rate_Result */

        if (!$this->_isInAustralia()) { return $result->setError(self::ERR_INVALID_COUNTRY); }

        if (!$rate_request->getDestCountryId() || !$rate_request->getDestPostcode() || !$rate_request->getDestCity()) {
	    return $this->_getErrorMethod(self::ERR_INVALID_DEST);
        }
	
        // Check all items are with free ship
        $has_paid = false;
        foreach ($rate_request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }

            if ($item->getFreeShipping()) {
                continue;
            }

            $has_paid = true;
        }

	//free shipping set or all items have free shipping
        if (!$has_paid || $this->getConfigData('free_shipping_enable') && 
		$this->isEligibleForFreeShipping($rate_request->getAllItems(), $this->getConfigData('free_shipping_subtotal')))
	{
            $this->_pricing_method = 'free';
        }

	//static methods - return immediately
        switch ($this->_pricing_method) {
            case 'flat': return $result->append($this->_getFlatRateMethod());
            case 'free': return $result->append($this->_getFlatRateMethod('0.00', true));
        }

	//prepare extras
        $insurance = Mage::getModel('temando/option_insurance')->setSetting(Mage::getStoreConfig('temando/insurance/status'));
        $carbon = Mage::getModel('temando/option_carbonoffset')->setSetting(Mage::getStoreConfig('temando/carbon/status'));
        
        if ($isProductPage || $isCartPage) 
	{
            if (!in_array($insurance->getForcedValue(), array(Ewave_Temando_Model_Option_Boolean::YES, Ewave_Temando_Model_Option_Boolean::NO))) {
                $insurance->setForcedValue(Ewave_Temando_Model_Option_Boolean::NO);
            }

            if (!in_array($carbon->getForcedValue(), array(Ewave_Temando_Model_Option_Boolean::YES, Ewave_Temando_Model_Option_Boolean::NO))) {
                $carbon->setForcedValue(Ewave_Temando_Model_Option_Boolean::NO);
            }
        }
        /* @var Ewave_Temando_Model_Options $options */
        $options = Mage::getModel('temando/options')->addItem($insurance)->addItem($carbon);

	//get magento quote id
        $magento_quote_id = Mage::getSingleton('checkout/session')->getQuoteId();
        if (!$magento_quote_id && Mage::getSingleton('admin/session')->isLoggedIn() && Mage::getSingleton('adminhtml/session_quote')->getQuote()) {
            $magento_quote_id = Mage::getSingleton('adminhtml/session_quote')->getQuote()->getId();
        }
        if ($isProductPage){ $magento_quote_id = 100000000 + mt_rand(0, 100000); }

	//save current extras
        if (is_null(Mage::registry('temando_current_options'))) {
            Mage::register('temando_current_options', $options);
        }

	//check if request same as previous
	$last_request = Mage::getSingleton('checkout/session')->getTemandoRequestString();

        if ($last_request == $this->_createRequestString($rate_request)) {
            // load existing quotes from DB instead
            $quotes = Mage::getModel('temando/quote')->getCollection()
                ->addFieldToFilter('magento_quote_id', Mage::getSingleton('checkout/session')->getTemandoQuoteId());
        } else {
            // fetch new quotes
            try {
		
		if(Mage::helper('temando')->isVersion2()) {
		    $request = Mage::getModel('temando/api_v2_request');		    
		    $this->_origin = Mage::helper('temando/v2')->getDynamicOrigin($rate_request->getDestPostcode(), Mage::app()->getStore()->getId());
		    //check if we have a origin
		    if(!$this->_origin) {
			throw new Exception('Missing mandatory origin data.');
		    }
		    $desc = $this->_origin->getName();
		} else {
		    $request = Mage::getModel('temando/api_request');
		    $this->_origin = Mage::helper('temando')->getOrigin();
		    $desc = Ewave_Temando_Helper_Data::DEFAULT_WAREHOUSE_NAME;
		}
		
                $request
                    ->setUsername($this->getConfigData('general/username'))
                    ->setPassword($this->getConfigData('general/password'))
                    ->setSandbox($this->getConfigData('general/sandbox'))
                    ->setMagentoQuoteId($magento_quote_id)
                    ->setOrigin(
                        $this->_origin->getCountry(),
                        $this->_origin->getPostcode(),
                        $this->_origin->getCity(),
                        $this->_origin->getType() ? $this->_origin->getType() : $this->_origin->getLocationType(),
			$desc)
                    ->setDestination(
                        $rate_request->getDestCountryId(),
                        $rate_request->getDestPostcode(),
                        $rate_request->getDestCity(),
                        $rate_request->getDestStreet())
                    ->setItems($rate_request->getAllItems())
                    ->setReady()
                    ->setAllowedCarriers($this->getAllowedMethods());

                $quotes = $request->getQuotes();
            } catch (Exception $ex) {
                if (Ewave_Temando_Model_System_Config_Source_Errorprocess::VIEW == Mage::helper('temando')->getConfigData('pricing/error_process')) {
                    return $this->_getErrorMethod($ex->getMessage());
                    return $result->append($this->_getErrorMethod($ex->getMessage()));
                }
                // return flat rate
                return $result->append($this->_getFlatRateMethod());
            }
        }
        
	// save quotes for use in the observer
        Mage::getSingleton('checkout/session')->setTemandoQuoteId($magento_quote_id);
	
	switch($this->_pricing_method) {
	    case Ewave_Temando_Model_System_Config_Source_Pricing::RULE_ENGINE: 
		
		$data = Mage::app()->getRequest()->getParams();
		if($isProductPage && isset($data['product_id'])) {
		    $product = Mage::getModel('catalog/product')->load($data['product_id']);
		    
		    if($product->getId()) {
			$subtotal = $product->getPrice() * $data['qty'];
		    }
		    $items = $data['qty'];
		} else {
		    $subtotal = $rate_request->getOrderSubtotal() ? $rate_request->getOrderSubtotal() : $rate_request->getPackageValue(); 
		    $items = $rate_request->getOrderTotalQty() ? $rate_request->getOrderTotalQty() : $rate_request->getPackageQty();
		}
		
		
		$engine = Mage::getModel('temando/hybrid');
		$engine->loadRules($rate_request->getPackageWeight(), $subtotal, $items, $rate_request->getDestPostcode());
		
		$err = array();
		$methods = $engine->getShippingMethods($err, $options, $quotes);
		if(!empty($err)) {
		    if (Ewave_Temando_Model_System_Config_Source_Errorprocess::VIEW == Mage::helper('temando')->getConfigData('pricing/error_process'))
		    {
			return $this->_getErrorMethod($err);
			return $result->append($this->_getErrorMethod($err));
		    } 
		    else 
		    {
			return $result->append($this->_getFlatRateMethod());
		    }
		} else {
		    foreach($methods as $method) {
			$result->append($method);
		    }
		}
		break;
	    
	    default: //DYNAMIC - all variants 
		//need quotes - check if we have some
		if (!$quotes || count($quotes) == 0) {
		    if (Ewave_Temando_Model_System_Config_Source_Errorprocess::VIEW == Mage::helper('temando')->getConfigData('pricing/error_process')) {
				return $this->_getErrorMethod(self::ERR_NO_METHODS);
				return $result->append($this->_getErrorMethod(self::ERR_NO_METHODS));
		    }
		    // return flat rate
		    return $result->append($this->_getFlatRateMethod());
		}
        
		//if not admin then filter display by pricing method
		foreach ($quotes as $method_id => $quote) {
		    if (!Mage::app()->getStore()->isAdmin()) {
			if (($this->_pricing_method == Ewave_Temando_Model_System_Config_Source_Pricing::DYNAMIC_CHEAPEST) &&
			    $quotes->getCheapest() && ($quote->getId() != $quotes->getCheapest()->getId())
			) {
			    continue;
			} else if (($this->_pricing_method == Ewave_Temando_Model_System_Config_Source_Pricing::DYNAMIC_FASTEST) &&
			    $quotes->getFastest() && ($quote->getId() != $quotes->getFastest()->getId())) {
			    continue;
			} else if (($this->_pricing_method == Ewave_Temando_Model_System_Config_Source_Pricing::DYNAMIC_FASTEST_AND_CHEAPEST)) {
			    if (($quote->getId() != $quotes->getFastest()->getId()) && ($quote->getId() != $quotes->getCheapest()->getId())) {
				continue;
			    }
			}
		    }
			    //get all quotes by extras
		    $permutations = $options->applyAll($quote);
		    foreach ($permutations as $permutation_id => $permutation) {
			$result->append($this->_getRateMethodFromQuote($permutation, $method_id . '_' . $permutation_id));
		    }
		}
		break;
	}

        Mage::getSingleton('checkout/session')->setTemandoRequestString($this->_createRequestString($this->_rate_request));
        return $result;
    }

    public function getAllowedMethods()
    {
        return explode(',', Mage::getStoreConfig('carriers/temando/allowed_methods'));
    }

    public function getTrackingInfo($tracking_number)
    {
        $api = Mage::getModel('temando/api_client');
        $api->connect(
            Mage::helper('temando')->getConfigData('general/username'),
            Mage::helper('temando')->getConfigData('general/password'),
            Mage::helper('temando')->getConfigData('general/sandbox'));

        $_t = explode('Request Id: ', $tracking_number);
        if (isset($_t[1])) {
            $tracking_number = $_t[1];
        }

        $status = $api->getRequest(array('requestId' => $tracking_number));
        
        $result = Mage::getModel('shipping/tracking_result_abstract')
            ->setTracking($tracking_number);
        /* @var $result Mage_Shipping_Model_Tracking_Result_Abstract */
        if ($status && $status->request->quotes && $status->request->quotes->quote) {
            if (isset($status->request->quotes->quote->carrier->companyName)) {
                $result->setCarrierTitle($status->request->quotes->quote->carrier->companyName);
            }

            if (isset($status->request->quotes->quote->trackingStatus)) {
                $result->setStatus($status->request->quotes->quote->trackingStatus);
            } else {
                $result->setStatus($this->_helper->__('Unavailable'));
            }
            
            $text = '';
            if (isset($status->request->quotes->quote->trackingFurtherDetails)) {
                $text .= $status->request->quotes->quote->trackingFurtherDetails;
            }
            if (isset($status->request->quotes->quote->trackingLastChecked)) {
                $text .= 'Last Update: ' . date('Y-m-d h:ia', strtotime($status->request->quotes->quote->trackingLastChecked));
            }
            
            if ($text) {
                $result->setTrackSummary($text);
            }
        } else {
            $result->setErrorMessage($this->_helper->__('An error occurred while fetching the shipment status.'));
        }
        
        return $result;
    }
    
    public function getConfigData($field)
    {
        if (in_array($field, array('handling_fee', 'handling_type'))) {
            $field = 'pricing/' . $field;
        }

        $parent = parent::getConfigData($field);
        return $parent !== null ? $parent : $this->_helper->getConfigData($field);
    }
    
    private function isEligibleForFreeShipping($items, $minimum)
    {
	if(empty($minimum))
	    return false;
	
	$goods_value = 0;
        
        foreach ($items as $item) {
	    
	    if ($item->getParentItem() || $item->getIsVirtual()) {
                // do not add child products or virtual items
                continue;
            }

            if ($item->getProduct() && $item->getProduct()->isVirtual()) {
                // do not add virtual product
                continue;
            }

            if ($item->getFreeShipping()) {
                continue;
            }
	    
            $value = $item->getValue();
            if (!$value) {
                $value = $item->getRowTotalInclTax();
            }
            if (!$value) {
                $value = $item->getRowTotal();
            }	    
            if (!$value) {
                $qty = $item->getQty();
                if (!$qty) {
                    $qty = $item->getQtyOrdered();
                }
                $value = $item->getPrice() * $qty;
            }
	    
            $goods_value += $value;
        }
	
	if($goods_value >= (float)$minimum)
	    return true;
	
	return false;
    }    
   
    
    public function getCode()
    {
        return $this->_code;
    }

    public function isStateProvinceRequired()
    {
        return true;
    }

    public function isCityRequired()
    {
        return true;
    }

    /**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     *
     * @return bool
     */
    public function isZipCodeRequired($countryId = null)
    {
        return true;
    }
    
}
