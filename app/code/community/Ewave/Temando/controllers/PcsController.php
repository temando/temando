<?php

class Ewave_Temando_PcsController extends Mage_Core_Controller_Front_Action
{
    
    public function testAction()
    {
	$origin = Mage::helper('temando/v2')->getDynamicOrigin('2000');
	var_dump($origin);
    }
    
    private $_result = array (
            'query' => '',
            'suggestions' => array(0 => 'No result found'),
            'data' => array(
                0 => array (
                    0 => array(
                            'city' => '',
                            'region_id' => '',
                            'postcode' => ''
                        )
                    )
            )
        );
    
    public function construct() {
        parent:: construct();
        
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function autocomplete($field) {
        
        $query = $this->getRequest()->getParam('query');
        
        $country = $this->getRequest()->getParam('country');
        
        if (!$country || $country != 'AU') {
            return;
        }
        
        $stateId = $this->getRequest()->getParam('state_id');
        
        $this->_result['query'] = $query;
        
        $collection = Mage::getModel('temando/pcs')->getCollection();
        
        $collection->addFieldToFilter($field, array('like' => $query . '%'));

        if ($stateId) {
            $collection->addFieldToFilter('main_table.region_id', $stateId);
        }

	$i = -1;
	if (count($collection) > 0) {
		$this->_result['data'] = array();
		foreach ($collection as $item) {
		   
		    $value = $item->getData($field);
		    if (!in_array($value, $this->_result['suggestions'])) {
			$i++;
			$this->_result['suggestions'][$i] = $value; 
			$this->_result['data'][$i][] = $item->getData();
		    } else {
			$this->_result['data'][$i][] = $item->getData();
		    }
		    
		}
        }


        $core_helper = Mage::helper('core');
        if (method_exists($core_helper, "jsonEncode")) {
            return Mage::helper('core')->jsonEncode($this->_result);
        } else {
            return Zend_Json::encode($this->_result);
        }

    }
    
    public function autocompletezipAction() {
        
        echo $this->autocomplete('postcode'); die;
        
    }
        
    public function autocompletecityAction() {
        
        echo $this->autocomplete('city'); die;
        
    }

    protected function _makeAutocomplete($query)
    {
        $this->_result['query'] = $query;

        $query = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $query);
        $query = trim(preg_replace('/  */', ' ', $query));

        $words = explode(' ', $query);
        $result = Mage::helper('temando')->getSuggestionsCache($words);
        if ($result) {
            return $result;
        }

        if (count($words)) {
            $collection = Mage::getModel('temando/pcs')->getCollection();

            foreach ($words as $word) {
                $collection->addFieldToFilter('main_table.fulltext', array('like' => '% ' . $word . '%'));
            }

            $i = -1;
            if (count($collection) > 0) {
                $this->_result['data'] = array();
                foreach ($collection as $item) {
                    $value = $item->getFulltext();
                    if (!in_array($value, $this->_result['suggestions'])) {
                        $i++;
                        $this->_result['suggestions'][$i] = $value;
                        $this->_result['data'][$i][] = $item->getData();
                    } else {
                        $this->_result['data'][$i][] = $item->getData();
                    }
                }
            }
        }

        $core_helper = Mage::helper('core');
        if (method_exists($core_helper, "jsonEncode")) {
            $result = Mage::helper('core')->jsonEncode($this->_result);
        } else {
            $result = Zend_Json::encode($this->_result);
        }

        Mage::helper('temando')->setSuggestionsCache($words, $result);
        return $result;
    }
    
    public function autocompletecartAction() {
        
        $query = $this->getRequest()->getParam('query');
        echo $this->_makeAutocomplete($query);
        exit;
    }

    public function generateAction()
    {
        $array1 = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $array2 = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
                        'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
                        'u', 'v', 'w', 'x', 'y', 'z');

        foreach ($array1 as $c) {
            $this->_makeAutocomplete($c);
        }

        foreach ($array1 as $c1) {
            foreach ($array1 as $c2) {
                $this->_makeAutocomplete($c1 . $c2);
            }
        }

        foreach ($array2 as $c) {
            $this->_makeAutocomplete($c);
        }

        /*foreach ($array2 as $c1) {
            foreach ($array2 as $c2) {
                $this->_makeAutocomplete($c1 . $c2);
            }
        }*/

