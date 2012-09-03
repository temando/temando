<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Products
    extends Ewave_Temando_Block_Adminhtml_Warehouse_Inventory_Grid //Mage_Adminhtml_Block_Catalog_Product_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('temando')->__('Products');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('temando')->__('Products');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareColumns() {
	parent::_prepareColumns();
    }
    
    protected function _prepareCollection() {
	parent::_prepareCollection();	
    }
    
    
    
}

