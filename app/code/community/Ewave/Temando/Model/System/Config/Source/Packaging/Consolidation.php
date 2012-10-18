<?php

/**
 * Packaging logic - Consolidation mode
 *
 * @author martin
 */
class Ewave_Temando_Model_System_Config_Source_Packaging_Consolidation extends Ewave_Temando_Model_System_Config_Source {

    const MAGENTO   = 1;
    const TEMANDO   = 2;
    
    protected function _setupOptions()
    {
	$this->_options = array(
	    self::MAGENTO => 'Use Magento',
	    self::TEMANDO => 'Use Temando',
	);
    }
    
}


