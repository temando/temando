<?php

class Ewave_Temando_Helper_Data extends Mage_Core_Helper_Abstract {

    const DEFAULT_WAREHOUSE_NAME = 'Default Warehouse';

    protected $_location_list = array();

    public function isVersion2() {
	return ($this->getConfigData('version/version_used')
		== Ewave_Temando_Model_System_Config_Source_Version::VERSION20);
    }

    /**
     * Gets the origin address details.
     *
     * @return Varien_Object
     */
    public function getOrigin() {
	$origin = new Varien_Object();

	$origin
		->setCountry($this->getConfigData('origin/country'))
		->setPostcode($this->getConfigData('origin/postcode'))
		->setCity($this->getConfigData('origin/city'))
		->setType($this->getConfigData('origin/type'))
		->setContactName($this->getConfigData('origin/contact_name'))
		->setCompanyName($this->getConfigData('origin/company_name'))
		->setStreet($this->getConfigData('origin/street'))
		->setState($this->getConfigData('origin/region'))
		->setPhone1($this->getConfigData('origin/phone1'))
		->setPhone2($this->getConfigData('origin/phone2'))
		->setFax($this->getConfigData('origin/fax'))
		->setEmail($this->getConfigData('origin/email'));
	return $origin;
    }

    public function fetchAllCarrier($carrierQuote, $validCarrier) {
	$result = array();
	if (count($carrierQuote) > 0) {
	    foreach ($carrierQuote as $carreirQuotes) {
		foreach ($validCarrier as $key => $value) {
		    if ($key == $carreirQuotes->carrier->id) {
			$result[$key] = array(
			    "carreirId" => $carreirQuotes->carrier->id,
			    "carrierName" => $carreirQuotes->carrier->companyName,
			    "currency" => $carreirQuotes->currency,
			    "deliveryMethod" => $carreirQuotes->deliveryMethod,
			    "etaFrom" => $carreirQuotes->etaFrom,
			    "etaTo" => $carreirQuotes->etaTo,
			    "guaranteedEta" => $carreirQuotes->guaranteedEta,
			    "lowestprice" => $carreirQuotes->totalPrice,
			    "basePrice" => $carreirQuotes->basePrice,
			    "tax" => $carreirQuotes->tax
			);
		    }
		}
	    }
	    return $result;
	}
    }

    /**
     * Retrieves an element from the module configuration data.
     *
     * @param string $field
     */
    public function getConfigData($field) {
	$path = 'temando/' . $field;
	return Mage::getStoreConfig($path);
    }

    // TODO: load from Magento countries
    public function getCountries() {
	$opt = array('AU' => 'Australia');

	return $opt;
    }

    // TODO: load from Magento regions
    public function getStates() {
	$opt = array(
	    'ACT' => 'Australian Capital Territory',
	    'NSW' => 'New South Wales',
	    'NT' => 'Northern Territory',
	    'QLD' => 'Queensland',
	    'SA' => 'South Australia',
	    'VIC' => 'Victoria',
	    'WA' => 'Western Australia',
	    'TAS' => 'Tasmania'
	);

	return $opt;
    }

    /**
     * Gets the date when a package will be ready to ship. Adjusts dates so
     * that they always fall on a weekday.
     *
     * @param <type> $ready_time timestamp for when the package will be ready
     * to ship, defaults to 10 days from current date
     */
    public function getReadyDate($ready_time = NULL) {
	if (is_null($ready_time)) {
	    $ready_time = strtotime('+10 days');
	}
	if (is_numeric($ready_time) && $ready_time >= strtotime(date('Y-m-d'))) {
	    $weekend_days = array('6', '7');
	    while (in_array(date('N', $ready_time), $weekend_days)) {
		$ready_time = strtotime('+1 day', $ready_time);
	    }
	    return $ready_time;
	}
    }

    /**
     * Apply temando request params (length, width, etc to product item)
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $product
     * @return void
     */
    public function applyTemandoParamsToProductByItem($item, $product) {
	return $this->_applySimpleParamsToProduct($item, $product)
			->_applyDefaultParamsToProduct($product);
    }

