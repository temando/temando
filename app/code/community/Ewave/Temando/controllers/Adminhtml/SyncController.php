<?php

class Ewave_Temando_Adminhtml_SyncController extends Mage_Adminhtml_Controller_Action {

    public function locationsAction() {

	try {
	    $api = Mage::getModel('temando/api_client');
	    $api->connect(
		    Mage::helper('temando')->getConfigData('general/username'), Mage::helper('temando')->getConfigData('general/password'), Mage::helper('temando')->getConfigData('general/sandbox'), true);
	    $result = $api->getLocations(array('type' => 'Origin', 'description' => 'Default Warehouse'));
	    var_dump($result);
	} catch (Exception $e) {
	    echo 'ERROR<br/>'; var_dump($e);
	}
	die();
	$locations = array();
	//get all locations from API
	try {
	    $api = Mage::getModel('temando/api_client');
	    $api->connect(
		    Mage::helper('temando')->getConfigData('general/username'), Mage::helper('temando')->getConfigData('general/password'), Mage::helper('temando')->getConfigData('general/sandbox'), true);
	    $result = $api->getLocations(array('type' => 'Origin'));
	    if ($result) {
		foreach ($result->locations as $location) {
		    //map locations to temp objects
		}
	    }

	    //update & create locations from temp object
	} catch (Exception $e) {
	    Mage::log('Sync location error: ' . $e->getMessage(), null, 'temando-sync.log', true);
	    $this->_getSession()->addError(Mage::helper('temando')->__('Synchronization failed, cannot send request'));
	    $this->_redirect('*/adminhtml_warehouse/index');
	    return;
	}
    }

}

