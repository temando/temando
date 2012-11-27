<?php

class Ewave_Temando_Block_Adminhtml_Rule_Edit_Tab_Main
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
        return Mage::helper('temando')->__('Rule Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('temando')->__('Rule Information');
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
        $model = Mage::registry('current_temando_rule');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

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
            'label' => Mage::helper('temando')->__('Rule Name'),
            'title' => Mage::helper('temando')->__('Rule Name'),
            'required' => true,
        ));
	
	$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('temando')->__('Status'),
            'title'     => Mage::helper('temando')->__('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('temando')->__('Active'),
                '0' => Mage::helper('temando')->__('Inactive'),
            ),
        ));
	
	$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'   => 'from_date',
            'label'  => Mage::helper('temando')->__('From Date'),
            'title'  => Mage::helper('temando')->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'   => 'to_date',
            'label'  => Mage::helper('temando')->__('To Date'),
            'title'  => Mage::helper('temando')->__('To Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
	
	$fieldset->addField('priority', 'text', array(
	    'label' => Mage::helper('temando')->__('Priority'),
	    'title' => Mage::helper('temando')->__('Priority'),
	    'name'  => 'priority',
	    'class' => 'validate-number',
	));
	
	$fieldset->addField('stop_other', 'select', array(
            'label'     => Mage::helper('temando')->__('Stop processing of further rules'),
            'title'     => Mage::helper('temando')->__('Stop processing of further rules'),
            'name'      => 'stop_other',
            'options'    => array(
                '1' => Mage::helper('temando')->__('Yes'),
                '0' => Mage::helper('temando')->__('No'),
            ),
	    'note'  => Mage::helper('temando')->__('Rules with higher number in priority field will not be processed if set to \'Yes\''),
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
	
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }
	
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
