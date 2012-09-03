<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER table {$this->getTable('temando_manifest')} ADD `type` varchar(255) not null default 'Awaiting Confirmation';
ALTER table {$this->getTable('temando_manifest')} CHANGE `manifest_document` `manifest_document` longtext not null;
ALTER table {$this->getTable('temando_manifest')} ADD KEY (`type`);
");

$installer->endSetup();
