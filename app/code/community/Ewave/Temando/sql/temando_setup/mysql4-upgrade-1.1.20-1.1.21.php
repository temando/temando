<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER table {$this->getTable('temando_manifest')} CHANGE `label_document` `label_document` longtext not null;
");

$installer->endSetup();
