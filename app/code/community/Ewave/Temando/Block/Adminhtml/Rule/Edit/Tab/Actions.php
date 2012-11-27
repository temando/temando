<?php

class Ewave_Temando_Block_Adminhtml_Rule_Edit_Tab_Actions
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('temando')->__('Actions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('temando')->__('Actions');
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

    protected function _prepareForm()
    {
        $model = Mage::registry('current_temando_rule');
        
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('actions_fieldset', array(
            'legend'=>Mage::helper('temando')->__('Actions Configuration')
        ));
	
	$fieldset->addField('action_rate_type', 'select', array(
            'label'     => Mage::helper('temando')->__('Shipping Rate Type'),
            'title'     => Mage::helper('temando')->__('Shipping Rate Type'),
            'name'      => 'action_rate_type',
            'required' => true,
            'options'    => Mage::getModel('temando/system_config_source_rule_type')->getOptions(),
        ));
	
	$fieldset = $form->addFieldset('actions_static_fieldset', array(
	    'legend' => Mage::helper('temando')->__('Static Rate Configuration')
	));
	
	$fieldset->addField('action_static_value', 'text', array(
	    'label' => Mage::helper('temando')->__('Static Rate Value'),
	    'title' => Mage::helper('temando')->__('Static Rate Value'),
	    'name' => 'action_static_value',
	    'class' => 'validate-number',
	    'note' => 'Applies to free shipping and flat rate.'
	));
	
	$fieldset->addField('action_static_label', 'text', array(
	    'label' => Mage::helper('temando')->__('Static Rate Label'),
	    'title' => Mage::helper('temando')->__('Static Rate Label'),
	    'name' => 'action_static_label',
	    'note' => Mage::helper('temando')->__('As displayed to a customer. Applies to free shipping & flat rate.')
	    
	));
	
	$fieldset = $form->addFieldset('actions_dynamic_fieldset', array(
	    'legend' => Mage::helper('temando')->__('Dynamic Rate Configuration')
	));
	
	$fieldset->addField('action_dynamic_carriers', 'multiselect', array(
	    'name'     => 'action_dynamic_carriers[]',
	    'label'     => Mage::helper('temando')->__('Carriers'),
	    'title'     => Mage::helper('temando')->__('Carriers'),
	    'values'   => Mage::getSingleton('temando/shipping_carrier_temando_source_method')->getOptionsForForm(true),
	));
	
	$fieldset->addField('action_dynamic_filter', 'select', array(
	    'name'  => 'action_dynamic_filter',
	    'label' => Mage::helper('temando')->__('Display Filter'),
	    'title' => Mage::helper('temando')->__('Display Filter'),
	    'options' => Mage::getSingleton('temando/system_config_source_rule_action_filter')->getOptions(),
	));
	
	$fieldset->addField('action_dynamic_adjustment_type', 'select', array(
	    'label' => Mage::helper('temando')->__('Rate Adjustment Type'),
	    'title' => Mage::helper('temando')->__('Rate Adjustment Type'),
	    'name' => 'action_dynamic_adjustment_type',
	    'options' => Mage::getSingleton('temando/system_config_source_rule_action_adjustment_type')->getOptions(),	    
	));
	
	$fieldset->addField('action_dynamic_adjustment_value', 'text', array(
	    'label' => Mage::helper('temando')->__('Rate Adjustment Value'),
	    'title' => Mage::helper('temando')->__('Rate Adjustment Value'),
	    'name' => 'action_dynamic_adjustment_value',
	    'class' => 'validate-range-single-decimal',
	    'note' => Mage::helper('temando')->__('For min/max, enter range as min:max (ie \'5.95:10.95\' to keep shipping price between $5.95 - $10.95)')
	));
	
	$fieldset->addField('action_dynamic_show_carrier_name', 'select', array(
	    'label' => Mage::helper('temando')->__('Show Carrier Name'),
	    'title' => Mage::helper('temando')->__('Show Carrier Name'),
	    'name' => 'action_dynamic_show_carrier_name',
	    'options' => array(
                '1' => Mage::helper('temando')->__('Yes'),
                '0' => Mage::helper('temando')->__('No'),
            ),
	));
	
	$fieldset->addField('action_dynamic_show_carrier_time', 'select', array(
	    'label' => Mage::helper('temando')->__('Show Transit Type and Time'),
	    'title' => Mage::helper('temando')->__('Show Transit Type and Time'),
	    'name' => 'action_dynamic_show_carrier_time',
	    'options' => array(
                '1' => Mage::helper('temando')->__('Yes'),
                '0' => Mage::helper('temando')->__('No'),
            ),
	));
	
	$fieldset->addField('action_dynamic_label', 'text', array(
	    'label' => Mage::helper('temando')->__('Alternative Method Title'),
	    'title' => Mage::helper('temando')->__('Alternative Method Title'),
	    'name' => 'action_dynamic_label',
		'note' => Mage::helper('temando')->__('Appears in place of carrier name when \'Show Carrier Name\' is off')	,    
	));
	
	$fieldset = $form->addFieldset('actions_restrict_fieldset', array(
	    'legend' => Mage::helper('temando')->__('Restrict Shipping Configuration')
	));

	$fieldset->addField('action_restrict_note', 'textarea', array(
	   'name'   => 'action_restrict_note',
	   'label'  => Mage::helper('temando')->__('Note'),
	   'title'  => Mage::helper('temando')->__('Note'),
	   'note'   => Mage::helper('temando')->__('Displayed to a customer when shipping is restricted.') 
	));
	

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

