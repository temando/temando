<?php

class Ewave_Temando_Block_Adminhtml_Rule_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    
    public function __construct()
    {
	$this->_objectId = 'id';
        $this->_blockGroup = 'temando';
        $this->_controller = 'adminhtml_rule';
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
        $rule = Mage::registry('current_temando_rule');
        if ($rule->getId()) {
            return Mage::helper('temando')->__("Edit Rule '%s'", $this->escapeHtml($rule->getName()));
        }
        else {
            return Mage::helper('temando')->__('New Rule');
        }
    }
    
}
