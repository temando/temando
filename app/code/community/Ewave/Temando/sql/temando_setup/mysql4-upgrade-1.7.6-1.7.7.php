<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_rule')}
  ADD `condition_day` TEXT NULL AFTER `condition_time_value`
;");

$installer->endSetup();