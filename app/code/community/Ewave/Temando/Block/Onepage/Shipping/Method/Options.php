<?php

class Ewave_Temando_Block_Onepage_Shipping_Method_Options extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    
    protected $_rates = null;
    
    public function getCode()
    {
        return Mage::getModel('temando/shipping_carrier_temando')->getCode();
    }
    
    public function getShippingRates()
    {
        if (!$this->_rates) {
            $groups = parent::getShippingRates();
            $this->_rates = $groups[$this->getCode()];
        }
        return $this->_rates;
    }
    
    /**
     * Gets all the permutations for a specific quote, given the options
     * available.
     *
     * @param Ewave_Temando_Model_Quote $quote
     */
    public function getPermutations($quote)
    {
        return $this->getOptions()->applyAll($quote);
    }
    
    public function getQuotes()
    {
        $quote_collection = Mage::getModel('temando/quote')->getCollection();
        /* @var $quote_collection Ewave_Temando_Model_Mysql4_Quote_Collection */
        $quote_collection->addFieldToFilter('magento_quote_id', $this->getQuote()->getId());
        
        $quotes = array();

        $rates = $this->getShippingRates();
        foreach ($quote_collection as $quote) {
            $found = false;
            foreach ($rates as $rate) {
                $_t = explode("_", $rate->getMethod());
                if ($_t[0] == $quote->getId()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                continue;
            }

            $carrier_name = strtolower(trim($quote->getCarrier()->getCompanyName()));
            
            if (!array_key_exists($carrier_name, $quotes)) {
                $quotes[$carrier_name] = array();
            }
            
            $quotes[$carrier_name][] = $quote;
        }
        
        ksort($quotes);
        
        return $quotes;
    }
    
    public function getRateFromPermutation($quote, $permutation_id)
    {
	($quote->getRuleId()) ? $ruleId = '_' . $quote->getRuleId() : $ruleId = '';
        foreach ($this->_rates as $rate) {
            if ($rate->getMethod() === $quote->getId().$ruleId. '_' . $permutation_id) {
                return $rate;
            }
        }
        return null;
    }
    
    public function getSole()
    {
        $groups = parent::getShippingRates();
        return count($groups) == 1 && count($groups[0]) == 1;
    }
    
    /**
     * @return Ewave_Temando_Model_Options
     */
    public function getOptions()
    {
        return Mage::registry('temando_current_options');
    }
    
    public function getDynamicRuleTitle($permutation) {
	$title = '';
	if($permutation->getRuleId()) {
	    $rule = Mage::getModel('temando/rule')->load($permutation->getRuleId());
	    if($rule->isDynamic()) {
		$title = $permutation->getDynamicDescriptionFromRule(
			    $rule->getActionDynamicShowCarrierName(),
			    $rule->getActionDynamicShowCarrierTime(),
			    $rule->getActionDynamicLabel()
			 );
	    }
	} else {
	    if(Mage::helper('temando')->getConfigData('options/show_name_time'))
	    {
		$title = $permutation->getDescription(false);
	    }
	    else
	    {
		$title = Mage::helper('temando')->getConfigData('options/shown_name');
	    }
	}
	
	return $title;
    }
    
}
