<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->updateAttribute('catalog_product', 'temando_packaging', 'is_required', 0);
$installer->updateAttribute('catalog_product', 'temando_fragile', 'is_required', 0);
$installer->updateAttribute('catalog_product', 'temando_length', 'is_required', 0);
$installer->updateAttribute('catalog_product', 'temando_width', 'is_required', 0);
$installer->updateAttribute('catalog_product', 'temando_height', 'is_required', 0);

$installer->endSetup();
