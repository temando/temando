<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_box')}
  ADD `value` decimal(12, 4) NOT NULL AFTER `qty`
;");

$installer->endSetup();
