<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_quote')}
  ADD `extras` TEXT NOT NULL
;");

$installer->endSetup();
