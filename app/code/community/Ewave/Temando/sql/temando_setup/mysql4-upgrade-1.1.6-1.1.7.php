<?php

/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_shipment')}
  ADD `customer_selected_quote_description` TEXT NOT NULL AFTER `customer_selected_quote_id`,
  MODIFY `ready_date` DATE NOT NULL;
");

$installer->endSetup();