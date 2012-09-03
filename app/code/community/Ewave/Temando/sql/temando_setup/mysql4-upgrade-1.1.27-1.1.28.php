<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `grid_display` boolean NOT NULL DEFAULT '1'
;");

$installer->endSetup();
