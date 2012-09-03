<?php

class Ewave_Temando_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
	$this->_init('temando/rule', 'id');
    }
}
