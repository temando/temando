<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_rule')}
  ADD `action_restrict_note` text NULL
;");

$installer->endSetup();

