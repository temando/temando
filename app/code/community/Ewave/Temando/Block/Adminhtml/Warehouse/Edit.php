<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    
    public function __construct()
    {
	$this->_objectId = 'id';
        $this->_blockGroup = 'temando';
        $this->_controller = 'adminhtml_warehouse';
        parent::__construct();
	

	$this->_addButton('save_and_continue_edit', array(
            'class'   => 'save',
            'label'   => Mage::helper('temando')->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
        ), 10);
	
    } 

    /**
     * Getter for form header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $warehouse = Mage::registry('current_temando_warehouse');
        if ($warehouse->getId()) {
            return Mage::helper('temando')->__("Edit Warehouse '%s'", $this->escapeHtml($warehouse->getName()));
        }
        else {
            return Mage::helper('temando')->__('New Warehouse');
        }
    }
    
}
