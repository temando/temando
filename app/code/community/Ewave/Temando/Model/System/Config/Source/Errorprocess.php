<?php

class Ewave_Temando_Model_System_Config_Source_Errorprocess extends Ewave_Temando_Model_System_Config_Source
{

    const VIEW  = 'view';
    const FLAT  = 'flat';
    const CUST	= 'custom';

    protected function _setupOptions()
    {
        $this->_options = array(
            self::FLAT  => 'Show flat rate',
            self::VIEW  => 'Show error message',
	    self::CUST	=> 'Show custom message',
        );
    }

}
