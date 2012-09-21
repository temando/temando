<?php

/**
 * @method Ewave_Temando_Model_Api_Request setMagentoQuoteId($quote_id)
 * @method string getMagentoQuoteId()
 */
class Ewave_Temando_Model_Api_Request extends Mage_Core_Model_Abstract
{
    
    /**
     * @var Ewave_Temando_Model_Api_Request_Anythings
     */
    protected $_anythings = null;
    
    /**
     * @var Ewave_Temando_Model_Api_Request_Anywhere
     */
    protected $_anywhere = null;
    
    /**
     * @var Ewave_Temando_Model_Api_Request_Anytime
     */
    protected $_anytime = null;
    
    /**
     * @var array
     */
    protected $_quotes = null;

    protected $use_anytime = false;
    
    protected $_allowed_carriers = null;
    protected $_last_request = null;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/api_request');
        
        $this->_anythings = Mage::getModel('temando/api_request_anythings');
        $this->_anywhere = Mage::getModel('temando/api_request_anywhere');
        $this->_anytime = Mage::getModel('temando/api_request_anytime');
    }
    
    public function setItems($items)
    {
        $this->_anythings->setItems($items);
        return $this;
    }
    
    public function setDestination($country, $postcode, $city, $street = null)
    {
        $this->_anywhere
            ->setDestinationCountry($country)
            ->setDestinationPostcode($postcode)
            ->setDestinationCity($city)
            ->setDestinationStreet($street);
        return $this;
    }
    
    public function setOrigin($country, $postcode, $city, $type, $desc = Ewave_Temando_Helper_Data::DEFAULT_WAREHOUSE_NAME)
    {
        $this->_anywhere
            ->setOriginCountry($country)
            ->setOriginPostcode($postcode)
            ->setOriginCity($city)
            ->setOriginType($type)
	    ->setOriginName($desc);
        return $this;
    }

    public function setReady($timestamp = null, $time_of_day = 'AM')
    {
        if (!is_null($timestamp)) {
            $this->use_anytime = true;
        }

        $this->_anytime
            ->setReadyDate($timestamp)
            ->setReadyTimeOfDay($time_of_day);
        return $this;
    }
    
    public function setAllowedCarriers($allowed_carriers)
    {
        if (is_array($allowed_carriers)) {
            $this->_allowed_carriers = $allowed_carriers;
        }
        return $this;
    }
    
    /**
     * Gets all available Temando quotes for this request.
     *
     * This should only be called once the parameters have been set. This
     * includes:
     *     - setDestination
     *     - setOrigin
     *     - setItems
     *     - setReady
     *     - setUseInsurance (optional)
     *     - setInsuranceOptional (optional)
     *     - setAllowedCarriers (optional)
     *
     * If the above conditions aren't met, it will return NULL.
     *
     * @return Ewave_Temando_Model_Mysql4_Quote_Collection
     */
    public function getQuotes()
    {
        if (!$this->_fetchQuotes()) {
            // validation failed
            return false;
        }
        
        $quotes = Mage::getModel('temando/quote')->getCollection()
            ->addFieldToFilter('magento_quote_id', $this->getMagentoQuoteId());
        
        return $quotes;
    }
    
    /**
     * Fetches the quotes and saves them into the database.
     *
     * @throws Exception
     */
    protected function _fetchQuotes()
    {
        $request_array = $this->toRequestArray();

        if (!$request_array) {
            return false;
        }

        if ($this->_last_request == $request_array) {
            // no change since last request; quotes in database are valid.
            return true;
        }

        try {
            $api = Mage::getModel('temando/api_client')
                ->connect(
                    $this->getUsername(),
                    $this->getPassword(),
                    $this->getSandbox()
                );

            $quotes = $api->getQuotesByRequest($request_array);
        } catch(Exception $e) {
            throw $e;
        }
        
        // filter by allowed carriers, if the filter has been set
        $filtered_quotes = $quotes;
        if (is_array($this->_allowed_carriers)) {
            $filtered_quotes = array();
            foreach ($quotes as $quote) {
                /* @var $quote Ewave_Temando_Model_Quote */
                $quote_carrier_id = $quote->getCarrier()->getCarrierId();
                if (in_array($quote_carrier_id, $this->_allowed_carriers)) {
                    $filtered_quotes[] = $quote;
                }
            }
        }
        
        $this->_saveQuotes($filtered_quotes);
        
        // save request so we can tell next time if it's changed
        $this->_last_request = $request_array;
        
        return true;
    }
    
    /**
     * Saves an array of quotes to the database.
     *
     * @param array $quotes an array of Ewave_Temando_Model_Quote objects.
     */
    protected function _saveQuotes($quotes)
    {
        // delete all old Temando quotes for this Magento quote
        $old_quotes = Mage::getModel('temando/quote')->getCollection()
            ->addFieldToFilter('magento_quote_id', $this->getMagentoQuoteId());
        foreach ($old_quotes as $quote) {
            /* @var $quote Ewave_Temando_Model_Quote */
            $quote->delete();
        }
        
        // add new Temando quotes to the database
        foreach ($quotes as $quote) {
            $quote->setMagentoQuoteId($this->getMagentoQuoteId())
                ->save();
        }
        
        return $this;
    }
    
    public function getCheapestQuote()
    {
	if(!$this->_fetchQuotes())
	    return false;
	
	$quotes = Mage::getModel('temando/quote')->getCollection()
            ->addFieldToFilter('magento_quote_id', $this->getMagentoQuoteId());
	
	$cheapestQuote = null;
	$first = true;
	foreach($quotes as $quote)
	{
	    if($first) {
		$cheapestQuote = $quote;
		$first = false;
	    } else {
		if($quote->getTotalPrice() < $cheapestQuote->getTotalPrice()) {
		    $cheapestQuote = $quote;
		}
	    }		
	}

	return $cheapestQuote;	
    }    
    
    public function toRequestArray()
    {
        if (!$this->validate()) {
            return false;
        }
        
        $goods_value = 0;
        
        foreach ($this->_anythings->getItems() as $anything) {
            $item = $anything->getItem();
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
        
        $return = array(
            'anythings' => $this->_anythings->toRequestArray(),
            'anywhere' => $this->_anywhere->toRequestArray(),
        );

        if ($goods_value) {
            $return['general'] = array(
                'goodsValue' => $goods_value,
            );
        }

        if ($this->use_anytime) {
            $return['anytime'] = $this->_anytime->toRequestArray();
        }

        return $return;
    }
    
    public function validate()
    {
        return
            $this->getMagentoQuoteId() &&
            $this->_anythings instanceof Ewave_Temando_Model_Api_Request_Anythings &&
            $this->_anywhere instanceof Ewave_Temando_Model_Api_Request_Anywhere &&
            $this->_anytime instanceof Ewave_Temando_Model_Api_Request_Anytime &&
            $this->_anythings->validate() &&
            $this->_anywhere->validate() &&
            $this->_anytime->validate();
    }
    
}
