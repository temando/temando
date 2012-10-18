<?php
class DebugSoapClient extends SoapClient
{


	public function __doRequest($request, $location, $action, $version, $one_way = 0)
	{
		$return = parent::__doRequest($request, $location, $action, $version, $one_way);
//        echo print_r($request); exit;
        /*$fd = fopen('res.zip', 'wb');
        fwrite($fd, $return);
        fclose($fd);
        exit;*/
        echo $request . "\n\n" . $return; exit;
		return $return;
	}


}
class Ewave_Temando_Model_Api_Client extends Mage_Core_Model_Abstract
{
    
    protected $_client = null;

    protected $_is_sand = null;

    public function  __construct()
    {
        parent::__construct();
    }

    public function connect($username = null, $password = null, $sandbox = false, $not_affilate = false)
    {
        $this->_is_sand = $sandbox;
        if ($sandbox) {
            $url = "https://training-api.temando.com/schema/2009_06/server.wsdl";
        } else {
            $url = "https://api.temando.com/schema/2009_06/server.wsdl";
        }
        
        if ($username == null || $username == false) {
            $username = Mage::helper('temando')->getConfigData('general/username');
        }
        if ($password == null || $username == false) {
            $password = Mage::helper('temando')->getConfigData('general/password');
        }

        // The WSDL cache should be set to on to prevent the WSDL being loaded everytime.
        ini_set("soap.wsdl_cache_enabled", "1");

        // Create a new SoapClient referencing the Temando WSDL file.
        $this->_client = new SoapClient($url, array('soap_version' => SOAP_1_2, 'trace' => TRUE));

        // Define the security string that wraps your login details. Due to limitations
        // with the PHP language this header information can only be provided via a string.
        $headerSecurityStr = "<Security><UsernameToken><Username>" . htmlentities($username) . "</Username>".
                             "<Password>" . htmlentities($password) . "</Password></UsernameToken></Security>";

        // Create a new SoapVar using the header security string.
        $headerSecurityVar = new SoapVar($headerSecurityStr, XSD_ANYXML);

        // Create a new SoapHeader containing all your login details.
        $soapHeader = new SoapHeader('wsse:http://schemas.xmlsoap.org/ws/2002/04/secext', 'soapenv:Header', $headerSecurityVar);

        // Add the SoapHeader to your SoapClient.
        $this->_client->__setSoapHeaders(array($soapHeader));
        
        return $this;

    }

    /**
     * Gets quotes for a delivery.
     *
     * @param array $request the request parameters, in an array format.
     *
     * @return array
     */
    public function getQuotesByRequest($request)
    {
        if(!$this->_client) {
            return false;
        }

        if (!$this->_is_sand) {
            $request['clientId'] = Mage::helper('temando')->getClientId();
        }
	
	//Mage::log($request, null, 'temando-request.log', true);
        $response = $this->_client->getQuotesByRequest($request);

	if(!is_array($response->quote)) {
	    $response->quote = array(0 => $response->quote);
	}

        $quotes = array();
        foreach ($response->quote as $quote_details) {
            $quotes[] = Mage::getModel('temando/quote')
                ->loadResponse($quote_details);
        }
        
        return $quotes;
    }

    public function makeBookingByRequest($request)
    {
        if (!$this->_is_sand) {
            $request['clientId'] = Mage::helper('temando')->getClientId();
        }
        if (!$this->_client) {
            return false;
        }

        return $this->_client->makeBookingByRequest($request);
    }

    public function getRequest($request)
    {
        if (!$this->_client) {
            return false;
        }
        
        return $this->_client->getRequest($request);
    }

    public function confirmManifest($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->confirmManifest($request);
    }

    public function getManifest($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->getManifest($request);
    }

    /**
     * get list of location
     *
     * @param $request
     * @return bool
     */
    public function getLocations($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->getLocations($request);
    }

    /**
     * create location
     *
     * @param $request
     * @return bool
     */
    public function createLocation($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->createLocation($request);
    }

    /**
     * update location
     *
     * @param $request
     * @return bool
     */
    public function updateLocation($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->updateLocation($request);
    }
    
    public function cancelRequest($requestId)
    {
	if(!$this->_client && empty($requestId)) {
	    return false;
	}
	
	return $this->_client->cancelRequest(array(
	    'requestId' => $requestId,
	    'reference' => null,
	));
    }

    /**
     * Gets the multiplier for insurance (currently 1%).
     *
     * To add insurance to a quote, the total price should be multiplied by
     * this value.
     */
    public function getInsuranceMultiplier()
    {
        return 1.01; // 1%
    }
    
    public function createClient($request)
    {
        if (!$this->_client) {
            return false;
        }
        
        return $this->_client->createClient($request);
    }

    public function getClient($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->getClient($request);
    }

    public function updateClient($request)
    {
        if (!$this->_client) {
            return false;
        }

        return $this->_client->updateClient($request);
    }
    
}
