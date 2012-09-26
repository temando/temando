<?php

/**
 * Custom grid renderer for shipment origin location
 *
 * @author martin
 */
class Ewave_Temando_Block_Adminhtml_Shipment_Grid_Renderer_Origin extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
    
    public function _getValue(Varien_Object $row)
    {
	$warehouse = Mage::getModel('temando/warehouse')->load($row->getWarehouseId());
	/* @var $warehouse Ewave_Temando_Model_Warehouse */
	
	return $warehouse->getName();
    }
}


