<?php

class Ewave_Temando_Model_Api_Request_Anything extends Mage_Core_Model_Abstract
{
    
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;
    
    /**
     * @var Mage_Sales_Model_Order_Item
     */
    protected $_item = null;
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temando/api_request_anything');
    }
    
    public function setItem($item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Item || $item instanceof Mage_Sales_Model_Order_Item || $item instanceof Mage_Sales_Model_Quote_Address_Item || $item instanceof Ewave_Temando_Model_Box) {
            $this->_item = $item;
            if ($item instanceof Mage_Sales_Model_Quote_Item || $item instanceof Mage_Sales_Model_Quote_Address_Item || $item instanceof Mage_Sales_Model_Order_Item) {
                $this->_product = Mage::getModel('catalog/product')->load($item->getProductId());
            }
        }
        return $this;
    }
    
    /**
     * Gets the order item for this Anything object.
     *
     * @return Mage_Sales_Model_Order_Item
     */
    public function getItem()
    {
        if ($this->_item) {
            return $this->_item;
        }
        return false;
    }
    
    /**
     * Gets the catalog product for this Anything object.
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->_product) {
            return $this->_product;
        }
        return false;
    }
    
    public function toRequestArray()
    {
        if (!$this->validate()) {
            return false;
        }
        
        if (false && (Mage::helper('temando')->getConfigData('defaults/always_use'))) {
            $this->_loadDefaults();
        }
        
        if ($this->_item instanceof Ewave_Temando_Model_Box) {
            $anything = array(
                'class'         => 'General Goods',
                'subclass'      => 'Household Goods',
                'packaging'     => Mage::getModel('temando/system_config_source_shipment_packaging')
                                       ->getOptionLabel($this->_item->getPackaging()),
                'quantity'      => (int)($this->_item->getQty()),
                'distanceMeasurementType'
                                => $this->_item->getMeasureUnit(),
                'weightMeasurementType'
                                => $this->_item->getWeightUnit(),
                'weight'        => $this->_item->getWeight(),
                'length'        => $this->_item->getLength(),
                'width'         => $this->_item->getWidth(),
                'height'  	    => $this->_item->getHeight(),
                'qualifierFreightGeneralFragile'
                                => $this->_item->getFragile() == '1' ? 'Y' : 'N',
                'description'   => $this->_item->getComment()
            );

            if ($this->_item->getWeight() < 1) {
                $anything['weight'] = Mage::helper('temando')
                                    ->getGramsWeight($anything);
                $anything['weightMeasurementType'] = Ewave_Temando_Model_System_Config_Source_Unit_Weight::GRAMS;
            }
        } else {
            Mage::helper('temando')->applyTemandoParamsToProductByItem($this->_item, $this->_product);

            $anything = array(
                'class'         => 'General Goods',
                'subclass'      => 'Household Goods',
                'packaging'     => Mage::getModel('temando/system_config_source_shipment_packaging')
                                       ->getOptionLabel($this->_product->getTemandoPackaging()),
                'quantity'      => (int)($this->_item->getQty() ? $this->_item->getQty() : $this->_item->getQtyOrdered()),
                'distanceMeasurementType'
                                => Mage::helper('temando')
                                    ->getConfigData('units/measure'),
                'weightMeasurementType'
                                => Mage::helper('temando')
                                    ->getConfigData('units/weight'),
                'weight'        => $this->_product->getWeight(),
                'length'        => $this->_product->getTemandoLength(),
                'width'         => $this->_product->getTemandoWidth(),
                'height'  	    => $this->_product->getTemandoHeight(),
                'qualifierFreightGeneralFragile'
                                => $this->_product->getTemandoFragile() == '1' ? 'Y' : 'N',
                'description'   => $this->_product->getName()
            );

            if ($this->_product->getWeight() < 1) {
                $anything['weight'] = Mage::helper('temando')
                                    ->getGramsWeight($anything);
                $anything['weightMeasurementType'] = Ewave_Temando_Model_System_Config_Source_Unit_Weight::GRAMS;
            }

            if ($this->_product->getTemandoPackaging() == 'Pallet') {
                $anything['palletType'] = 'Plain';
                $anything['palletNature'] = 'Not Required';
            }
        }

        // return only after checking empty data of product attributes
        return $anything;
    }    
    public function validate()
    {
        return $this->_item instanceof Mage_Sales_Model_Quote_Item ||
            $this->_item instanceof Mage_Sales_Model_Order_Item ||
	    $this->_item instanceof Mage_Sales_Model_Quote_Address_Item ||
            $this->_item instanceof Ewave_Temando_Model_Box;
        // TODO: improve validation
        // - product dimensions
        // - product "fragile" flag
        // - product packaging type
    }

    /**
     * @deprecated
     * @return void
     */
    protected function _loadDefaults()
    {
        if ($this->_item instanceof Ewave_Temando_Model_Box) {
            /*$this->_item
                ->setLength(Mage::helper('temando')->getConfigData('defaults/length'))
                ->setWidth(Mage::helper('temando')->getConfigData('defaults/width'))
                ->setHeight(Mage::helper('temando')->getConfigData('defaults/height'))
                ->setFragile(Mage::helper('temando')->getConfigData('defaults/fragile'))
                ->setPackaging(Mage::helper('temando')->getConfigData('defaults/packaging'));*/
        } else {
            $this->_product
                ->setTemandoLength(Mage::helper('temando')->getConfigData('defaults/length'))
                ->setTemandoWidth(Mage::helper('temando')->getConfigData('defaults/width'))
                ->setTemandoHeight(Mage::helper('temando')->getConfigData('defaults/height'))
                ->setTemandoFragile(Mage::helper('temando')->getConfigData('defaults/fragile'))
                ->setTemandoPackaging(Mage::helper('temando')->getConfigData('defaults/packaging'));
        }
    }

}
