<?php

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('temando_manifest')};
CREATE TABLE {$this->getTable('temando_manifest')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(10) NOT NULL default 0,
  `carrier_id` int(10) NOT NULL default 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `manifest_document_type` varchar(250) NOT NULL default '',
  `manifest_document` TEXT NOT NULL,
  `label_document_type` varchar(250) NOT NULL default '',
  `label_document` TEXT NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
;");

$installer->endSetup();
