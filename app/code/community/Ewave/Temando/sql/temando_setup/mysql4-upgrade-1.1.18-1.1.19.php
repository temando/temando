<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER table {$this->getTable('temando_manifest')} CHANGE `location_id` `location_id` varchar(255) not null default '';
;");

$installer->endSetup();
