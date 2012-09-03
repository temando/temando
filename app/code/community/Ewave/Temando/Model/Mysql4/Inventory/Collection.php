<?php

class Ewave_Temando_Model_Mysql4_Inventory_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/inventory');
    }
    
    public function updateProductQuantities($products, $whs_id)
    {
	$this->addFieldToFilter('warehouse_id', $whs_id)->load();
	
	foreach($this->_items as $item) {
	    /* @var $item Ewave_Temando_Model_Inventory */
	    if(array_key_exists($item->getProductId(), $products)) {
		//update quantity and unset from $products array
		$item->setQuantity($products[$item->getProductId()]['quantity']);
		$item->save();
		unset($products[$item->getProductId()]);
	    } else {
		//remove item from this warehouse
		$item->delete();
	    }
	}
	
	//add remaining products & quantities
	foreach($products as $productId => $qty) {
	    $item = Mage::getModel('temando/inventory')
		    ->setProductId($productId)
		    ->setQuantity($qty['quantity'])
		    ->setWarehouseId($whs_id);
	    $item->save();
	}
	
    }
    
}