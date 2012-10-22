<?php


$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `order_increment_id` VARCHAR(50) NULL DEFAULT NULL,
  MODIFY `order_id` int(10) NULL DEFAULT NULL 
;");


$installer->endSetup();