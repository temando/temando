<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_rule')}
  ADD `from_date` DATE NULL AFTER `is_active`,
  ADD `to_date` DATE NULL AFTER `from_date`
;");

$installer->endSetup();