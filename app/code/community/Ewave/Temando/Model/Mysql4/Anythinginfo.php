<?php

class Ewave_Temando_Model_Mysql4_Anythinginfo extends Mage_Core_Model_Mysql4_Abstract
{
    
    public function _construct()
    {
        // Note that the Id refers to the key field in your database table.
        $this->_init('temando/anythinginfo', 'id');
    }
    
    public function loadByProduct($productId)
    {
        $db = Mage::getSingleton('core/resource')->getConnection('temando_read');
        $result = $db->fetchRow("SELECT * FROM " . Mage::getSingleton('core/resource')->getTableName('temando_anythinginfo') . " WHERE product_id='" . $productId . "' LIMIT 1");
        $this->setData($result);
        return $this;
    }
    
}
