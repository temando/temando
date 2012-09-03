<?php

class Ewave_Temando_Model_System_Config_Backend_Form_Field_Required_Location extends Ewave_Temando_Model_System_Config_Backend_Form_Field_Required_Text
{

    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        try {
            $location = '';
            $location_name = Ewave_Temando_Helper_Data::DEFAULT_WAREHOUSE_NAME;
            if (!$location = Mage::helper('temando')->getLocationName()) {
                foreach (Mage::helper('temando')->getLocationList() as $l) {
                    if ($location_name == $l) {
                        $location = $l;
                    }
                }
            }

            $api = Mage::getModel('temando/api_client');
            $api->connect(
                Mage::helper('temando')->getConfigData('general/username'),
                Mage::helper('temando')->getConfigData('general/password'),
                Mage::helper('temando')->getConfigData('general/sandbox'),
                true);

            if (!$location) {
                $result = $api->createLocation(array('location' => Mage::helper('temando')->getLocationWarehouse(0)));
            } else {
                $result = $api->updateLocation(array('location' => Mage::helper('temando')->getLocationWarehouse(0)));
            }

            Mage::getModel('core/config')->saveConfig('temando/general/location', md5(Mage::helper('temando')->getConfigData('general/username')) . ":" . $location_name, 'default', 0);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }

        return parent::_afterSave();
    }
    
}
