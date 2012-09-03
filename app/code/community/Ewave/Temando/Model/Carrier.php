<?php

/**
 * @method Ewave_Temando_Model_Carrier setCarrierId()
 * @method Ewave_Temando_Model_Carrier setCompanyName()
 * @method Ewave_Temando_Model_Carrier setCompanyContact()
 * @method Ewave_Temando_Model_Carrier setStreetAddress()
 * @method Ewave_Temando_Model_Carrier setStreetSuburb()
 * @method Ewave_Temando_Model_Carrier setStreetCity()
 * @method Ewave_Temando_Model_Carrier setStreetState()
 * @method Ewave_Temando_Model_Carrier setStreetPostode()
 * @method Ewave_Temando_Model_Carrier setStreetCountry()
 * @method Ewave_Temando_Model_Carrier setPostalAddress()
 * @method Ewave_Temando_Model_Carrier setPostalSuburb()
 * @method Ewave_Temando_Model_Carrier setPostalCity()
 * @method Ewave_Temando_Model_Carrier setPostalState()
 * @method Ewave_Temando_Model_Carrier setPostalPostcode()
 * @method Ewave_Temando_Model_Carrier setPostalCountry()
 * @method Ewave_Temando_Model_Carrier setPhone()
 * @method Ewave_Temando_Model_Carrier setEmail()
 * @method Ewave_Temando_Model_Carrier setWebsite()
 *
 * @method string getCarrierId()
 * @method string getCompanyName()
 * @method string getCompanyContact()
 * @method string getStreetAddress()
 * @method string getStreetSuburb()
 * @method string getStreetCity()
 * @method string getStreetState()
 * @method string getStreetPostode()
 * @method string getStreetCountry()
 * @method string getPostalAddress()
 * @method string getPostalSuburb()
 * @method string getPostalCity()
 * @method string getPostalState()
 * @method string getPostalPostcode()
 * @method string getPostalCountry()
 * @method string getPhone()
 * @method string getEmail()
 * @method string getWebsite()
 */
class Ewave_Temando_Model_Carrier extends Mage_Core_Model_Abstract
{
    
    const FLAT_RATE = 10000;
    const FREE      = 10001;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/carrier');
    }
    
}
