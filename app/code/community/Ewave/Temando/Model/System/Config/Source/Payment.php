<?php

class Ewave_Temando_Model_System_Config_Source_Payment extends Ewave_Temando_Model_System_Config_Source
{
    
    const CREDIT  = 'Credit';
    const ACCOUNT = 'Account';
    
    protected function _setupOptions()
    {
        $this->_options = array(
            self::CREDIT  => 'Credit',
            self::ACCOUNT => 'Account'
        );
    }
    
}
