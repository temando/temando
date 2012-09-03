<?php

class Ewave_Temando_Block_Adminhtml_Zone extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct()
    {
        $this->_blockGroup = 'temando';
        $this->_controller = 'adminhtml_zone';
        $this->_headerText = Mage::helper('temando')->__('Manage Zones');
	$this->_addButtonLabel = Mage::helper('temando')->__('Add New Zone');
        parent::__construct();
    }
    
}