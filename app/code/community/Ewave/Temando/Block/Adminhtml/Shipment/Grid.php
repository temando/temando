<?php

class Ewave_Temando_Block_Adminhtml_Shipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
	Mage::getModel('temando/shipment')->getCollection()->fixOrderIds();
	
        $collection = Mage::getModel('temando/shipment')->getCollection();
	$collection->addFieldToFilter('grid_display', 1);
        $collection->join('sales/order', 'main_table.order_id=`sales/order`.entity_id', array('increment_id', 'created_at', 'shipping_amount'));
        /* @var $collection Ewave_Temando_Model_Shipment */
	
	$statuses = Mage::helper('temando')->getConfigData('shipments_display/shipment_order_statuses');
	$status_arr = explode(',', $statuses);
	
	if(is_array($status_arr) && !empty($status_arr)) {
	    $collection->addFieldToFilter('`sales/order`.status', array('nin' => $status_arr));
	}
	
	//per user/warehouse view if Temando 2.0 active
	if(Mage::helper('temando')->isVersion2()) {
	    $currentUser = Mage::getSingleton('admin/session')->getUser();
	    $allowedWarehouses = Mage::getModel('temando/warehouse')->getCollection()
				    ->getAllowedWarehouseIds($currentUser->getId());
	    $collection->addFieldToFilter('warehouse_id', array('in' => $allowedWarehouses));
	}
	
	$collection->setOrder('status', 'ASC')
		   ->setOrder('service_type', 'ASC')
		   ->setOrder('created_at', 'DESC');
	
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _addColumnFilterToCollection($column) {
	if($column->getId() == 'warehouse_id') {
	    $val = $column->getFilter()->getValue();
	    if($val) {
		$col = Mage::getModel('temando/warehouse')->getCollection()
			    ->addFieldToFilter('name', array('like' => '%'.$val.'%'));
		$this->getCollection()->addFieldToFilter('warehouse_id', array('in' => $col->getAllIds()));
	    }
	} else {
	    parent::_addColumnFilterToCollection($column);
	}
	return $this;
    }

    protected function _prepareColumns()
    {
	$this->addColumn('service_type', array(
            'header'    => $this->__('Urgency'),
            'width'     => '120',
            'align'     => 'left',
            'index'     => 'service_type',
            'type'      => 'options',
            'options'   => array(1 => $this->__('Same Day'), 2 => $this->__('Express'), 3 => $this->__('Standard')),
            'frame_callback' => array($this, 'decorateServiceType')
        ));

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

	if(Mage::helper('temando')->isVersion2()) {
	    $this->addColumn('warehouse_id', array(
		'header' => Mage::helper('temando')->__('Origin'),
		'align'  => 'left',
		'index'  => 'warehouse_id',
		'renderer'  => new Ewave_Temando_Block_Adminhtml_Shipment_Grid_Renderer_Origin,
	    ));
	}
	
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
    
    /**
     * Decorate service type column values
     *
     * @return string
     */
    public function decorateServiceType($value, $row, $column, $isExport)
    {
        $cell = '';
	switch($row->getServiceType()) {
	    case Ewave_Temando_Model_System_Config_Source_Shipment_Service::SAME_DAY:
		$cell = '<span class="tmd-grid-severity-critical"><span>'.$value.'</span></span>';
		break;
	    case Ewave_Temando_Model_System_Config_Source_Shipment_Service::EXPRESS:
		$cell = '<span class="tmd-grid-severity-major"><span>'.$value.'</span></span>';
		break;
	    case Ewave_Temando_Model_System_Config_Source_Shipment_Service::STANDARD:
		$cell = '<span class="tmd-grid-severity-minor"><span>'.$value.'</span></span>';
		break;
	}
        return $cell;
    }
    
    protected function _prepareMassaction() {
	parent::_prepareMassaction();
	
	$this->getMassactionBlock()->addItem('book_all', array(
             'label'=> Mage::helper('temando')->__('Book Selected Shipments'),
             'url'  => $this->getUrl('*/*/massBook'),
	     'confirm' => Mage::helper('temando')->__('Are you sure?'),
        ));
	
	$this->getMassactionBlock()->addItem('remove_all', array(
             'label'=> Mage::helper('temando')->__('Remove Selected Shipments'),
             'url'  => $this->getUrl('*/*/massRemove'),
	     'confirm' => Mage::helper('temando')->__('Are you sure?'),
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
