<?php


class Ewave_Temando_Model_Warehouse extends Mage_Core_Model_Abstract {
    
    public function _construct() {
	parent::_construct();
	$this->_init('temando/warehouse');
    }
    
    public function getTitle() {
	return $this->getName() . ' (' . $this->getStreet() . ', ' . 
		$this->getCity() . ', ' . $this->getCountry() . ')';
    }
    
    public function servesArea($postcode) {
	$zones = explode(',', $this->getZoneIds());
	
	if(is_array($zones) && !empty($zones)) {
	    foreach($zones as $zoneId) {
		$ranges = explode(',', Mage::getModel('temando/zone')->load($zoneId)->getRanges());
		foreach($ranges as $range) {
		    $minmax = explode(':', $range);
		    if(count($minmax) == 2) {
			//range specified as a:b
			if($postcode >= $minmax[0] && $postcode <= $minmax[1])
			    return true;
		    } else if(count($minmax) == 1) {
			//single value
			if($postcode == $minmax[0])
			    return true;
		    } 
		}
	    }
	}
	
	return false;
    }
    
}


