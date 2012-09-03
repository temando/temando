<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Related extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Set grid params
     *
     */
    public function __construct() {

	parent::__construct();
	$this->setId('related_product_grid');
	$this->setDefaultSort('entity_id');
	$this->setUseAjax(true);
	if ($this->_getWarehouse()->getId()) {
	    $this->setDefaultFilter(array('in_products' => 1));
	}
    }

    
    protected function _getWarehouse() {
	return Mage::registry('current_temando_warehouse');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Related
     */
    protected function _addColumnFilterToCollection($column) {
	// Set custom filter for in product flag
	if ($column->getId() == 'in_products') {
	    $productIds = $this->_getSelectedProducts();
	    if (empty($productIds)) {
		$productIds = 0;
	    }
	    if ($column->getFilter()->getValue()) {
		$this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
	    } else {
		if ($productIds) {
		    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
		}
	    }
	} else {
	    parent::_addColumnFilterToCollection($column);
	}
	return $this;
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection() {
	
	$whs_id = $this->getRequest()->getParam('id', '0');		
	$collection = Mage::getModel('catalog/product_link')->useRelatedLinks()
		->getProductCollection()
		->addAttributeToSelect('*')
		->joinTable('temando/inventory', 
			    'product_id=entity_id', 
			    array('quantity' => 'quantity', 'inv_id' => 'id'), 
			    '{{table}}.warehouse_id='.$whs_id,
			    'left');
	
	//die($collection->getSelectSql(true));
	
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }

	$this->setCollection($collection);
	return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns() {
	
	$this->addColumn('in_products', array(
	    'header_css_class' => 'a-center',
	    'type' => 'checkbox',
	    'name' => 'in_products',
	    'values' => $this->_getSelectedProducts(),
	    'align' => 'center',
	    'index' => 'entity_id'
	));

	$this->addColumn('entity_id', array(
	    'header' => Mage::helper('temando')->__('ID'),
	    'sortable' => true,
	    'width' => 60,
	    'index' => 'entity_id'
	));
	$this->addColumn('product_name', array(
	    'header' => Mage::helper('temando')->__('Name'),
	    'index' => 'name'
	));

	$this->addColumn('type', array(
	    'header' => Mage::helper('temando')->__('Type'),
	    'width' => 100,
	    'index' => 'type_id',
	    'type' => 'options',
	    'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
	));

	$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
		->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
		->load()
		->toOptionHash();

	$this->addColumn('set_name', array(
	    'header' => Mage::helper('temando')->__('Attrib. Set Name'),
	    'width' => 130,
	    'index' => 'attribute_set_id',
	    'type' => 'options',
	    'options' => $sets,
	));

	$this->addColumn('visibility', array(
	    'header' => Mage::helper('temando')->__('Visibility'),
	    'width' => 90,
	    'index' => 'visibility',
	    'type' => 'options',
	    'options' => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
	));

	$this->addColumn('sku', array(
	    'header' => Mage::helper('temando')->__('SKU'),
	    'width' => 80,
	    'index' => 'sku'
	));

	$this->addColumn('price', array(
	    'header' => Mage::helper('temando')->__('Price'),
	    'type' => 'currency',
	    'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
	    'index' => 'price'
	));
	
	if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header'=> Mage::helper('catalog')->__('Global Qty'),
                    'width' => '100px',
                    'type'  => 'number',
                    'index' => 'qty',
            ));
        }
	
	$this->addColumn('quantity', array(
	    'header' => Mage::helper('temando')->__('Whse Qty'),
	    'sortable' => true,
	    'width' => 60,
	    'index' => 'quantity',
	    'type'  => 'number',
	    'validate_class' => 'validate-number',
	    'editable' => true,
	    'default' => '0',
	));

	return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl() {
	return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/relatedGrid', array('_current' => true));
    }

    /**
     * Retrieve selected related products
     *
     * @return array
     */
    protected function _getSelectedProducts() {

	$products = $this->getRequest()->getPost('products_related');
	if (!is_array($products)) {
	    $products = array_keys($this->getSelectedRelatedProducts());
	}
	return $products;
    }

    /**
     * Retrieve related products with quantities
     *
     * @return array
     */
    public function getSelectedRelatedProducts() {

	$products = array();

	if ($whsProducts = Mage::registry('current_temando_warehouse')->getWhsProducts()) {
	    foreach (unserialize($whsProducts) as $productID => $quantity) {
		$products[$productID] = array('quantity' => $quantity['quantity']);
	    }
	}
	return $products;
    }

}