        echo 'done'; exit;
    }


    public function productAction()
    {
        try {
            $data = array(
                'country_id' => $this->getRequest()->getParam('country_id'),
                'region_id' => $this->getRequest()->getParam('region_id'),
                'city' => $this->getRequest()->getParam('city'),
                'postcode' => $this->getRequest()->getParam('postcode'),
                'pcs' => $this->getRequest()->getParam('pcs'),
            );
            Mage::getSingleton('customer/session')->setData('estimate_product_shipping', new Varien_Object($data));
            $product_id = $this->getRequest()->getParam('product_id');
            $product = Mage::getModel('catalog/product')->load($product_id);
            $options = array();
            foreach (explode(';', $this->getRequest()->getParam('options')) as $o) {
                if (!$o) {
                    continue;
                }

                $_t = explode(':', $o);
                if (isset($_t[1])) {
                    $options[$_t[0]] = $_t[1];
                }
            }
            $quote = Mage::getModel('sales/quote');
            $request = array('qty' => $this->getRequest()->getParam('qty'));
            if (count($options)) {
                $request['super_attribute'] = $options;
            }

            $options = array();
            foreach (explode(';', $this->getRequest()->getParam('pr_options')) as $o) {
                if (!$o) {
                    continue;
                }

                $_t = explode(':', $o);
                if (isset($_t[1])) {
                    $options[$_t[0]] = $_t[1];
                }
            }
	    
	    //get bundle products options
	    $bundle_options = array();
	    if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
	    {
		foreach (explode(';', $this->getRequest()->getParam('bl_options')) as $o) {
		    if (!$o) {
			continue;
		    }

		    $_t = explode(':', $o);
		    if (isset($_t[1])) {
			$bundle_options[$_t[0]] = $_t[1];
		    }
		}
		if(empty($bundle_options)) { //assign default values
		    $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
			$product->getTypeInstance(true)->getOptionsIds($product), $product
			);

		    foreach($selectionCollection as $option) {
			if($option->getIsDefault() == true) {
			    $bundle_options[$option->getOptionId()] = $option->getSelectionId();
			}
		    }
		}
	    }
	    
            if (count($options)) {
                $request['options'] = $options;
            } 
	    
	    if(count($bundle_options)) {
		$request['bundle_option'] = $bundle_options;
	    }

            $item = $quote->addProduct($product, new Varien_Object($request));
            if (!is_object($item)) {
                throw new Exception('Cannot calculate shipping cost for separate item');
            }
            $item->setStoreId(Mage::app()->getStore()->getId());
            /*$item->setProduct($product);
            $item->setQuote(new Varien_Object);
    		$item->setOptions(array(''));*/
            $item->setQty($this->getRequest()->getParam('qty'));
            $item->setPrice($product->getFinalPrice());
//            $quote->collectTotals();
            $request = Mage::getModel('shipping/rate_request');
            foreach ($quote->getAllItems() as $i) {
                if (!$i->getPrice()) {
                    $i->setPrice($product->getFinalPrice());
                }
            }

            $request->setAllItems($quote->getAllItems());
            $request->setDestCountryId($this->getRequest()->getParam('country_id'));
            $request->setDestRegionId($this->getRequest()->getParam('region_id'));
            $request->setDestRegionCode('');
            $request->setDestStreet('');
            $request->setDestCity($this->getRequest()->getParam('city'));
            $request->setDestPostcode($this->getRequest()->getParam('postcode'));
            $request->setPackageValue($item->getTotal());
            $request->setPackageValueWithDiscount($item->getTotal());
            $request->setPackageWeight($this->getRequest()->getParam('qty')*$product->getWeight());
            $request->setPackageQty($this->getRequest()->getParam('qty'));

            /**
             * Need for shipping methods that use insurance based on price of physical products
             */
            $request->setPackagePhysicalValue($item->getTotal());

            $request->setFreeMethodWeight($item);
            $request->setStoreId(Mage::app()->getStore()->getId());
            $request->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            $request->setFreeShipping(null);
            /**
             * Currencies need to convert in free shipping
             */
            $request->setBaseCurrency(Mage::app()->getStore()->getBaseCurrency());
            $request->setPackageCurrency(Mage::app()->getStore()->getCurrentCurrency());
            $request->setLimitCarrier(null);
            $request->setOrig(false);
            $result = Mage::getModel('shipping/shipping')->collectCarrierRates('temando', $request)->getResult();
//            echo "<pre>"; print_r($result); exit;
            Mage::register('product_rates', array('temando' => $result));
            $this->loadLayout();
            $this->renderLayout();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    
}
