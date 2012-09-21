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
    
    /**
     * Returns true if warehouse serves given postal code,
     * false otherwise
     * 
     * @param string|int $postcode
     * @return boolean 
     */
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
    
    /**
     * Returns request array used to create location via API
     * 
     * @return array
     */
    public function toCreateLocationRequestArray()
    {
	return array(
	    'description' => $this->getName(),
	    'type' => 'Origin',
	    'contactName' => $this->getContactName(),
	    'companyName' => $this->getCompanyName(),
	    'street' => $this->getStreet(),
	    'suburb' => $this->getCity(),
	    'state' => $this->getRegion(),
	    'code' => $this->getPostcode(),
	    'country' => $this->getCountry(),
	    'phone1' => $this->getData('contact_phone_1'),
	    'phone2' => $this->getData('contact_phone_2'),
	    'fax' => $this->getContactFax(),
	    'email' => $this->getContactEmail(),
	    'loadingFacilities' => 'N',
	    'forklift' => 'N',
	    'dock' => 'N',
	    'limitedAccess' => 'N',
	    'postalBox' => 'N',
	);
    }
    
}


