<?php

class Ewave_Temando_Model_System_Config_Source_Shipment_Service extends Ewave_Temando_Model_System_Config_Source
{
    
    const SAME_DAY	= 1;
    const EXPRESS       = 2;
    const STANDARD      = 3;
    
    protected function _setupOptions()
    {
        $this->_options = array(
            self::SAME_DAY	=> 'Same Day',
            self::EXPRESS       => 'Express',
            self::STANDARD      => 'Standard',
        );
    }
    
}