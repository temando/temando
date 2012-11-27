<?php

/**
 * Description of V2
 *
 * @author martin
 */
class Ewave_Temando_Helper_V2 extends Ewave_Temando_Helper_Data {
    
    /**
     * Holds all items on individual order and their split value
     * 
     * @var array 
     */
    protected $_orderItemValues = null;
    
    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $product 
     */
    public function getProductPackages($item, $product) {
	
	$packages = array(); $simpleProductWeight = 0;
	
	if ($product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
	    //try getting from selected simple product first
	    $simpleProduct = $this->getSelectedSimpleFromConfigurable($product, $item);
	    if($simpleProduct->getData('temando_packaging_mode')) {
		//packaging defined - use it
		$packages = $this->getPackages($simpleProduct);
	    }
	    $simpleProductWeight = $simpleProduct->getWeight();
	}
	
	if(!empty($packages)) return $packages;
	
	//all other cases - get parent/simple product packaging
	if($product->getData('temando_packaging_mode')) {
	    $packages = $this->getPackages($product, $simpleProductWeight);
	} 
	
	if(!empty($packages)) return $packages;
	
	//last resort - just use defaults
	return array($this->getDefaultPackage($product, $simpleProductWeight));
	
    }
    
    /**
     * Get product packages - as defined on product level
     * or default from configuration
     * 
     * @param type $product
     * @return type 
     */
    protected function getPackages($product, $simpleProductWeight) {
	$packages = array();
	
	if($product->getData('temando_packaging_mode')) {
	    //packaging defined - use it
	    for($i=1; $i<=5; $i++) {
		$desc = $product->getData("temando_package_{$i}_description");
		if($desc && !empty($desc))
		{
		    $packages[] = array(
			'description' => $product->getData("temando_package_{$i}_description"),
			'packaging' => $product->getData("temando_package_{$i}_type"),
			'fragile' => $product->getData("temando_package_{$i}_fragile"),
			'weight' => $product->getData("temando_package_{$i}_weight"),
			'length' => $product->getData("temando_package_{$i}_length"),
			'width' => $product->getData("temando_package_{$i}_width"),
			'height' => $product->getData("temando_package_{$i}_height"),
		    );
		}
	    }
	} 
	//use defaults if empty
	if(empty($packages)) {
	    $packages[] = $this->getDefaultPackage($product, $simpleProductWeight);
	}
	
	return $packages;
    }
    
    /**
     * Get default package from configured values
     * 
     * @param type $product
     * @return array 
     */
    protected function getDefaultPackage($product, $simpleProductWeight) {
	return array(
	    'description' => $product->getName(),
	    'packaging' => $this->getConfigData('defaults/packaging'),
	    'fragile' => $this->getConfigData('defaults/fragile'),
	    'weight' => $product->getWeight() ? $product->getWeight() : $simpleProductWeight,
	    'length' => $this->getConfigData('defaults/length'),
	    'width' => $this->getConfigData('defaults/width'),
	    'height' => $this->getConfigData('defaults/height')
	);
    }
    
