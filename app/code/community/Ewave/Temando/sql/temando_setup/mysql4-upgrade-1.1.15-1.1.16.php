<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `label_document` TEXT NULL DEFAULT NULL,
  ADD `label_document_type` VARCHAR(32) NOT NULL DEFAULT '';
;");

$installer->endSetup();
