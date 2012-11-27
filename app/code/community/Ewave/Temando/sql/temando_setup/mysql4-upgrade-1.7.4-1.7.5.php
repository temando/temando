<?php


$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_rule')}
  ADD `action_dynamic_show_carrier_name` TINYINT NOT NULL DEFAULT 1 AFTER `action_dynamic_adjustment_value`,
  ADD `action_dynamic_show_carrier_time` TINYINT NOT NULL DEFAULT 1 AFTER `action_dynamic_show_carrier_name`,
  ADD `action_dynamic_label` VARCHAR(500) NULL DEFAULT NULL AFTER `action_dynamic_show_carrier_time`
;");