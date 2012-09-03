<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->removeAttribute('catalog_product', 'temando_warehouse');

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('temando_carrierinfo')};
CREATE TABLE {$this->getTable('temando_carrier')} (
  `id` int(13) UNSIGNED NOT NULL AUTO_INCREMENT,
  `carrier_id` bigint(20) NOT NULL,
  `company_name` varchar(250) NOT NULL,
  `company_contact` text NOT NULL,
  `street_address` text NOT NULL,
  `street_suburb` varchar(255) NOT NULL,
  `street_city` varchar(255) NOT NULL,
  `street_state` varchar(255) NOT NULL,
  `street_postcode` varchar(255) NOT NULL,
  `street_country` varchar(255) NOT NULL,
  `postal_address` text NOT NULL,
  `postal_suburb` varchar(255) NOT NULL,
  `postal_city` varchar(255) NOT NULL,
  `postal_state` varchar(255) NOT NULL,
  `postal_postcode` varchar(255) NOT NULL,
  `postal_country` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('temando_carrier')} (`id`, `carrier_id`, `company_name`) VALUES
(1, 54381, 'Allied Express'),
(2, 54426, 'Allied Express (Bulk)'),
(3, 54359, 'Australian Air Express'),
(4, 54396, 'Australian Air Express-residential'),
(5, 54360, 'Bluestar Logistics'),
(6, 54429, 'Bluestar Logistics Bulk'),
(7, 54433, 'Capital Transport Courier'),
(8, 54432, 'Capital Transport HDS'),
(9, 54425, 'Couriers Please'),
(10, 54343, 'DHL'),
(11, 54430, 'DHL MultiZone'),
(12, 54431, 'DHL SingleZone'),
(13, 54427, 'Fastway Couriers Adhoc'),
(14, 54428, 'Fastway Couriers Bulk'),
(15, 54344, 'Hunter Express'),
(16, 54398, 'Hunter Express (bulk)'),
(17, 54358, 'Mainfreight'),
(18, 54410, 'Northline'),
(19, 10000, 'Flat Rate'),
(20, 10001, 'FREE Shipping');
");

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('temando_booking')};
CREATE TABLE {$this->getTable('temando_quote')} (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `magento_quote_id` int(10) UNSIGNED NOT NULL,
  `carrier_id` int(13) UNSIGNED NOT NULL,
  `accepted` boolean NOT NULL DEFAULT '0',
  `total_price` decimal(12, 4) NOT NULL,
  `base_price` decimal(12, 4) NOT NULL,
  `tax` decimal(12, 4) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `delivery_method` text NOT NULL,
  `eta_from` int UNSIGNED NOT NULL DEFAULT '0',
  `eta_to` int UNSIGNED NOT NULL DEFAULT '0',
  `guaranteed_eta` boolean NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    INDEX (`carrier_id`),
    INDEX (`magento_quote_id`),
    CONSTRAINT `fk_magento_quote_id`
      FOREIGN KEY (`magento_quote_id`)
      REFERENCES {$this->getTable('sales_flat_quote')} (`entity_id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
    CONSTRAINT `fk_carrier_id`
      FOREIGN KEY (`carrier_id`)
      REFERENCES {$this->getTable('temando_carrier')} (`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('temando_orders')};
CREATE TABLE {$this->getTable('temando_shipment')} (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(10) UNSIGNED NOT NULL,
  `customer_selected_quote_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `admin_selected_quote_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `anticipated_cost` decimal(12,4) UNSIGNED NOT NULL,
  `status` int(10) NOT NULL DEFAULT '0',
  `booking_request_id` int(13) UNSIGNED NOT NULL,
  `booking_number` varchar(255) NOT NULL,
  `consignment_number` varchar(255) NOT NULL,
  `consignment_document` mediumtext NOT NULL,
  `consignment_document_type` varchar(255) NOT NULL,
  `insurance` boolean NOT NULL DEFAULT '0',
  `destination_contact_name` varchar(255) NOT NULL,
  `destination_company_name` varchar(255) NOT NULL,
  `destination_street` varchar(255) NOT NULL,
  `destination_city` varchar(255) NOT NULL,
  `destination_postcode` varchar(255) NOT NULL,
  `destination_region` varchar(255) NOT NULL,
  `destination_country` varchar(255) NOT NULL,
  `destination_phone` varchar(255) NOT NULL,
  `destination_email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`order_id`),
  INDEX (`customer_selected_quote_id`),
  INDEX (`admin_selected_quote_id`),
  CONSTRAINT `fk_order_id`
  	FOREIGN KEY (`order_id`)
  	REFERENCES {$installer->getTable('sales_flat_order')} (`entity_id`)
  	ON DELETE CASCADE
  	ON UPDATE CASCADE,
  CONSTRAINT `fk_customer_selected_quote_id`
  	FOREIGN KEY (`customer_selected_quote_id`)
  	REFERENCES {$installer->getTable('temando_quote')} (`id`)
  	ON DELETE SET NULL
  	ON UPDATE SET NULL,
  CONSTRAINT `fk_admin_selected_quote_id`
  	FOREIGN KEY (`admin_selected_quote_id`)
  	REFERENCES {$installer->getTable('temando_quote')} (`id`)
  	ON DELETE SET NULL
  	ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('temando_warehouse')};
");


$installer->run("
CREATE TABLE {$this->getTable('temando_box')} (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `shipment_id` int(13) NOT NULL,
  `comment` text NOT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `length` decimal(12, 4) NOT NULL,
  `width` decimal(12, 4) NOT NULL,
  `height` decimal(12, 4) NOT NULL,
  `measure_unit` varchar(255) NOT NULL,
  `weight` decimal(12, 4) NOT NULL,
  `weight_unit` varchar(255) NOT NULL,
  `fragile` boolean NOT NULL DEFAULT '0',
  `destination_country` varchar(255) NOT NULL,
  `destination_postcode` varchar(255) NOT NULL,
  `destination_city` varchar(255) NOT NULL,
  `ready_date` date NULL DEFAULT NULL,
  `ready_time` varchar(10) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();