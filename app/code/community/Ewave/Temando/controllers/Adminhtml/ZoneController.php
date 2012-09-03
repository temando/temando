<?php

class Ewave_Temando_Adminhtml_ZoneController extends Mage_Adminhtml_Controller_Action {
    
    public function indexAction()
    {
	$this->loadLayout()
	     ->_setActiveMenu('temando/zone')
	     ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Zones'), Mage::helper('adminhtml')->__('Manage Zones'))
	     ->renderLayout();
    }
    
    public function newAction() {
	$this->_forward('edit');
    }

    public function editAction() {
	$id = $this->getRequest()->getParam('id', null);
	$model = Mage::getModel('temando/zone');
	if ($id) {
	    $model->load((int) $id);
	    if ($model->getId()) {
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if ($data) {
		    $model->setData($data)->setId($id);
		}
	    } else {
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temando')->__('Zone does not exist'));
		$this->_redirect('*/*/');
	    }
	}
	Mage::register('current_temando_zone', $model);

	$this->loadLayout();
	$this->renderLayout();
    }
    
    
    public function saveAction() {
	if ($data = $this->getRequest()->getPost()) {
	    $model = Mage::getModel('temando/zone');
	    $id = $this->getRequest()->getParam('id');
	    if ($id) {
		$model->load($id);
	    }
	    
	    //convert arrays to string
	    foreach($data as $key => $val) {
		if(is_array($val)) {
		    $data[$key] = implode(',', $val);
		}
	    }
	    
	    $model->setData($data);
	    
	    //VALIDATE RANGES

	    Mage::getSingleton('adminhtml/session')->setFormData($data);
	    try {
		if ($id) {
		    $model->setId($id);
		}
		$model->save();

		if (!$model->getId()) {
		    Mage::throwException(Mage::helper('temando')->__('Error saving zone'));
		}

		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temando')->__('Zone was successfully saved.'));
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
	if ($id = $this->getRequest()->getParam('id')) {
	    try {
		$model = Mage::getModel('temando/zone');
		$model->setId($id);
		$model->delete();
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temando')->__('The zone has been deleted.'));
		$this->_redirect('*/*/');
		return;
	    } catch (Exception $e) {
		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
		return;
	    }
	}
	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the zone to delete.'));
	$this->_redirect('*/*/');
    }
    
    
    public function massDeleteAction()
    {
        $params = $this->getRequest()->getParams();
	if(!isset($params['massaction']) || !is_array($params['massaction']) || empty($params['massaction'])) {
	    $this->getSession()->addError(Mage::helper('temando')->__('No zones selected for booking.'));
	    $this->_redirect('*/*/');
	}
	
	$zone_ids = $params['massaction'];
	$notices = array();
	
	$count = 0;
	foreach($zone_ids as $id) {
	    $zone = Mage::getModel('temando/zone')->load($id);
	    
	    if(!$zone->getId()) {
		$notices[] = "Zone ID $id not found.";
	    } else {
		//zone loaded = delete
		$zone->delete();
		$count++;
	    }
	}
	
	if(!empty($notices)) {
	    foreach($notices as $notice)
		$this->_getSession()->addError($notice);
	}
	
	if($count) {
	    $this->_getSession()->addSuccess(
		$this->__('Total of %d record(s) have been deleted.', $count)
	    );
	}
	
	$this->_redirect('*/*/index');
       
    }	
    
}