    /**
     * Aplly Simple product parameters for product if no Temando parameters setup
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $product
     * @return void
     */
    protected function _applySimpleParamsToProduct($item, $product) {
	if (!$item || !is_object($item)) {
	    return $this;
	}

	$item_product = $product;
	if ($item_product->getTypeId() != Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
	    return $this;
	}

	if (!is_callable(array($item_product, 'getTypeInstance')) || !is_callable(array($item_product->getTypeInstance(), 'getUsedProducts'))) {
	    return $this;
	}

	$selectedAttributes = array();
	$_attributes = $item->getOptionByCode('attributes');
	if (!$_attributes) {
	    $_attributes = $item->getProductOptionByCode('info_buyRequest');
	    if ($_attributes && isset($_attributes['super_attribute'])) {
		$selectedAttributes = $_attributes['super_attribute'];
	    }
	} else {
	    $selectedAttributes = unserialize($_attributes->getValue());
	}

	if (!count($selectedAttributes)) {
	    return $this;
	}

	$_Product = false;
	foreach ($item_product->getTypeInstance()->getUsedProducts() as $childProduct) {
	    $checkRes = true;
	    foreach ($selectedAttributes as $attributeId => $attributeValue) {
		$code = $item_product->getTypeInstance()->getAttributeById($attributeId)->getAttributeCode();
		if ($childProduct->getData($code) != $attributeValue) {
		    $checkRes = false;
		    break;
		}
	    }

	    if ($checkRes) {
		$_Product = $childProduct;
		break;
	    }
	}

	if (!$_Product) {
	    return $this;
	}

	$fields = $this->getProductTemandoParams();
	$product->setWeight($_Product->getWeight());
	foreach ($fields as $field) {
	    if (is_null($product->getData('temando_' . $field))) {
		$product->setData('temando_' . $field, $_Product->getData('temando_' . $field));
	    }
	}

	return $this;
    }

    /**
     * Aplly default Temando parameters for product if no Temando parameters setup
     *
     * @param Mage_Catalog_Model_Product $product
     * @return void
     */
    protected function _applyDefaultParamsToProduct($product) {
	$fields = $this->getProductTemandoParams();
	foreach ($fields as $field) {
	    if ($field == 'packaging') {
		$_temp = $product->getData('temando_' . $field);
		if (!is_null($_temp) && preg_match('/[^0-9]/', $_temp)) {
		    $product->setData('temando_' . $field, $this->getConfigData('defaults/' . $field));
		}
	    }

	    if (is_null($product->getData('temando_' . $field)) || ('' === $product->getData('temando_' . $field))) {
		$product->setData('temando_' . $field, $this->getConfigData('defaults/' . $field));
	    }
	}

	return $this;
    }

    /**
     * Get product parameters for temando
     *
     * @return array fields of temando params
     */
    public function getProductTemandoParams() {
	return array('height', 'length', 'width', 'packaging', 'fragile');
    }

    // TODO: load from config models
    public function getFragile() {

	return array("Y" => 'Yes', "N" => 'No',);
    }

    // TODO: not required
    public function getProducts() {
	$result = array();
	//$store = Mage::app()->getStore(1);
	$products = Mage::getResourceModel('catalog/product_collection')
		->addAttributeToSelect('*')
		->load();
	//         ->setStore($store);

	foreach ($products->getData() as $key => $val) {
	    $p = Mage::getModel('catalog/product')->load($val['entity_id']);
	    $a = $p->getData();
	    $result[$a['entity_id']] = $a['name'] . " - [" . $a['sku'] . "] ";
	}

	return $result;
    }

    public function getGramsWeight($anything) {
	$return = $anything['weight'];
	switch ($anything['weightMeasurementType']) {
	    case Ewave_Temando_Model_System_Config_Source_Unit_Weight::GRAMS:
		$return = $anything['weight'];
		break;
	    case Ewave_Temando_Model_System_Config_Source_Unit_Weight::KILOGRAMS:
		$return = $anything['weight'] * 1000;
		break;
	    case Ewave_Temando_Model_System_Config_Source_Unit_Weight::POUNDS:
		$return = $anything['weight'] * 453.5923;
		break;
	    case Ewave_Temando_Model_System_Config_Source_Unit_Weight::OUNCES:
		$return = $anything['weight'] * 28.3495;
		break;
	    default:
		// exception?
		return $return;
		break;
	}

	return (int) $return;
    }

    public function convertWeightToGrams($weight, $unit) {
	$return = $weight;
	switch ($unit) {
	    case Ewave_Temando_Model_System_Config_Source_Unit_Weight::GRAMS:
		$return = $weight;
		break;
	    case Ewave_Temando_Model_System_Config_Source_Unit_Weight::KILOGRAMS:
		$return = $weight * 1000;
		break;
	    case Ewave_Temando_Model_System_Config_Source_Unit_Weight::POUNDS:
		$return = $weight * 453.5923;
		break;
	    case Ewave_Temando_Model_System_Config_Source_Unit_Weight::OUNCES:
		$return = $weight * 28.3495;
		break;
	    default:
		// exception?
		break;
	}

	return $return;
    }

    public function canSignUp() {
	if ($this->getConfigData('general/username') && !$this->getConfigData('general/sandbox')) {
	    return false;
	}

	return true;
    }

    public function getClientId() {
	return $this->getConfigData('general/client');
    }

    public function getSuggestionsCache($words) {
	if (is_file($this->getFileCacheByWords($words))) {
	    return file_get_contents($this->getFileCacheByWords($words));
	}

	return false;
    }

    public function getFileCacheByWords($words) {
	$key = md5(strtolower(join('_', $words)));
	$path = Mage::getBaseDir('cache');
	return $path . DS . 'temando' . DS . $key[0] . DS . $key[1] . DS . $key . '.cache';
    }

