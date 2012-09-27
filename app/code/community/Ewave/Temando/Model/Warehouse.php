<?php


class Ewave_Temando_Model_Warehouse extends Mage_Core_Model_Abstract {
    
    
    /**
     * User IDs of all users allowed to view this warehouse data
     * 
     * @var array 
     */
    protected $_allowedUsers = null;
    
    
    public function _construct() {
	parent::_construct();
	$this->_init('temando/warehouse');
    }
    
    
    /**
     * Returns warehouse title (combined name + address)
     * 
     * @return string 
     */
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
     * Load allowed users for this warehouse
     * 
     * @return \Ewave_Temando_Model_Warehouse 
     */
    protected function _loadUsers()
    {
	$users = unserialize($this->getWhsUsers());
	if(is_array($users) && !empty($users))
	{
	    $this->_allowedUsers = array_keys($users);
	}
	
	if(!is_array($this->_allowedUsers))
	    $this->_allowedUsers = array();
	 
	return $this;
	
    }
    
    /**
     * Returns user ids allowed to view warehouse data
     * 
     * @return array 
     */
    public function getAllowedUsers()
    {
	if(!is_array($this->_allowedUsers)) {
	    $this->_loadUsers();
	}
	
	return $this->_allowedUsers;
    }
    
    /**
     * Returns true if user is allowed to view this warehouse data,
     * false otherwise
     * 
     * @param int $id
     * @return boolean 
     */
    public function isAllowedUser($id)
    {
	$allowed = $this->getAllowedUsers();
	if(in_array($id, $allowed)) {
	    return true;
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
	    'loadingFacilities' => $this->getLoadingFacilities(),
	    'forklift' => $this->getForklift(),
	    'dock' => $this->getDock(),
	    'limitedAccess' => $this->getLimitedAccess(),
	    'postalBox' => $this->getPostalBox(),
	);
    }
    
}


