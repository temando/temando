<?php

class Ewave_Temando_Model_System_Config_Source_Labeltype extends Ewave_Temando_Model_System_Config_Source
{

    const NO       = '';
    const STANDARD = 'Standard';
    const THERMAL  = 'Thermal';

    protected function _setupOptions()
    {
        $this->_options = array(
            self::NO       => 'No',
            self::STANDARD => 'Plain Paper',
            self::THERMAL  => 'Thermal',
        );
    }

}
