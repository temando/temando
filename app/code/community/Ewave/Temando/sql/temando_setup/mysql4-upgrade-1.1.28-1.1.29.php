<?php

$installer = $this;
$installer->startSetup();


/** Add TNT Carriers **/
$installer->run("
INSERT INTO {$this->getTable('temando_carrier')} (`carrier_id`, `company_name`) VALUES
(54397, 'TNT'),
(60027, 'TNT - Auth to Leave');
");

$installer->endSetup();