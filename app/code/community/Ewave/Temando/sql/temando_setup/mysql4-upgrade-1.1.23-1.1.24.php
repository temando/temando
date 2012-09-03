<?php

set_time_limit(0);

/* @var $this Mage_Eav_Model_Entity_Setup */
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

//make all attributes invisible in backend - custom tab on edit product page
$installer->run("
    UPDATE {$this->getTable('catalog_eav_attribute')} SET `is_visible` = 0
    WHERE `attribute_id` IN(SELECT `attribute_id`
    FROM {$this->getTable('eav_attribute')}
    WHERE `attribute_code` LIKE 'temando%');
");

$installer->endSetup();
?>
