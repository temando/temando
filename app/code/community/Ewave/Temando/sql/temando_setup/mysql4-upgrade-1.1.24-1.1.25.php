<?php

/**
 * RULE ENGINE - TABLE RULE 
 */

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();

$installer->run("
   DROP TABLE IF EXISTS {$this->getTable('temando_rule')};
   CREATE TABLE {$this->getTable('temando_rule')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `priority` int(11) DEFAULT NULL,
  `stop_other` tinyint(1) NOT NULL DEFAULT '0',
  `store_ids` text NOT NULL,
  `condition_weight` text,
  `condition_subtotal` text,
  `condition_items` text,
  `condition_zone` mediumtext,
  `condition_time_type` tinyint(4) DEFAULT NULL,
  `condition_time_value` varchar(8) DEFAULT NULL,
  `action_rate_type` tinyint(4) NOT NULL,
  `action_static_value` float DEFAULT NULL,
  `action_static_label` varchar(500) DEFAULT NULL,
  `action_dynamic_carriers` text,
  `action_dynamic_filter` tinyint(4) DEFAULT NULL,
  `action_dynamic_adjustment_type` tinyint(4) DEFAULT NULL,
  `action_dynamic_adjustment_value` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8; 
");

$installer->endSetup();