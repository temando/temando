<?php

class Ewave_Temando_Model_System_Config_Source_Version extends Ewave_Temando_Model_System_Config_Source
{
    
    const LEGACY      = 'legacy';
    const VERSION20   = 'version20';
    
    protected function _setupOptions()
    {
        $this->_options = array(
            self::LEGACY     => 'Legacy Temando',
            self::VERSION20  => 'Temando 2.0',
        );
    }
    
}
