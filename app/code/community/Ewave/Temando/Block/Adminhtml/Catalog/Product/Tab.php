<?php

/**
 * Additional Tab on Product Edit Page
 * 
 * Allows user to define product specific temando packaging
 */

class Ewave_Temando_Block_Adminhtml_Catalog_Product_Tab
extends Mage_Adminhtml_Block_Template
implements Mage_Adminhtml_Block_Widget_Tab_Interface {
 
    /**
     * Set the template for the block
     *
     */
    public function _construct()
    {
        parent::_construct();
 
        $this->setTemplate('ewave/temando/catalog/product/tab.phtml');
    }
 
    /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Temando');
    }
 
    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Temando');
    }
 
    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
 
    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
    
    /**
     * Returns currently edited product
     * 
     * @return Mage_Catalog_Model_Product 
     */
    public function getProduct()
    {
	return Mage::registry('product');
    }
    
    /**
     * Returns style html to hide/display package div
     * 
     * @param int $id Package index
     * @return string 
     */
    public function getDisplayPackageHtml($id)
    {
	$desc = $this->getProduct()->getData("temando_package_{$id}_description");
	if(empty($desc)) {
	    return "style='display: none'";
	}
	
	return '';
    }
    
    public function getTemandoPackaging()
    {
	$options = Mage::getModel('temando/system_config_source_shipment_packaging')->getOptions();
	/*@var $options Ewave_Temando_Model_System_Config_Source_Shipment_Packaging */
	
	return $options;
    }
 
}