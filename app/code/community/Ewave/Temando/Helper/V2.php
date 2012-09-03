<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of V2
 *
 * @author martin
 */
class Ewave_Temando_Helper_V2 extends Ewave_Temando_Helper_Data {
    
    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @param Mage_Catalog_Model_Product $product 
     */
    public function getProductPackages($item, $product) {
	
	$packages = array();
	
	if ($product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
	    //try getting from selected simple product first
	    $simpleProduct = $this->getSelectedSimpleFromConfigurable($product, $item);
	    if($simpleProduct->getData('temando_packaging_mode')) {
		//packaging defined - use it
		$packages = $this->getPackages($simpleProduct);
	    }
	}
	
	if(!empty($packages)) return $packages;
	
	//all other cases - get parent/simple product packaging
	if($product->getData('temando_packaging_mode')) {
	    $packages = $this->getPackages($product);
	} 
	
	if(!empty($packages)) return $packages;
	
	//last resort - just use defaults
	return array($this->getDefaultPackage($product));
	
    }
    
    protected function getPackages($product) {
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
	    $packages[] = $this->getDefaultPackage($product);
	}
	
	return $packages;
    }
    
    protected function getDefaultPackage($product) {
	return array(
	    'description' => $product->getName(),
	    'packaging' => $this->getConfigData('defaults/packaging'),
	    'fragile' => $this->getConfigData('defaults/fragile'),
	    'weight' => $product->getWeight(),
	    'length' => $this->getConfigData('defaults/length'),
	    'width' => $this->getConfigData('defaults/width'),
	    'height' => $this->getConfigData('defaults/height')
	);
    }
    
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
    
    
    public function getDynamicOrigin($postcode, $storeId = null)
    {
	return Mage::getSingleton('temando/warehouse')->getCollection()->getOriginByPostcode($postcode, $storeId);
    }
    
}


