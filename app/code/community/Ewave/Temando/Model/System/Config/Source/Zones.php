<?php

class Ewave_Temando_Model_System_Config_Source_Zones extends Ewave_Temando_Model_System_Config_Source
{
    
    protected $_options;
    
    protected function _setupOptions()
    {
        if(!$this->_options) {
	    $zones = Mage::getModel('temando/zone')->getCollection();
	    /* @var $zones Ewave_Temando_Model_Mysql4_Zone_Collection */

	    foreach ($zones as $zone) {
		/* @var $zone Ewave_Temando_Model_Zone */
		$this->_options[$zone->getId()] = $zone->getName();
	    }
	}
    }
    
}

