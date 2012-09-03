<?php

class Ewave_Temando_Block_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    
    public function isDynamicPricing()
    {
        $method = Mage::helper('temando')->getConfigData('pricing/method');
        
        $rates = $this->getShippingRates();
        
        if (!array_key_exists('temando', $rates)) {
            return false;
        }
        
        if (count($rates['temando']) === 1) {
            return false;
        }
        
        return 
            $method === 'dynamic' ||
            $method === 'dynamiccheap' ||
            $method === 'dynamicfast' ||
	    $method === 'dynamicfastcheap' ||
	    $method === 'hybrid'
        ;
    }
    
}
