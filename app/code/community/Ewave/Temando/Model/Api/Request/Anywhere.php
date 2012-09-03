<?php

class Ewave_Temando_Model_Api_Request_Anywhere extends Mage_Core_Model_Abstract
{
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/api_request_anywhere');
    }
    
    public function toRequestArray()
    {
        if (!$this->validate()) {
            return false;
        }

        $data = array(
            'itemNature' => 'Domestic',
            'itemMethod' => 'Door to Door',
            'destinationCountry' => $this->getDestinationCountry(),
            'destinationCode' => sprintf("%04d", $this->getDestinationPostcode()),
            'destinationSuburb' => $this->getDestinationCity(),
            'destinationIs' => 'Residence',
            'destinationBusNotifyBefore' => 'N',
            'destinationBusLimitedAccess' => 'N',
            'originBusNotifyBefore' => 'Y',
            'originBusLimitedAccess' => 'N',
        );

        if (Mage::helper('temando')->getLocationName()) {
            $data['originDescription'] = Mage::helper('temando')->getLocationName();
        } else {
            $data['originCountry'] = $this->getOriginCountry();
            $data['originCode'] = $this->getOriginPostcode();
            $data['originSuburb'] = $this->getOriginCity();
            $data['originIs'] = $this->getOriginType();
        }

        if (Mage::helper('temando')->isStreetWithPO($this->getDestinationStreet())) {
            $data['destinationResPostalBox'] = 'Y';
        }

        return $data;
    }
    
    public function validate()
    {
        return
            $this->getOriginCountry() &&
            $this->getOriginPostcode() &&
            $this->getOriginCity() &&
            $this->getOriginType() &&
            $this->getDestinationCountry() &&
            $this->getDestinationPostcode() &&
            $this->getDestinationCity();
    }
    
}
