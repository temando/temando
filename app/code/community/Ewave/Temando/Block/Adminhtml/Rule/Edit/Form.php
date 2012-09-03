<?php

class Ewave_Temando_Block_Adminhtml_Rule_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    public function __construct()
    {
	parent::__construct();
        $this->setId('rule_form');
        $this->setTitle(Mage::helper('temando')->__('Rule Configuration'));
    }
    
    
    protected function _prepareForm()
    {
	$form = new Varien_Data_Form(
		array(
		    'id' => 'edit_form', 
		    'action' => $this->getData('action'), 
		    'method' => 'post')
	);

//        $form = new Varien_Data_Form(
//            array(
//                'id' => 'edit_form',
//                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
//                'method' => 'post',
//                'enctype' => 'multipart/form-data'
//            )
//        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
