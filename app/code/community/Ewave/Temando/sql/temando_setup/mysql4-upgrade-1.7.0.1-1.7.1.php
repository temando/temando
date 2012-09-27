<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_warehouse')}
  ADD `loading_facilities` CHAR(1) NOT NULL DEFAULT 'N',
  ADD `dock` CHAR(1) NOT NULL DEFAULT 'N',
  ADD `forklift` CHAR(1) NOT NULL DEFAULT 'N',
  ADD `limited_access` CHAR(1) NOT NULL DEFAULT 'N',
  ADD `postal_box` CHAR(1) NOT NULL DEFAULT 'N'
;");

$installer->endSetup();