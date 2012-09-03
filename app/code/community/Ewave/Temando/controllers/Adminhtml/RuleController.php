<?php

class Ewave_Temando_Adminhtml_RuleController extends Mage_Adminhtml_Controller_Action {
    
    public function indexAction()
    {
	$this->loadLayout()
	     ->_setActiveMenu('temando/rule')
	     ->_addBreadcrumb(Mage::helper('adminhtml')->__('Manage Rules'), Mage::helper('adminhtml')->__('Manage Rules'))
	     ->renderLayout();
    }
    
    public function newAction() {
	$this->_forward('edit');
    }

    public function editAction() {
	$id = $this->getRequest()->getParam('id', null);
	$model = Mage::getModel('temando/rule');
	if ($id) {
	    $model->load((int) $id);
	    if ($model->getId()) {
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if ($data) {
		    $model->setData($data)->setId($id);
		}
	    } else {
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temando')->__('Rule does not exist'));
		$this->_redirect('*/*/');
	    }
	}
	Mage::register('current_temando_rule', $model);

	$this->loadLayout();
	$this->renderLayout();
    }
    
    
    public function saveAction() {
	if ($data = $this->getRequest()->getPost()) {
	    $model = Mage::getModel('temando/rule');
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

	    Mage::getSingleton('adminhtml/session')->setFormData($data);
	    try {
		if ($id) {
		    $model->setId($id);
		}
		$model->save();

		if (!$model->getId()) {
		    Mage::throwException(Mage::helper('temando')->__('Error saving rule'));
		}

		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temando')->__('Rule was successfully saved.'));
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
		$model = Mage::getModel('temando/rule');
		$model->setId($id);
		$model->delete();
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temando')->__('The rule has been deleted.'));
		$this->_redirect('*/*/');
		return;
	    } catch (Exception $e) {
		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
		return;
	    }
	}
	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find the rule to delete.'));
	$this->_redirect('*/*/');
    }
    
    
    
    
}
