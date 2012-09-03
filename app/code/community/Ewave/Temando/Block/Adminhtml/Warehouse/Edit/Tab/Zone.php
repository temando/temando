<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Zone
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
        return Mage::helper('temando')->__('Zones');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('temando')->__('Zones');
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

        $fieldset = $form->addFieldset('zone_fieldset', array(
            'legend'=>Mage::helper('temando')->__('Zones')
        ));
	
	$fieldset->addField('zone_ids', 'multiselect', array(
	    'name' => 'zone_ids[]',
	    'label' => Mage::helper('temando')->__('Zones'),
	    'title' => Mage::helper('temando')->__('Zones'),
	    'required' => true,
	    'values' => Mage::getSingleton('temando/system_config_source_zones')->toOptionArray(),
	    'note' => Mage::helper('temando')->__('Use CTRL + click to assign multiple zones.'),
	));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

