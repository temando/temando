<?php

class Ewave_Temando_Model_Option_Action_Insurance extends Ewave_Temando_Model_Option_Action_Abstract
{
    
    public function apply(&$quote)
    {
        /* @var $quote Ewave_Temando_Model_Quote */
        $price = $quote->getTotalPrice();
        $insurance_price = $quote->getInsuranceTotalPrice();
        
        $quote->setTotalPrice($price + $insurance_price);
    }
    
}
