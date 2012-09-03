<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    public function __construct()
    {
	parent::__construct();
        $this->setId('warehouse_form');
    }
    
    
    protected function _prepareForm()
    {
	$form = new Varien_Data_Form(
		array(
		    'id' => 'edit_form', 
		    'action' => $this->getData('action'), 
		    'method' => 'post')
	);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
