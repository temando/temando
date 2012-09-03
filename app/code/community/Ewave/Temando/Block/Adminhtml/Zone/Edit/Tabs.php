<?php

class Ewave_Temando_Block_Adminhtml_Zone_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
	parent::__construct();
	$this->setId('temando_zone_edit_tabs');
	$this->setDestElementId('edit_form');
	$this->setTitle(Mage::helper('temando')->__('Zone Configuration'));
    }

}
