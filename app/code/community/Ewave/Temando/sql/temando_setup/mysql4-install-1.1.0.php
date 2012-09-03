<?php

set_time_limit(0);

/* @var $this Mage_Eav_Model_Entity_Setup */
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

// Add custom Temando attributes
$installer->addAttributeGroup('catalog_product', 'Default', 'Temando', 90);

$installer->addAttribute('catalog_product', 'temando_warehouse',
        array(
            'type' => 'int',
            'label' => 'Warehouse',
            'group' => 'Temando',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'source' => 'temando/entity_attribute_source_warehouse',
            'input' => 'select',
            'visible' => true,
            'required' => true,
            'user_defined' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'unique' => false
        )
);

$installer->addAttribute('catalog_product', 'temando_packaging',
        array(
            'type' => 'varchar',
            'label' => 'Packaging',
            'group' => 'Temando',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'source' => 'temando/entity_attribute_source_packaging',
            'input' => 'select',
            'visible' => true,
            'required' => true,
            'user_defined' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'unique' => false
        )
);

$installer->addAttribute('catalog_product', 'temando_fragile',
        array(
            'type' => 'int',
            'label' => 'Fragile',
            'group' => 'Temando',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'source' => 'eav/entity_attribute_source_boolean',
            'input' => 'select',
            'default' => false,
            'visible' => true,
            'required' => true,
            'user_defined' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'unique' => false
        )
);

$installer->addAttribute('catalog_product', 'temando_length',
        array(
            'type' => 'decimal',
            'label' => 'Length',
            'group' => 'Temando',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => true,
            'user_defined' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'unique' => false
        )
);

$installer->addAttribute('catalog_product', 'temando_width',
        array(
            'type' => 'decimal',
            'label' => 'Width',
            'group' => 'Temando',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => true,
            'user_defined' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'unique' => false
        )
);

$installer->addAttribute('catalog_product', 'temando_height',
        array(
            'type' => 'decimal',
            'label' => 'Height',
            'group' => 'Temando',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => true,
            'user_defined' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'unique' => false
        )
);

// Create custom tables
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('temando_booking')};
CREATE TABLE {$this->getTable('temando_booking')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) NOT NULL,
  `booking_number` varchar(250) NOT NULL,
  `consignment_number` varchar(250) NOT NULL,
  `consignment_document` longblob NOT NULL,
  `consignment_document_type` varchar(250) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `shipping_cost` int(11) NOT NULL,
  `carrier_id` int(11) NOT NULL,
  `active` varchar(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('temando_carrierinfo')};
CREATE TABLE {$this->getTable('temando_carrierinfo')} (
  `id` int(11) NOT NULL,
  `carrier_id` bigint(20) NOT NULL,
  `company_name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO {$this->getTable('temando_carrierinfo')} (`id`, `carrier_id`, `company_name`) VALUES
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


DROP TABLE IF EXISTS {$this->getTable('temando_orders')};
CREATE TABLE {$this->getTable('temando_orders')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` int(11) NOT NULL,
  `real_order_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `order_cost` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS {$this->getTable('temando_warehouse')};
CREATE TABLE {$this->getTable('temando_warehouse')} (
  `warehouse_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `country` varchar(3) NOT NULL,
  `state` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `postcode` varchar(12) NOT NULL,
  `address` varchar(255) NOT NULL,
  `cperson` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(64) NOT NULL,
  `phone2` varchar(255) NOT NULL,
  `fax` varchar(255) NOT NULL,
  `location_type` varchar(256) NOT NULL,
  PRIMARY KEY (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

// Insert a list of states into the regions database. Magento will then pick
// these up when displaying addresses and allow the user to select from a drop-down
// list, rather than having to type them in manually.
$regions = array(
    array('code' => 'ACT', 'name' => 'Australia Capital Territory'),
    array('code' => 'NSW', 'name' => 'New South Wales'),
    array('code' => 'NT', 'name' => 'Northern Territory'),
    array('code' => 'QLD', 'name' => 'Queensland'),
    array('code' => 'SA', 'name' => 'South Australia'),
    array('code' => 'TAS', 'name' => 'Tasmania'),
    array('code' => 'VIC', 'name' => 'Victoria'),
    array('code' => 'WA', 'name' => 'Western Australia')
);

$db = Mage::getSingleton('core/resource')->getConnection('core_read');

foreach ($regions as $region) {
    // Check if this region has already been added
    $result = $db->fetchOne("SELECT code FROM " . $this->getTable('directory_country_region') . " WHERE `country_id` = 'AU' AND `code` = '" . $region['code'] . "'");
    if ($result != $region['code']) {
        $installer->run(
                "INSERT INTO `{$this->getTable('directory_country_region')}` (`country_id`, `code`, `default_name`) VALUES
            ('AU', '" . $region['code'] . "', '" . $region['name'] . "');
            INSERT INTO `{$this->getTable('directory_country_region_name')}` (`locale`, `region_id`, `name`) VALUES
            ('en_US', LAST_INSERT_ID(), '" . $region['name'] . "'), ('en_AU', LAST_INSERT_ID(), '" . $region['name'] . "');"
        );
    }
}

$installer->endSetup();
