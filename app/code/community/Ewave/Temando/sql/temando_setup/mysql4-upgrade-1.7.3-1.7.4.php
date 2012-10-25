<?php


$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `service_type` int(10) NOT NULL DEFAULT 3
;");


$installer->endSetup();