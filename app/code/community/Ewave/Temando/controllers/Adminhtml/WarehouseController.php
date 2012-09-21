<?php

/**
 * Description of WarehouseController
 *
 * @author martin
 */
class Ewave_Temando_Adminhtml_WarehouseController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
	$this->loadLayout()->renderLayout();
    }

    public function editAction() {
	$id = $this->getRequest()->getParam('id', null);
	$model = Mage::getModel('temando/warehouse');
	if ($id) {
	    $model->load((int) $id);
	    if ($model->getId()) {
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if ($data) {
		    $model->setData($data)->setId($id);
		}
	    } else {
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temando')->__('Warehouse does not exist'));
		$this->_redirect('*/*/');
	    }
	}
	Mage::register('current_temando_warehouse', $model);

	$this->loadLayout();
	$this->renderLayout();
    }

    public function newAction() {
	$this->_forward('edit');
    }

    public function saveAction() {
	if ($data = $this->getRequest()->getPost()) {
	    $model = Mage::getModel('temando/warehouse');
	    $id = $this->getRequest()->getParam('id');
	    if ($id) {
		$model->load($id);
	    }

	    //convert arrays to string
	    foreach ($data as $key => $val) {
		//skip quantities
		if($key == 'links') continue;
		
		if (is_array($val)) {
		    $data[$key] = implode(',', $val);
		}
	    }
	    
	    //related products
	    $links = $this->getRequest()->getPost('links');
	    $products = '';
	    if (isset($links['related'])) {
		$products = Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['related']);
		$data['whs_products'] = serialize($products);
	    }

	    $model->setData($data);

	    Mage::getSingleton('adminhtml/session')->setFormData($data);
	    try {
		if ($id) {
		    $model->setId($id);
		}
		$model->save();
		/* @var $model Ewave_Temando_Model_Warehouse */
		
		//update warehouse quantities if any changes
		if(is_array($products)) {
		    Mage::getModel('temando/inventory')->getCollection()
			->updateProductQuantities($products, $id);
		}	

		if (!$model->getId()) {
		    Mage::throwException(Mage::helper('temando')->__('Error saving warehouse'));
		}

		//sync with temando.com
		$request['location'] = $model->toCreateLocationRequestArray();		
		try {
		    $api = Mage::getModel('temando/api_v2_client');
		    $api->connect(
			Mage::helper('temando')->getConfigData('general/username'),
			Mage::helper('temando')->getConfigData('general/password'),
			Mage::helper('temando')->getConfigData('general/sandbox'));
		    $result = $api->getLocations(array('type' => 'Origin', 'clientId' => Mage::helper('temando')->getClientId(), 'description' => $model->getName()));
		    if($result && isset($result->locations->location)) {
			//location exists = update
			$api->updateLocation($request);
		    }
		} catch (Exception $e) {
		    try {
			$result = $api->createLocation($request);
		    } catch(Exception $e) {
			//cannot create location
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temando')->__('An error occured when synchronizing with temando.com'));
		    }
		}
		
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temando')->__('Warehouse was successfully saved.'));
		Mage::getSingleton('adminhtml/session')->setFormData(false);

		// The following line decides if it is a "save" or "save and continue"
		if ($this->getRequest()->getParam('back')) {
		    $this->_redirect('*/*/edit', array('id' => $model->getId()));
		} else {
		    $this->_redirect('*/*/');
		}
	    } catch (Exception $e) {
		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		if ($model && $model->getId()) {
		    $this->_redirect('*/*/edit', array('id' => $model->getId()));
		} else {
		    $this->_redirect('*/*/');
		}
	    }

	    return;
	}
	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temando')->__('No data found to save'));
	$this->_redirect('*/*/');
    }

    public function deleteAction() {
	$id = $this->getRequest()->getParam('id');
	$model = Mage::getModel('temando/warehouse')->load($id);

	if ($model->getId() == $id) {
	    try {
		$model->delete();
		$this->_getSession()->addSuccess(Mage::helper('temando')->__('The warehouse has been deleted.'));
		$this->_redirect('*/*/');
		return;
	    } catch (Exception $e) {
		$this->_getSession()->addError(Mage::helper('temando')->__('Error deleting warehouse.'));
		$this->_redirect('*/*/edit', array('id' => $id));
		return;
	    }
	} else {
	    $this->_getSession()->addError('Invalid warehouse id supplied. Warehouse does not exist.');
	    $this->_redirect('*/*/');
	    return;
	}
    }

    /**
     * Get related products grid and serializer block
     */
    public function relatedAction() {

	//$this->_initProduct();
	$this->_initWarehouse();
	$this->loadLayout();
	$this->getLayout()->getBlock('temando.warehouse.product.edit.tab.related');
	$this->renderLayout();
    }

    /**
     * Get related products grid
     */
    public function relatedGridAction() {
	//$this->_initProduct();
	$this->_initWarehouse();
	$this->loadLayout();
	$this->getLayout()->getBlock('catalog.product.edit.tab.related');
	$this->renderLayout();
    }
    
    /**
     * Initialize product from request parameters
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct() {
	$this->_title($this->__('Warehouse'))
		->_title($this->__('Manage Products'));

	$productId = (int) $this->getRequest()->getParam('id');
	$product = Mage::getModel('catalog/product');

	$product->setData('_edit_mode', true);
	if ($productId) {
	    $product->load($productId);
	}

	Mage::register('product', $product);
	return $product;
    }
    
    /**
     * Initialize product from request parameters
     *
     * @return Ewave_Temando_Model_Warehouse
     */
    protected function _initWarehouse() {
	$this->_title($this->__('Warehouse'))
		->_title($this->__('Manage Products'));

	$warehouseId = (int) $this->getRequest()->getParam('id');
	$warehouse = Mage::getModel('temando/warehouse');

	$warehouse->setData('_edit_mode', true);
	if ($warehouseId) {
	    $warehouse->load($warehouseId);
	}

	Mage::register('current_temando_warehouse', $warehouse);
	return $warehouse;
    }

}

