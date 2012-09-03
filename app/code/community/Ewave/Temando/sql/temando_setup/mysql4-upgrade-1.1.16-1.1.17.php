<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  CHANGE `ready_date` `ready_date` DATE NULL DEFAULT NULL;
;");

$installer->endSetup();
