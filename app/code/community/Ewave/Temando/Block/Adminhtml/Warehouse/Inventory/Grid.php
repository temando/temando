<?php


class Ewave_Temando_Block_Adminhtml_Warehouse_Inventory_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    
    public function __construct()
    {
        parent::__construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
	$warehouseId = $this->getRequest()->getParam('id');
	$warehouse_products = Mage::getModel('temando/inventory')
				    ->getCollection()
				    ->addFieldToFilter('warehouse_id', $warehouseId);
	
	/* @var $warehouse_products Ewave_Temando_Model_Mysql4_Inventory_Collection */
	$whs_ids = $warehouse_products->getColumnValues('product_id');
	$collection = Mage::getModel('catalog/product')->getCollection()
			->addFieldToFilter('entity_id', $whs_ids)
			->addAttributeToSelect('*')
			->joinTable('temando/inventory', 'product_id=entity_id', array('quantity' => 'quantity'));
	
	$this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
	$this->addColumn('sku', array(
	    'header' => Mage::helper('temando')->__('SKU'),
	    'type'   => 'text',
	    'index'  => 'sku'
	));
	
	$this->addColumn('name', array(
	    'header' => Mage::helper('temando')->__('Name'),
	    'type'   => 'text',
	    'index'  => 'name'
	));
	
	$this->addColumn('quantity', array(
	    'header' => Mage::helper('temando')->__('Warehouse Quantity'),
	    'type'   => 'text',
	    'index'  => 'quantity',
	));
	
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array(
            'store'=>$this->getRequest()->getParam('store'),
            'id'=>$row->getId())
        );
    }
    
}
