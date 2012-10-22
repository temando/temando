<?php

class Ewave_Temando_Model_Mysql4_Shipment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/shipment');
    }
    
    /**
     * Returns the shipment associated to an order or null if none
     * 
     * NO SUPPORT FOR PARTIAL-SHIPMENTS HERE; ONLY 1 IS RETURNED
     * 
     * @param string $orderId
     * @return null|\Ewave_Temando_Model_Shipment 
     */
    public function loadByOrderId($orderId) {
	if(!$orderId) return false;
	
	$this->addFieldToFilter('order_id', $orderId)->load();
	if($this->count())
	    return $this->getFirstItem ();
	
	return false;
    }
    
    /**
     * Populate order_id on shipments where order_id is NULL
     * - happens when shipping to multiple addresses used on checkout
     */
    public function fixOrderIds() {
	$this->addFieldToFilter('order_id', array('null' => true))->load();
	if($this->count()) {
	    foreach($this->getItems() as $item) {
		if($item->getOrderIncrementId())
		{
		    $order = Mage::getModel('sales/order')->loadByIncrementId($item->getOrderIncrementId());
		    $item->setOrderId($order->getId());
		    $item->save();
		}
	    }
	}
    }    
    
}