    public function setSuggestionsCache($words, $value) {
	if (strlen(join(' ', $words)) > 4) {
	    return;
	}

	$file = $this->getFileCacheByWords($words);
	$dir = dirname($file);
	if (!is_dir($dir)) {
	    mkdir($dir, 0777, true);
	}

	$fd = @fopen($file, 'w');
	if (!$fd) {
	    return;
	}

	$size = fwrite($fd, $value);
	fclose($fd);
	if ($size != strlen($value)) {
	    unlink($file);
	}
    }

    public function getSessionRegion() {
	$data = Mage::getSingleton('customer/session')->getData('estimate_product_shipping');
	if ($data) {
	    return Mage::getModel('directory/region')->load($data['region_id'])->getName();
	}

	return null;
    }

    public function getSessionCity() {
	$data = Mage::getSingleton('customer/session')->getData('estimate_product_shipping');
	if ($data) {
	    return $data['city'];
	}

	return null;
    }

    public function getSessionPostcode() {
	$data = Mage::getSingleton('customer/session')->getData('estimate_product_shipping');
	if ($data) {
	    return $data['postcode'];
	}

	return null;
    }

    public function getSessionRegionId() {
	$data = Mage::getSingleton('customer/session')->getData('estimate_product_shipping');
	if ($data) {
	    return $data['region_id'];
	}

	return null;
    }

    public function getLocationName() {
	$username = $this->getConfigData('general/username');
	$location = $this->getConfigData('general/location');
	if (!$username) {
	    return '';
	}

	$return = '';
	if ($location) {
	    $_t = explode(':', $location, 2);
	    if (count($_t) == 2) {
		if (md5($username) == $_t[0]) {
		    $return = $_t[1];
		}
	    }
	}

	return $return;
    }

    public function getLocationWarehouse($warehouse_id) {
	return array(
	    'description' => $warehouse_id ? $this->getConfigData('origin/company_name') ? $this->getConfigData('origin/company_name') : $this->getConfigData('origin/contact_name') : self::DEFAULT_WAREHOUSE_NAME,
	    'contactName' => $this->getConfigData('origin/contact_name'),
	    'type' => "Origin",
	    'companyName' => $this->getConfigData('origin/company_name'),
	    'street' => $this->getConfigData('origin/street'),
	    'suburb' => $this->getConfigData('origin/city'),
	    'state' => $this->getConfigData('origin/region'),
	    'code' => $this->getConfigData('origin/postcode'),
	    'country' => $this->getConfigData('origin/country'),
	    'phone1' => $this->getConfigData('origin/phone1'),
	    'fax' => $this->getConfigData('origin/fax'),
	    'email' => $this->getConfigData('origin/email'),
	);
    }

    public function getLocationList() {
	if (count($this->_location_list)) {
	    return $this->_location_list;
	}

	$return = array();
	try {
	    $api = Mage::getModel('temando/api_client');
	    $api->connect(
		    Mage::helper('temando')->getConfigData('general/username'), Mage::helper('temando')->getConfigData('general/password'), Mage::helper('temando')->getConfigData('general/sandbox'), true);
	    $result = $api->getLocations(array('type' => 'Origin'));
	    if (!$result) {
		throw new Exception('Cannot send request');
	    }

	    if ($result->locations && $result->locations->location) {
		if (!is_array($result->locations->location)) {
		    $return[(string) $result->locations->location->description] = (string) $result->locations->location->description;
		} else {
		    foreach ($result->locations->location as $v) {
			$return[(string) $v->description] = (string) $v->description;
		    }
		}
	    }
	} catch (Exception $e) {
	    Mage::log($e->getMessage());
	}

	$this->_location_list = $return;
	return $return;
    }

    public function isStreetWithPO($street) {
	if (!is_string($street)) {
	    return false;
	}

	if (preg_match('/p[\. ]*o[\.]* ?box/', strtolower($street))) {
	    return true;
	}

	$templates = array('PO Box', 'P.O. Box', 'P.O Box', 'PO. Box', 'p o box');
	foreach ($templates as $t) {
	    if (strpos(strtolower($street), strtolower($t)) !== false) {
		return true;
	    }
	}

	return false;
    }

    public function isQuoteDynamic($quote_id) {
	$fixed_carriers = array(
	    Ewave_Temando_Model_Carrier::FLAT_RATE,
	    Ewave_Temando_Model_Carrier::FREE,
	    Ewave_Temando_Model_Hybrid::METHOD_CODE
	);

	if (in_array($quote_id, $fixed_carriers)) {
	    return false;
	}

	return true;
    }

    public function ceil_dec($number, $precision, $separator = '.') {
	$numberpart = explode($separator, $number);
	$numberpart[1] = substr_replace($numberpart[1], $separator, $precision, 0);
	if ($numberpart[0] >= 0) {
	    $numberpart[1] = ceil($numberpart[1]);
	} else {
	    $numberpart[1] = floor($numberpart[1]);
	}

	$ceil_number = array($numberpart[0], $numberpart[1]);
	return implode($separator, $ceil_number);
    }

}