<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_quote')}
  DROP FOREIGN KEY `fk_magento_quote_id`
;");

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  DROP FOREIGN KEY `fk_order_id`,
  DROP FOREIGN KEY `fk_customer_selected_quote_id`,
  DROP FOREIGN KEY `fk_admin_selected_quote_id`
;");

$installer->endSetup();
