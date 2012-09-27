<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Extras
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
        return Mage::helper('temando')->__('Extra Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('temando')->__('Extra Information');
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
            array('legend' => Mage::helper('temando')->__('Extra Location Information'))
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $fieldset->addField('loading_facilities', 'select', array(
            'name' => 'loading_facilities',
            'label' => Mage::helper('temando')->__('Loading Facilities'),
            'title' => Mage::helper('temando')->__('Loading Facilities'),
            'required' => true,
	    'values' => array(
		'N' => 'No', 
		'Y' => 'Yes'
	    ),
	    'note' => 'Does the warehouse have loading facilities?',
        ));
	
	$fieldset->addField('dock', 'select', array(
            'name' => 'dock',
            'label' => Mage::helper('temando')->__('Dock'),
            'title' => Mage::helper('temando')->__('Dock'),
            'required' => true,
	    'values' => array(
		'N' => 'No', 
		'Y' => 'Yes'
	    ),
	    'note' => 'Does the warehouse have a dock?',
        ));
	
	$fieldset->addField('forklift', 'select', array(
	    'name' => 'forklift',
	    'label' => Mage::helper('temando')->__('Forklift'),
	    'title' => Mage::helper('temando')->__('Forklift'),
	    'required' => true,
	    'values' => array(
		'N' => 'No', 
		'Y' => 'Yes'
	    ),
	    'note' => 'Does the warehouse have a forklift?',
	));
	
	$fieldset->addField('limited_access', 'select', array(
	    'name' => 'limited_access',
	    'label' => Mage::helper('temando')->__('Limited Access'),
	    'title' => Mage::helper('temando')->__('Limited Access'),
	    'required' => true,
	    'values' => array(
		'N' => 'No', 
		'Y' => 'Yes'
	    ),
	    'note' => 'Does the warehouse have limited access?',
	));
	
	$field = $fieldset->addField('postal_box', 'select', array(
	    'name'     => 'postal_box',
	    'label'     => Mage::helper('temando')->__('Postal Box'),
	    'title'     => Mage::helper('temando')->__('POstal Box'),
	    'required' => true,
	    'values' => array(
		'N' => 'No', 
		'Y' => 'Yes'
	    ),
	    'note' => 'Is the address of this warehouse a postal box?',
	));
	
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
