<?php

class Ewave_Temando_Model_Hybrid extends Mage_Core_Model_Abstract
{
    /**
     * Pricing Method Code 
     */
    const METHOD_CODE  = 'hybrid';
    
    /**
     * Carrier Code 
     */
    const CARRIER_CODE = 'temando';
    
    /**
     * Error message - no methods
     */
    const ERR_NO_METHODS = 'No shipping methods available';
    
    /**
     * All valid rules filtered by current conditions
     * @var array 
     */
    protected $_validRules;
    
    
    /**
     * Flag if any of the valid rules are dynamic
     * @var boolean 
     */
    protected $_hasDynamic = null;
    
    
    /**
     * Flag if any of the valid rules is restrictive (shipping not allowed)
     * @var boolean 
     */
    protected $_hasRestrictive = null;
    
    
    /**
     * Text to display to customer when restrictive rule setup
     * @var string 
     */
    protected $_restrictiveNote = '';
    
    
    /**
     * @var Ewave_Temando_Helper_Data 
     */
    protected $_helper;
    
    /**
     * 
     */
    protected $_quotes;
    

    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/hybrid');
        $this->_validRules = array();
	$this->_helper = Mage::helper('temando');
    }
    
    /**
     * Loads all rules based on the current conditions (store, postal codes,
     * cart weight, number of items in cart, cart subtotal, current time)
     * 
     * @param float $weight
     * @param float $subtotal
     * @param int $items
     * @param int $pcode
     * @return \Ewave_Temando_Model_Hybrid 
     */
    public function loadRules($weight, $subtotal, $items, $pcode)
    {
	$collection = Mage::getModel('temando/rule')->getCollection();
	/* @var $collection Ewave_Temando_Model_Mysql4_Rule_Collection */
	
	$collection->addFieldToFilter('is_active', '1')
		   ->setOrder('priority', 'ASC');
	
	$store_id = Mage::app()->getStore(null)->getId();
	foreach($collection->getItems() as $rule) {
	    /* @var $rule Ewave_Temando_Model_Rule */
	    $store_ids = explode(',', $rule->getStoreIds());
	    if(in_array($store_id, $store_ids) && 
	       $rule->isValid($weight, $subtotal, $items, $pcode))
	    {
		$this->_validRules[] = $rule;
	    }
	}
	
	return $this;
    }
    
    /**
     * Checks if there is a dynamic rule which needs to be processed
     * 
     * @return boolean true if dynamic rule exist, 
     * false otherwise or when rules are not loaded
     */
    public function hasDynamic()
    {
	if(is_null($this->_hasDynamic))
	{
	    $this->_hasDynamic = false;

	    if(!is_null($this->_validRules) && count($this->_validRules)) {
		foreach($this->_validRules as $rule) {
		    if($rule->getActionRuleType() == Ewave_Temando_Model_System_Config_Source_Rule_Type::DYNAMIC)
		    {
			$this->_hasDynamic = true;
			break;
		    }
		}
	    }
	}
	
	return $this->_hasDynamic;
    }
    
    /**
     * Checks if there is a restrictive rule which cancels all
     * 
     * @return boolean true if restrictive rule exist, 
     * false otherwise or when rules are not loaded
     */
    public function hasRestrictive()
    {
	if(is_null($this->_hasRestrictive))
	{
	    $this->_hasRestrictive = false;

	    if(!is_null($this->_validRules) && count($this->_validRules)) {
		foreach($this->_validRules as $rule) {
		    if($rule->isRestrictive())
		    {
			$this->_hasRestrictive = true;
			$this->_restrictiveNote = $rule->getActionRestrictNote();
			break;
		    }
		}
	    }
	}
	
	return $this->_hasRestrictive;
    }
    
    
    public function getShippingMethods(&$error, $options, $quotes = null)
    {
	if(!$this->_validRules || !count($this->_validRules)) {
	    $error = self::ERR_NO_METHODS;
	    return;
	}

	if($this->hasRestrictive()) {
	    $error = $this->_restrictiveNote;
	    return;
	}

	//all good, get individual shipping methods
	$methods = array();
	$stopOnNext = false; $stopPriorityAfter = null;
	foreach($this->_validRules as $rule) {
	    /* @var $rule Ewave_Temando_Model_Rule */
	    
	    $priority = $rule->getPriority();
	    
	    //stop if previous rule has stopOther and higher priority
	    if($stopOnNext && $priority > $stopPriorityAfter) break;
	    
	    if($rule->isDynamic()) {
		//check if we have quotes
		if(is_null($quotes)) continue;
		
		$ruleQuotes = $this->_processDynamicRule($rule, $quotes);
		
		//check if we have methods
		if(!$ruleQuotes) continue;
		
		//process quotes - apply extras and create methods
		foreach($ruleQuotes as $ruleQuote) {
		    /* @var $ruleQuote Ewave_Temando_Model_Quote */
		    
		    $permutations = $options->applyAll($ruleQuote);
		    
		    foreach ($permutations as $permutation_id => $permutation) {
			$title = $this->_helper->getConfigData('options/shown_name');
			if ($this->_helper->getConfigData('options/show_name_time')) {
			    $title = $permutation->getDescription($this->_helper->getConfigData('options/show_carrier_names'));
			}
			$methods[]  = Mage::getModel('shipping/rate_result_method')
				    ->setCarrier(self::CARRIER_CODE)
				    ->setCarrierTitle(Mage::getStoreConfig('carriers/temando/title'))
				    ->setMethodTitle($title)
				    ->setMethod($ruleQuote->getId().'_'.$rule->getId(). '_' . $permutation_id)
				    ->setPrice($permutation->getTotalPrice())
				    ->setCost($permutation->getTotalPrice());
		    }
		}		
	    } else {
		$methods[] = Mage::getModel('shipping/rate_result_method')
			    ->setCarrier(self::CARRIER_CODE)
			    ->setCarrierTitle(Mage::getStoreConfig('carriers/temando/title'))
			    ->setMethodTitle($rule->getActionStaticLabel())
			    ->setMethod(self::METHOD_CODE)
			    ->setPrice($rule->getActionStaticValue())
			    ->setCost($rule->getActionStaticValue());
	    }
	    //stop processing of further rules check
	    if($rule->getStopOther()) {
		$stopOnNext = true;	
		$stopPriorityAfter = $priority;
	    }
	}
	
	if(empty($methods)) {
	    $error = self::ERR_NO_METHODS;
	    return;
	}
	
	return $methods;
    }
    
    
    protected function _processDynamicRule(Ewave_Temando_Model_Rule $rule, $quotes)
    {
	$carriers = explode(',', $rule->getActionDynamicCarriers());
	$cleanQuotes = array();
	
	foreach($quotes as $quote) 
	{   
	    /* @var $quote Ewave_Temando_Model_Quote */
	    if(in_array($quote->getCarrier()->getCarrierId(), $carriers))
		$cleanQuotes[] = clone $quote;
	}
	
	if(empty($cleanQuotes))
	    return null;
	
	$filteredQuotes = array();
	switch($rule->getActionDynamicFilter()) {
	    case Ewave_Temando_Model_System_Config_Source_Rule_Action_Filter::DYNAMIC_CHEAPEST:
		$filteredQuotes[] = Mage::helper('temando/functions')->getCheapestQuote($cleanQuotes);
		break;
	    case Ewave_Temando_Model_System_Config_Source_Rule_Action_Filter::DYNAMIC_FASTEST:
		$filteredQuotes[] = Mage::helper('temando/functions')->getFastestQuote($cleanQuotes);
		break;	    
	    case Ewave_Temando_Model_System_Config_Source_Rule_Action_Filter::DYNAMIC_FASTEST_AND_CHEAPEST:
		$filteredQuotes = Mage::helper('temando/functions')->getCheapestAndFastestQuotes($cleanQuotes);
		break;
	    
	    default: $filteredQuotes = $cleanQuotes; break;
	}
	
	$adjustment = $rule->getActionDynamicAdjustmentType();
	$value = $rule->getActionDynamicAdjustmentValue();
	
	if($adjustment) {
	    foreach($filteredQuotes as $filteredQuote) {
		/* @var $quote Ewave_Temando_Model_Quote */
		$filteredQuote->processAdjustment((int)$adjustment, $value);
	    }
	}
	
	return $filteredQuotes;	
    }
    
    public function getDynamicQuotesByRule($carrier_quotes)
    {
	$return = array();
	
	$orig_quotes = array();
	foreach($carrier_quotes as $carrierId => $quotes) {
	    if(!is_array($quotes)) $quotes = array($quotes);
	    foreach($quotes as $quote)
	    {
		$orig_quotes[] = $quote;
	    }
	}
	
	$new_quotes = array();
	if($this->_validRules && count($this->_validRules))
	{
	    $stopOther = false; $stopOtherPriority = null;   
	    foreach($this->_validRules as $rule)
	    {
		$priority = $rule->getPriority();
		
		if($stopOther && $priority > $stopOtherPriority) break;
		
		if($rule->isDynamic()) {
		    $carriers = explode(',', $rule->getActionDynamicCarriers());
		    
		    foreach($orig_quotes as $orig_quote)
		    {
			if(in_array($orig_quote->getCarrier()->getCarrierId(), $carriers))
			{
			    $tmp = clone $orig_quote;
			    $tmp->processAdjustment((int)$rule->getActionDynamicAdjustmentType(), $rule->getActionDynamicAdjustmentValue());
			    $tmp->setRuleId($rule->getId());
			    $new_quotes[] = $tmp;
			}
		    }
		}
		
		if($rule->getStopOther()) {
		    $stopOther = true;
		    $stopOtherPriority = $priority;
		}
	    }
	}

	foreach($new_quotes as $new_quote) {
	    //Mage::log($new_quote->getTotalPrice(), null, 'quotes.log', true);
	    $carrierName = $new_quote->getCarrier()->getCompanyName();
	    if(!array_key_exists($carrierName, $return))
		    $return[$carrierName] = array();
	    $return[$carrierName][] = $new_quote;
	}
	
	return $return;
    }
}