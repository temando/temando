<?php

/**
 * @method string getShipmentId()
 * @method string getComment()
 * @method string getQty()
 * @method string getLength()
 * @method string getWidth()
 * @method string getHeight()
 * @method string getMeasureUnit()
 * @method string getWeight()
 * @method string getWeightUnit()
 * @method string getFragile()
 *
 * @method Ewave_Temando_Model_Box setShipmentId()
 * @method Ewave_Temando_Model_Box setComment()
 * @method Ewave_Temando_Model_Box setQty()
 * @method Ewave_Temando_Model_Box setLength()
 * @method Ewave_Temando_Model_Box setWidth()
 * @method Ewave_Temando_Model_Box setHeight()
 * @method Ewave_Temando_Model_Box setMeasureUnit()
 * @method Ewave_Temando_Model_Box setWeight()
 * @method Ewave_Temando_Model_Box setWeightUnit()
 * @method Ewave_Temando_Model_Box setFragile()
 */
class Ewave_Temando_Model_Box extends Mage_Core_Model_Abstract
{
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/box');
    }
    
}
