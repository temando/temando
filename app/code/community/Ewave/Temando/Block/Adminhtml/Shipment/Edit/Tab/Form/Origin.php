<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Form_Origin extends Ewave_Temando_Block_Adminhtml_Shipment_Edit_Tab_Abstract
{
    
    public function getWarehouseOptions() {
	
	$return = array();
	$return[''] = ' -- ';
	$warehouses = Mage::getModel('temando/warehouse')->getCollection();
	foreach($warehouses as $warehouse) {
	    $return[$warehouse->getId()] = $warehouse->getTitle();
	}
	
	return $return;
    }
    
}

