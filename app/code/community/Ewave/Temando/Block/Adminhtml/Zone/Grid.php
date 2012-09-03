<?php

class Ewave_Temando_Block_Adminhtml_Zone_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('description');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('temando/zone')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
	
	$this->addColumn('id', array(
            'header'    => Mage::helper('temando')->__('ID'),
            'align'     =>'right',
            'index'     => 'id',
        ));
	
        $this->addColumn('name', array(
            'header' => Mage::helper('temando')->__('Name'),
            'index' => 'name',
        ));
	
	$this->addColumn('country', array(
	    'header' => Mage::helper('temando')->__('Country'),
	    'index' => 'country',
	    'type'  => 'text',
	));
	
	$this->addColumn('ranges', array(
            'header' => Mage::helper('temando')->__('Range'),
	    'type' => 'text',
            'index' => 'ranges',
        ));	

        return parent::_prepareColumns();
    }    
    
    protected function _prepareMassaction()
    {
	parent::_prepareMassaction();
	
	$this->getMassactionBlock()->addItem('delete_all', array(
             'label'=> Mage::helper('temando')->__('Delete Selected Zones'),
             'url'  => $this->getUrl('*/*/massDelete'),
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