    /**
     * Get simple product from parent configurable product
     * 
     * @param type $product
     * @param type $item
     * @return type 
     */
    public function getSelectedSimpleFromConfigurable($product, $item) {
	if (!is_callable(array($product, 'getTypeInstance')) || !is_callable(array($product->getTypeInstance(), 'getUsedProducts'))) {
	    return $product;
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
	    return $product;
	}

	$_Product = false;
	foreach ($product->getTypeInstance()->getUsedProducts() as $childProduct) {
	    $checkRes = true;
	    foreach ($selectedAttributes as $attributeId => $attributeValue) {
		$code = $product->getTypeInstance()->getAttributeById($attributeId)->getAttributeCode();
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
	    return $product;
	}
	
	return $_Product;
    }
    
    /**
     * Get product package parameters for temando
     *
     * @return array fields of temando params
     */
    public function getProductTemandoParams() {
	
	$return = array('packaging_mode');
	for($i=1; $i<=5; $i++) {
	    $return[] = "package_{$i}_description";
	    $return[] = "package_{$i}_type";
	    $return[] = "package_{$i}_fragile";
	    $return[] = "package_{$i}_weight";
	    $return[] = "package_{$i}_length";
	    $return[] = "package_{$i}_width";
	    $return[] = "package_{$i}_height";
	}
	return $return;
    }
    
    /**
     * Returns applicable dynamic origin based on destination
     * 
     * @param int|string $postcode
     * @param int $storeId
     * @return Ewave_Temando_Model_Warehouse | null
     */
    public function getDynamicOrigin($postcode, $storeId = null)
    {
	return Mage::getSingleton('temando/warehouse')->getCollection()->getOriginByPostcode($postcode, $storeId);
    }
    
    /**
     * Prepares returned packaging information from getQuotes request
     * for save into database table
     * 
     * @param stdClass $anythings
     * @return serialized array of packaging details 
     */
    public function getSerializedPackagingFromApiResponse($anythings) {
	
	//Mage::log($anythings, null, 'anythings.log', true);
	
	if(!is_array($anythings->anything)) {
	    $anythings->anything = array(0 => $anythings->anything);
	}
    
	$packages = array();
	foreach($anythings->anything as $package)
    {
	    $productCounts = array();
	    $customPackageDescription = isset($package->packagingDescription) ? $package->packagingDescription . ': ' : '';
	    
	    isset($package->articles) ? 
		$description = $customPackageDescription . $this->getPackagingDescription($package->articles, $productCounts) : 
		$description = null;
	    
	    $packages[] = array(
		'class'		    => $package->class,
		'mode'		    => $package->mode,
		'packaging'	    => $package->packaging,
		'fragile'	    => $package->qualifierFreightGeneralFragile,
		'distanceMeasurementType'   => $package->distanceMeasurementType,
		'weightMeasurementType'	    => $package->weightMeasurementType,
		'length'	    => $package->length,
		'width'		    => $package->width,
		'height'	    => $package->height,
		'weight'	    => $package->weight,
		'quantity'	    => $package->quantity,
		'packagingDescription'   => isset($package->packagingDescription) ? $package->packagingDescription : '',
		'description'   => $description,//$this->getPackagingDescription(isset($package->articles) ? $package->articles : null)
		'products'	=> $productCounts,
	    );
	}
		
	return serialize($packages);
    }
    
    /**
     * Constructs package description from API getQuotes response (article)
     * with consolidated quantities (ie 1x t-shirt, 2x pants)
     * 
     * @param stdClass $articles
     * @return string Description | empty string if no articles returned 
     */
    public function getPackagingDescription($articles, &$count = array()) {
	
	if($articles) {
	    if(!is_array($articles->article)) {
		$articles->article = array(0 => $articles->article);
	    }
	    
	    $tmp = array();
	    foreach($articles->article as $article) {
		if(array_key_exists($article->sku, $count)) {
		    $count[$article->sku] += 1; 
		} else {
		    $count[$article->sku] = 1;
		}
		$tmp[$article->sku] = $count[$article->sku].'x '.$article->description; 
	    }
	    
	    return implode(',', array_values($tmp));  
	} 
	
	return '';
    }
    
    /**
     * Get values for individual items on order
     * 
     * @param array $allItems - All order items
     * @return array of individual item values 
     */
    public function getOrderItemValues($allItems) {
	$return = array();
	if(!empty($allItems)) {
	    foreach($allItems as $item => $values) {
		$qty = $values['qty'];
		$tot = $values['lineItemTotal'];
		$per1 = round($tot / $qty, 2);

		for($i = 1; $i <= $qty; $i++) {
		    //if last then substract from total to balance out, otherwise use per1 split
		    if($i == $qty) $return[$item][] = $tot - (($qty-1)*$per1);
		    else $return[$item][] = $per1;
		}
	    }
	}
	return $return;
    }
    
    /**
     * Get value of a package - combined value of all items in this consolidated package
     * 
     * @param array $products All products in this package with quantities
     * @param array $allItems All order items
     * @return float 
     */
    public function getConsolidatedPackageValue($products, $allItems)
    {
	if(is_null($this->_orderItemValues)) {
	    $this->_orderItemValues = $this->getOrderItemValues($allItems);
    }
    
	$total = 0;
	if(!empty($products)) {
	    foreach($products as $sku => $qty) {
		for($i = 1; $i <= $qty; $i++) {
		    $val = array_pop($this->_orderItemValues[$sku]);
		    if(!is_null($val))
			$total += (float)$val;
		}
	    }
	}
	
	return $total;
    }
}


