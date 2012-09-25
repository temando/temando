<?php

class Ewave_Temando_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('temando/rule')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
	
	$this->addColumn('id', array(
            'header'    => Mage::helper('temando')->__('ID'),
            'align'     =>'right',
            'width'     => '40px',
            'index'     => 'id',
        ));
	
        $this->addColumn('name', array(
            'header' => Mage::helper('temando')->__('Rule Name'),
	    'width' => '150px',
            'index' => 'name',
        ));

        $this->addColumn('is_active', array(
            'header' => Mage::helper('temando')->__('Status'),
	    'width' => '50px',
	    'index' => 'is_active',
	    'type' => 'options',
	    'options'   => array(
                1 => 'Active',
                0 => 'Inactive',
            ),           
        ));
        
        $this->addColumn('action_rate_type', array(
            'header' => Mage::helper('temando')->__('Type'),
            'index' => 'action_rate_type',
            'type'  => 'options',
            'width' => '50px',
            'options' => Mage::getSingleton('temando/system_config_source_rule_type')->getOptions(),
        ));
	
	$this->addColumn('priority', array(
	    'header' => Mage::helper('temando')->__('Priority'),
	    'index' => 'priority',
	    'type'  => 'text',
	    'width' => '50px',
	));
	
	$this->addColumn('stop_other', array(
	    'header' => Mage::helper('temando')->__('Stop Other Rules'),
	    'index' => 'stop_other',
	    'type'  => 'options',
	    'width' => '50px',
	    'options' => array(
		0 => 'No',
		1 => 'Yes',
	    ),
	));
        
        $this->addColumn('action_static_label', array(
            'header' => Mage::helper('temando')->__('Frontend Title (Static Method)'),
            'width' => '250px',
            'index' => 'action_static_label',
        ));

        return parent::_prepareColumns();
    }    

    protected function _prepareMassaction() {
	parent::_prepareMassaction();
	
	$this->getMassactionBlock()->addItem('deactivate_all', array(
            'label'=> Mage::helper('temando')->__('Deactivate Rules'),
            'url'  => $this->getUrl('*/*/massDeactivate'),
        ));
	
	$this->getMassactionBlock()->addItem('activate_all', array(
            'label'=> Mage::helper('temando')->__('Activate Rules'),
            'url'  => $this->getUrl('*/*/massActivate'),
        ));
	
	$this->getMassactionBlock()->addItem('remove_all', array(
            'label'=> Mage::helper('temando')->__('Delete Rules'),
            'url'  => $this->getUrl('*/*/massRemove'),
	    'confirm' => Mage::helper('temando')->__('Are you sure you want to delete selected rules?'),
        ));
	
	$this->setMassactionIdField('id');	
	$this->getMassactionBlock()->setUseSelectAll(false);

        return $this;
    }    

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}

