<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `store_id` int(11) NOT NULL AFTER `order_id`
;");

$installer->endSetup();