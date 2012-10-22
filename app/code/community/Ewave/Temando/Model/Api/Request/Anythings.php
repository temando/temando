<?php

class Ewave_Temando_Model_Api_Request_Anythings extends Mage_Core_Model_Abstract
{
    
    /**
     * @var array
     */
    protected $_anythings;

    protected $_need_optimize = false;

    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/api_request_anythings');
        $this->_anythings = array();
    }
    
    public function setItems($items)
    {
        $this->_anythings = array();
        foreach ($items as $item) {
            if ($item->getParentItem() || $item->getIsVirtual()) {
                // do not add child products or virtual items
                continue;
            }

            if ($item->getProduct() && $item->getProduct()->isVirtual()) {
                // do not add virtual product
                continue;
            }

            if ($item->getFreeShipping()) {
                continue;
            }

            $this->addItem($item);
        }
        return $this;
    }
    
    public function addItem($item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Item || $item instanceof Mage_Sales_Model_Order_Item || $item instanceof Mage_Sales_Model_Quote_Address_Item || $item instanceof Ewave_Temando_Model_Box) {
            if ($item instanceof Mage_Sales_Model_Quote_Item || $item instanceof Mage_Sales_Model_Quote_Address_Item || $item instanceof Mage_Sales_Model_Order_Item) {
                $this->_need_optimize = true;
            }

            $this->_anythings[] = Mage::getModel('temando/api_request_anything')
                ->setItem($item);
        }
        return $this;
    }
    
    public function getItems()
    {
        return $this->_anythings;
    }
    
    public function validate()
    {
        if (!count($this->_anythings)) {
            // no child items
            return false;
        }
        
        // validate each child item
        foreach ($this->_anythings as $anything) {
            /* @var $anything Ewave_Temando_Model_Api_Request_Anything */
            if (!$anything->validate()) {
                return false;
            }
        }
        return true;
    }
    
    public function toRequestArray()
    {
        if (!$this->validate()) {
            return false;
        }

        $only_satchel_and_carton = true;
        $has_carton = false;
        $output = array();
        foreach ($this->_anythings as $anything) {
            $request = $anything->toRequestArray();
			//convert weight to grams
			$request['weight'] = Mage::helper('temando')->convertWeightToGrams($request['weight'], $request['weightMeasurementType']);
			$request['weightMeasurementType'] = Ewave_Temando_Model_System_Config_Source_Unit_Weight::GRAMS;
	    
            if ($request && !in_array($request['packaging'], array('Satchel/Bag','Carton'))) {
                $only_satchel_and_carton = false;
            }

            if ($request && ($request['packaging'] == 'Carton')) {
                $has_carton = true;
            }

            if ($this->_need_optimize && $request && in_array($request['packaging'], array('Satchel/Bag','Parcel'))) {
                $grams_weight = Mage::helper('temando')->getGramsWeight($request);
                // locate all items or add new box
                for ($i = 0; $i < $request['quantity']; $i++) {
                    $adding = false;
                    $found = false;
                    foreach ($output as $k => $v) {
                        $cur_weight = Mage::helper('temando')->getGramsWeight($v);
                        if ($v && ($v['packaging'] == $request['packaging']) && (5000 > ($grams_weight + $cur_weight))) {
                            if ($v['quantity'] > 1) {
                                $v['quantity']--;
                                $adding = $v;
                                $adding['quantity'] = 1;
                                $adding['weight'] = $grams_weight + $cur_weight;
                                $adding['weightMeasurementType'] = Ewave_Temando_Model_System_Config_Source_Unit_Weight::GRAMS;
                            } else {
                                $v['weight'] = $grams_weight + $cur_weight;
                                $v['weightMeasurementType'] = Ewave_Temando_Model_System_Config_Source_Unit_Weight::GRAMS;
                            }

                            $found = true;
                            $output[$k] = $v;
                            break;
                        }
                    }

                    if (!$found) {
                        $_t = $request;
                        $_t['quantity'] = 1;
                        $_t['grams'] = $grams_weight;
                        $adding = $_t;
                    }

                    if ($adding) {
                        $output[] = $adding;
                    }
                }
            } else {
                $output[] = $request;
            }
        }

        if ($only_satchel_and_carton && $has_carton) {
            foreach ($output as $k => $v) {
                $v['packaging'] = 'Carton';
                $output[$k] = $v;
            }
        }

        return $output;
    }    
}
