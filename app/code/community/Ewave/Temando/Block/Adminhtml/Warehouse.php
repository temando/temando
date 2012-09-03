<?php

/**
 * Description of Warehouse
 *
 * @author martin
 */
class Ewave_Temando_Block_Adminhtml_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function _construct()
    {
        $this->_blockGroup = 'temando';
        $this->_controller = 'adminhtml_warehouse';
        $this->_headerText = Mage::helper('temando')->__('Manage Warehouses');
        parent::_construct();
	
//	$add_button_method = 'addButton';
//        if (!method_exists($this, $add_button_method)) {
//            $add_button_method = '_addButton';
//        }
//
//        $this->$add_button_method('synclocations', array(
//	    'label' => Mage::helper('temando')->__('Sync with Temando.Com'),
//	    'id' => 'synclocations',
//	    'onclick' => 'setLocation(\'' . $this->getUrl('*/adminhtml_sync/locations') .'\')',
//	    'value' => '',
//	    'class' => 'save',
//	));
        
        
    }
}


