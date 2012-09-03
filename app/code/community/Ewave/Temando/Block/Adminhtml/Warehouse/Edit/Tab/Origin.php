<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Origin
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
        return Mage::helper('temando')->__('Address & Contact');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('temando')->__('Address & Contact');
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
        $model = Mage::registry('current_temando_warehouse');
        
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('warehouse_');

        $fieldset = $form->addFieldset('address_fieldset', array(
            'legend'=>Mage::helper('temando')->__('Address Details')
        ));
	
	$fieldset->addField('street', 'text', array(
	    'name' => 'street',
	    'label' => Mage::helper('temando')->__('Street'),
	    'title' => Mage::helper('temando')->__('Street'),
	    'required' => true,
	));
	
	$fieldset->addField('city', 'text', array(
	    'name'	=> 'city',
	    'label'     => Mage::helper('temando')->__('City'),
	    'title'     => Mage::helper('temando')->__('City'),
	    'required'	=> true,
	));
	
	$fieldset->addField('postcode', 'text', array(
	    'label'	=> Mage::helper('temando')->__('ZIP/Postal Code'),
	    'title'	=> Mage::helper('temando')->__('ZIP/Postal Code'),
	    'name'	=> 'postcode',
	    'required'	=> true,
	    
	));
	
	$fieldset->addField('country', 'select', array(
            'label'     => Mage::helper('temando')->__('Country'),
            'title'     => Mage::helper('temando')->__('Country'),
            'name'      => 'country',
            'required'	=> true,
	    'class'	=> 'temando-countries',
            'options'   => Mage::getModel('temando/system_config_source_country')->getOptions(),
        ));
	
	$fieldset->addField('region', 'select', array(
	    'label'	=> Mage::helper('temando')->__('Region'),
	    'title'	=> Mage::helper('temando')->__('Region'),
	    'name'	=> 'region',
	    'required'	=> true,
	    'options'	=> Mage::getSingleton('temando/system_config_source_regions')->getOptions(),
	));

	
	//NEW FIELDSET
	$fieldset = $form->addFieldset('contact_fieldset', array(
	    'legend' => Mage::helper('temando')->__('Contact Details')
	));
	
	$fieldset->addField('contact_name', 'text', array(
	    'name' => 'contact_name',
	    'label' => Mage::helper('temando')->__('Contact Name'),
	    'title' => Mage::helper('temando')->__('Contact Name'),
	    'required' => true,
	));	
	
	$fieldset->addField('contact_email', 'text', array(
	    'name' => 'contact_email',
	    'label' => Mage::helper('temando')->__('Contact Email'),
	    'title' => Mage::helper('temando')->__('Contact Email'),
	    'required' => true,
	    'class' => 'validate-email',
	));
	
	$fieldset->addField('contact_phone_1', 'text', array(
	    'name' => 'contact_phone_1',
	    'label' => Mage::helper('temando')->__('Phone 1'),
	    'title' => Mage::helper('temando')->__('Phone 1'),
	    'required' => true,
	));
	
	$fieldset->addField('contact_phone_2', 'text', array(
	    'name' => 'contact_phone_2',
	    'label' => Mage::helper('temando')->__('Phone 2'),
	    'title' => Mage::helper('temando')->__('Phone 2'),
	    'required' => false,
	));
	
	$fieldset->addField('contact_fax', 'text', array(
	    'name' => 'contact_fax',
	    'label' => Mage::helper('temando')->__('Fax'),
	    'title' => Mage::helper('temando')->__('Fax'),
	    'required' => false,
	));
	

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

