<?php

/**
 * Add 'Ship with Temando' button on order view screen if carrier chosen is Temando
 * and order can ship
 *
 * @author martin
 */
class Ewave_Temando_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {
 
    public function _construct()
    {
	parent::_construct();
	
	$order = $this->getOrder();
	$shipment = Mage::getModel('temando/shipment')->getCollection()->loadByOrderId($order->getEntityId());
	/* @var $shipments Ewave_Temando_Model_Shipment */
	
	/* @var $order Mage_Sales_Model_Order */
	if ($this->_isAllowedAction('ship') && $order->canShip() && !$order->getForcedDoShipmentWithInvoice()
	      && substr($order->getShippingMethod(), 0, 7) == 'temando' && $shipment && $shipment->getId())
	{
            $this->_addButton('order_ship_temando', array(
                'label'     => Mage::helper('sales')->__('Ship with Temando'),
                'onclick'   => 'setLocation(\'' . $this->getTemandoShipUrl($shipment->getId()) . '\')',
                'class'     => 'go',
            ));
	    
        }
    }
    
    public function getTemandoShipUrl($id)
    {
        return $this->getUrl('temando/adminhtml_shipment/edit', array('id' => $id));
    }
}


