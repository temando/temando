<?php


class Ewave_Temando_Block_Product_Shipping_Methods extends Mage_Core_Block_Template
{

    public function getEstimateRates()
    {
        return Mage::registry('product_rates');
    }


    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }

    public function formatPrice($price)
    {
        return Mage::app()->getStore()->convertPrice($price, true);
    }
}
