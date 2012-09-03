<?php

class Ewave_Temando_Model_Option_Action_Carbon extends Ewave_Temando_Model_Option_Action_Abstract
{
    
    public function apply(&$quote)
    {
        /* @var $quote Ewave_Temando_Model_Quote */
        $price = $quote->getTotalPrice();
        $carbon_price = $quote->getCarbonTotalPrice();
        
        $quote->setTotalPrice($price + $carbon_price);
    }
    
}
