<?php

class Ewave_Temando_Model_Rule extends Mage_Core_Model_Abstract
{
    /**
     * Formatted Conditions of this rule
     * @var array 
     */
    protected $_conditions;
    
    /**
     * Standard Numeric Conditions
     * @var array 
     */
    protected $_allowedConditions = array(
	'weight', 'subtotal', 'items', 'zone'
    );
    
    public function _construct()
    {
	$this->_init('temando/rule');
    }
    

    public function load($id, $field = null) {
	parent::load($id, $field);
	$this->_prepareConditions();
    }
    
    /**
     * Consolidates ranges and sets conditions
     * 
     * @return \Ewave_Temando_Model_Rule 
     */
    protected function _prepareConditions()
    {
	if($this->_conditions)
	    return $this;
	
	//standard numeric conditions
	foreach($this->_allowedConditions as $condition)
	{
	    $data = $this->getData('condition_' . $condition);
	    if($data) {
		$ranges = explode(',', $data);
		foreach($ranges as $range) {
		    $minmax = explode(':', $range);
		    if(count($minmax) == 2) {
			//range specified as a:b
			if($minmax[0] <= $minmax[1]) {
			    $this->_conditions[$condition][] = array(
				'min' => trim($minmax[0]),
				'max' => trim($minmax[1])
			    );
			}	
		    } else if(count($minmax) == 1) {
			//single value
			$this->_conditions[$condition][] = array(
				'min' => trim($minmax[0]),
				'max' => trim($minmax[0])
			);
		    } 
		}
	    }
	}	
	return $this;
    }
    
    /**
     * Returns true if rule is a valid rule applicable to current scenario
     * based on weight, subtotal, total items and poscode conditions
     * 
     * @param type $weight
     * @param type $subtotal
     * @param type $items
     * @param type $postcode
     * @return boolean 
     */
    public function isValid($weight, $subtotal, $items, $postcode)
    {
	$this->_prepareConditions();
	
	return  $this->_validateCondition('weight', $weight) &&
		$this->_validateCondition('subtotal', $subtotal) &&
		$this->_validateCondition('items', $items) &&
		$this->_validateCondition('zone', $postcode) &&
		$this->_validateTimeCondition();
    }
    
    protected function _validateCondition($condition, $value)
    {
	if(is_null($value)) return true; //this should not happen but just in case
	
	$return = false;
	if(isset($this->_conditions[$condition])) {
	    foreach($this->_conditions[$condition] as $cond)
	    {
		if($value >= $cond['min'] && $value <= $cond['max'])
		{
		    $return = true;
		    break;
		}
	    }
	} else {
	    //no range specified - allow all
	    $return = true;
	}
	
	return $return;
    }
    
    private function _validateTimeCondition()
    {
	$return = false;
	if($this->getData('condition_time_type')) {
	    //need to validate time
	    $current_time = Mage::app()->getLocale()->storeTimeStamp(Mage::app()->getStore()->getId());
	    $config_time = strtotime(str_replace(',', ':', $this->getData('condition_time_value')));
	    
	    switch($this->getData('condition_time_type')) {
		case Ewave_Temando_Model_System_Config_Source_Rule_Condition_Time::BEFORE:
		    if($current_time < $config_time)
			$return = true;
		    break;
		case Ewave_Temando_Model_System_Config_Source_Rule_Condition_Time::AFTER:
		    if($current_time >= $config_time)
			$return = true;
		    break;
		
		default:
		    $return = true;
		    break;
	    }
	} else {
	    //no condition set
	    $return = true;
	}	
	return $return;
    }
    
    public function isDynamic()
    {
	if($this->getActionRateType() == Ewave_Temando_Model_System_Config_Source_Rule_Type::DYNAMIC) {
	    return true;
	}
	return false;
    }
}
