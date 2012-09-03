<?php

class Ewave_Temando_Block_Adminhtml_Rule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    public function __construct()
    {
        $this->_blockGroup = 'temando';
        $this->_controller = 'adminhtml_rule';
        $this->_headerText = Mage::helper('temando')->__('Manage Rules');
	$this->_addButtonLabel = Mage::helper('temando')->__('Add New Rule');
        parent::__construct();
    }
    
}