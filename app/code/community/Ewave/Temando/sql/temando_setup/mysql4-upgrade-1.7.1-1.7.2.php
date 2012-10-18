<?php


$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_quote')}
  ADD `packaging` text NULL
;");


$installer->endSetup();