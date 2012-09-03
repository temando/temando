<?php

class Ewave_Temando_Block_Adminhtml_Zone_Edit_Tab_General
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
        return Mage::helper('temando')->__('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('temando')->__('General');
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
        $model = Mage::registry('current_temando_zone');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('zone_');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('temando')->__('Zone Information'))
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
	
	$fieldset->addField('country', 'select', array(
	    'name' => 'country',
	    'label' => Mage::helper('temando')->__('Country'),
	    'title' => Mage::helper('temando')->__('Country'),
	    'options' => Mage::getSingleton('temando/system_config_source_country')->getOptions(),
	    'required' => true,
	));
	
	$fieldset->addField('ranges', 'textarea', array(
	    'name' => 'ranges',
	    'label' => Mage::helper('temando')->__('Postal Code Ranges'),
	    'title' => Mage::helper('temando')->__('Postal Code Ranges'),
	    'class' => 'validate-range-multi',
	    'required' => true,
	    'note' => Mage::helper('temando')->__('Use colon to specify range, comma to separate ranges.'),
	));
	
	
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
