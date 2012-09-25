<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
	parent::__construct();
	$this->setId('temando_warehouse_edit_tabs');
	$this->setDestElementId('edit_form');
    }

    public function _beforeToHtml() {

	$this->addTab('related', array(
	    'label' => Mage::helper('temando')->__('Related Products'),
	    'url' => $this->getUrl('*/*/related', array('_current' => true)),
	    'class' => 'ajax',
	));

	$this->addTab('users', array(
	    'label' => Mage::helper('temando')->__('Related Users'),
	    'url' => $this->getUrl('*/*/users', array('_current' => true)),
	    'class' => 'ajax',
	));

	return parent::_beforeToHtml();
    }

}
