<?php

class Ewave_Temando_Model_System_Config_Source_Warehouse extends Ewave_Temando_Model_System_Config_Source
{

    const DEFAULT_WAREHOUSE  = 0;

    protected function _setupOptions()
    {
        $this->_options = array(
            self::DEFAULT_WAREHOUSE => 'Default Warehouse',
        );
    }

}
