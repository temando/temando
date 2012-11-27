<?php

/**
 * MULTI ORIGIN SUPPORT 
 * temando_warehouse
 * temando_zone
 * temando_inventory
 */

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("
   DROP TABLE IF EXISTS {$this->getTable('temando_warehouse')};
   CREATE TABLE {$this->getTable('temando_warehouse')} (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `company_name` varchar(255) NOT NULL,
    `street` varchar(255) NOT NULL,
    `city` varchar(255) NOT NULL,
    `region` varchar(255) NOT NULL,
    `postcode` varchar(50) DEFAULT NULL,
    `country` varchar(50) NOT NULL,
    `contact_name` varchar(255) NOT NULL,
    `contact_email` varchar(255) NOT NULL,
    `contact_phone_1` varchar(255) NOT NULL,
    `contact_phone_2` varchar(255) DEFAULT NULL,
    `contact_fax` varchar(255) DEFAULT NULL,
    `priority` int(11) DEFAULT NULL,
    `location_type` varchar(255) NOT NULL,
    `store_ids` varchar(255) NOT NULL,
    `zone_ids` varchar(255) DEFAULT NULL,
    `whs_products` text,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8; 
");
   
$installer->run("
   DROP TABLE IF EXISTS {$this->getTable('temando_zone')};
   CREATE TABLE {$this->getTable('temando_zone')} (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `country` varchar(2) NOT NULL,
    `ranges` text NOT NULL,
    PRIMARY KEY (`id`),
    KEY `name` (`name`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8; 
");
   
$installer->run("
   DROP TABLE IF EXISTS {$this->getTable('temando_inventory')};
   CREATE TABLE {$this->getTable('temando_inventory')} (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `warehouse_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `warehouse_id` (`warehouse_id`,`product_id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8; 
");

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `warehouse_id` int(11) NOT NULL DEFAULT 1 AFTER `anticipated_cost`
;");

$installer->endSetup();