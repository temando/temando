<?php

class Ewave_Temando_Model_System_Config_Source_Rule_Action_Filter 
    extends Ewave_Temando_Model_System_Config_Source {
    
    const DYNAMIC_ALL                  = 1;
    const DYNAMIC_FASTEST              = 2;
    const DYNAMIC_CHEAPEST             = 3;
    const DYNAMIC_FASTEST_AND_CHEAPEST = 4;
    
    protected function _setupOptions()
    {
        $this->_options = array(
            self::DYNAMIC_ALL                  => 'All Quotes',
            self::DYNAMIC_CHEAPEST             => 'Cheapest only',
            self::DYNAMIC_FASTEST              => 'Fastest only',
            self::DYNAMIC_FASTEST_AND_CHEAPEST => 'Cheapest and Fastest only',
        );
    }
    
}