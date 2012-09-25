<?php

class Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Users extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Set grid params
     *
     */
    public function __construct() {

	parent::__construct();
	$this->setId('related_users_grid');
	$this->setDefaultSort('main_table.user_id');
	$this->setUseAjax(true);
	if ($this->_getWarehouse()->getId()) {
	    $this->setDefaultFilter(array('in_users' => 1));
	}
    }

    
    protected function _getWarehouse() {
	return Mage::registry('current_temando_warehouse');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Users
     */
    protected function _addColumnFilterToCollection($column) {
	// Set custom filter for in user flag
	if ($column->getId() == 'in_users') {
	    $userIds = $this->_getSelectedUsers();
	    if (empty($userIds)) {
		$userIds = 0;
	    }
	    if ($column->getFilter()->getValue()) {
		$this->getCollection()->addFieldToFilter('main_table.user_id', array('in' => $userIds));
	    } else {
		if ($userIds) {
		    $this->getCollection()->addFieldToFilter('main_table.user_id', array('nin' => $userIds));
		}
	    }
	} else if ($column->getId() == 'parent_id') {
	    if ($roleName = $column->getFilter()->getValue()) {
		$coll = Mage::getModel('admin/role')->getCollection()
				->addFieldToFilter('role_name', array('like' => '%'.$roleName.'%'));
		$this->getCollection()->addFieldToFilter('parent_id', array('in' => $coll->getAllIds()));
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
			
	$collection = Mage::getModel('admin/user')->getCollection();
	/* @var $collection Mage_Admin_Model_Mysql4_User_Collection */
	$collection->join('admin/role', '`main_table`.user_id=`admin/role`.user_id', array('role_name', 'parent_id'));

	//die($collection->getSelectSql(true));
	$this->setCollection($collection);
	return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns() {
	
	$this->addColumn('in_users', array(
	    'header_css_class' => 'a-center',
	    'type' => 'checkbox',
	    'name' => 'in_users',
	    'values' => $this->_getSelectedUsers(),
	    'align' => 'center',
	    'index' => 'user_id'
	));

	$this->addColumn('user_id', array(
	    'header' => Mage::helper('temando')->__('User ID'),
	    'sortable' => true,
	    'width' => 60,
	    'index' => 'user_id',
	    'filter_index' => 'main_table.user_id',
	));
	
	$this->addColumn('username', array(
	    'header' => Mage::helper('temando')->__('Username'),
	    'index' => 'username',
	    'width' => 120,
	));

	$this->addColumn('firstname', array(
	    'header' => Mage::helper('temando')->__('First Name'),
	    'width' => 100,
	    'index' => 'firstname',
	));

	$this->addColumn('lastname', array(
	    'header' => Mage::helper('temando')->__('Last Name'),
	    'width' => 100,
	    'index' => 'lastname',
	));

	$this->addColumn('email', array(
	    'header' => Mage::helper('temando')->__('Email'),
	    'width' => 130,
	    'index' => 'email',
	));
	
	$this->addColumn('parent_id', array(
	    'header' => Mage::helper('temando')->__('Role'),
	    'width' => 80,
	    'renderer' => 'Ewave_Temando_Block_Adminhtml_Warehouse_Edit_Tab_Users_Role_Renderer',
	    'index' => 'parent_id',
	));
	
	$this->addColumn('position', array(
	    'header' => Mage::helper('temando')->__('Position'),
	    'sortable' => true,
	    'width' => 60,
	    'index' => 'position',
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
	return $this->getData('grid_url') ? $this->getData('grid_url') : $this->getUrl('*/*/usersGrid', array('_current' => true));
    }

    /**
     * Retrieve selected related users
     *
     * @return array
     */
    protected function _getSelectedUsers() {

	$users = $this->getRequest()->getPost('in_users');
	if (!is_array($users)) {
	    $users = array_keys($this->getSelectedRelatedUsers());
	}
	return $users;
    }

    /**
     * Retrieve related users
     *
     * @return array
     */
    public function getSelectedRelatedUsers() {

	$users = array();

	if ($whsUsers = Mage::registry('current_temando_warehouse')->getWhsUsers()) {
	    foreach (unserialize($whsUsers) as $userid => $position) {
		$users[$userid] = array('position' => $position['position']);
	    }
	}
	return $users;
    }

}