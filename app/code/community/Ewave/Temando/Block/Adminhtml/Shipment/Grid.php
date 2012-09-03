<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('temando/shipment')->getCollection();
	$collection->addFieldToFilter('grid_display', 1);
        $collection->join('sales/order', 'main_table.order_id=`sales/order`.entity_id', array('increment_id', 'created_at', 'shipping_amount'));
        /* @var $collection Ewave_Temando_Model_Shipment */
	
	$statuses = Mage::helper('temando')->getConfigData('shipments_display/shipment_order_statuses');
	$status_arr = explode(',', $statuses);
	
	if(is_array($status_arr) && !empty($status_arr)) {
	    $collection->addFieldToFilter('`sales/order`.status', array('nin' => $status_arr));
	}
	
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('order_number', array(
            'header' => Mage::helper('temando')->__('Order #'),
	    'width' => '100px',
            'index' => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('temando')->__('Purchased On'),
	    'width' => '160px',
	    'type' => 'datetime',
            'index' => 'created_at',
        ));
        
        $this->addColumn('status', array(
            'header' => Mage::helper('temando')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '100px',
            'options' => Mage::getSingleton('temando/system_config_source_shipment_status')->getOptions(),
	    'filter_index' => 'main_table.status',
        ));
        
        $this->addColumn('anticipated_cost', array(
            'header' => Mage::helper('temando')->__('Anticipated Cost'),
            'align' => 'left',
	    'type'  => 'currency',
	    'currency_code' => 'AUD',
            'index' => 'anticipated_cost',
        ));
        
        $this->addColumn('shipping_paid', array(
            'header' => Mage::helper('temando')->__('Shipping Paid'),
            'align' => 'left',
	    'type'  => 'currency',
	    'currency_code' => Mage::app()->getStore()->getCurrentCurrencyCode(),
            'index' => 'shipping_amount',
        ));
	
	$this->addColumn('admin_selected_quote', array(
            'header'    => Mage::helper('temando')->__('Admin Selected Quote'),
	    'type'	=> 'text',
            'renderer'  => new Ewave_Temando_Block_Adminhtml_Shipment_Grid_Renderer_Adminquote(),    
        ));

        $this->addColumn('selected_quote_description', array(
            'header' => Mage::helper('temando')->__('Customer Selected Quote'),
            'align' => 'left',
            'index' => 'customer_selected_quote_description',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('temando')->__('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                'view' => array(
                    'caption' => Mage::helper('temando')->__('View'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction() {
	parent::_prepareMassaction();
	
	$this->getMassactionBlock()->addItem('book_all', array(
             'label'=> Mage::helper('temando')->__('Book Selected Shipments'),
             'url'  => $this->getUrl('*/*/massBook'),
        ));
	
	$this->getMassactionBlock()->addItem('remove_all', array(
             'label'=> Mage::helper('temando')->__('Remove Selected Shipments'),
             'url'  => $this->getUrl('*/*/massRemove'),
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
