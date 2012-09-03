<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_General
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
        return Mage::helper('temando')->__('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('temando')->__('General Information');
    }

    /**
     * Returns status flag about this tab can be showed or not
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

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('temando')->__('General Information'))
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('temando')->__('Name'),
            'title' => Mage::helper('temando')->__('Name'),
            'required' => true,
        ));
	
	$fieldset->addField('company_name', 'text', array(
            'name' => 'company_name',
            'label' => Mage::helper('temando')->__('Company Name'),
            'title' => Mage::helper('temando')->__('Company Name'),
            'required' => true,
        ));
	
	$fieldset->addField('location_type', 'select', array(
	    'name' => 'location_type',
	    'label' => Mage::helper('temando')->__('Location Type'),
	    'title' => Mage::helper('temando')->__('Location Type'),
	    'required' => true,
	    'values' => Mage::getSingleton('temando/system_config_source_origin_type')->getOptions(),
	));
	
	$fieldset->addField('priority', 'text', array(
	    'name' => 'priority',
	    'label' => Mage::helper('temando')->__('Priority'),
	    'title' => Mage::helper('temando')->__('Priority'),
	    'required' => false,
	    'class' => 'validate-digits',
	));
	
	$field = $fieldset->addField('store_ids', 'multiselect', array(
	    'name'     => 'store_ids[]',
	    'label'     => Mage::helper('temando')->__('Stores'),
	    'title'     => Mage::helper('temando')->__('Stores'),
	    'required' => true,
	    'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
	));
	$renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
	
	
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
