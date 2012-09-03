<?php


class Ewave_Temando_Model_Mysql4_Warehouse_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/warehouse');
    }
    
    /**
     * Returns origin with highest priority which serves given postcode
     * 
     * @param string $postcode
     * @return Ewave_Temando_Model_Warehouse|null 
     */
    public function getOriginByPostcode($postcode, $storeId = null)
    {
	$this->setOrder('priority', 'ASC')->load();
	
	$validOrigin = null;
	foreach($this->_items as $warehouse) {
	    
	    $store_ids = explode(',', $warehouse->getStoreIds());
	    if($storeId && !in_array($storeId, $store_ids))
		continue;
	    
	    if($warehouse->servesArea($postcode)) {
		$validOrigin = $warehouse;
		break;
	    }
	}
	
	return $validOrigin;
	
    }
    
}

