<?php


class Ewave_Temando_Block_Adminhtml_Warehouse_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    
    public function _construct()
    {
	parent::_construct();
	$this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection() {
	$collection = Mage::getModel('temando/warehouse')->getCollection();
	$this->setCollection($collection);
	
	parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
	
	$this->addColumn('id', array(
            'header' => Mage::helper('temando')->__('Warehouse ID'),
	    'index' => 'id',
	    'width' => '50px',
        ));
	
	$this->addColumn('name', array(
	    'header' => Mage::helper('temando')->__('Name'),
	    'index'  => 'name',
	));
	
	$this->addColumn('street', array(
	    'header' => Mage::helper('temando')->__('Street'),
	    'index'  => 'street',
	));
	
	$this->addColumn('city', array(
	    'header' => Mage::helper('temando')->__('City'),
	    'index'  => 'city',
	));
	
	$this->addColumn('postcode', array(
	    'header' => Mage::helper('temando')->__('ZIP/Postcode'),
	    'index'  => 'postcode',
	));
	
	$this->addColumn('region', array(
	    'header' => Mage::helper('temando')->__('Region'),
	    'index'  => 'region',
	));
	
	$this->addColumn('country', array(
	    'header' => Mage::helper('temando')->__('Country'),
	    'index'  => 'country',
	));
	
	parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }    
    
    
}
