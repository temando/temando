<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('temando_shipment')} ADD `ready_date` DATETIME NOT NULL ,
ADD `ready_time` VARCHAR( 2 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");

$installer->endSetup();