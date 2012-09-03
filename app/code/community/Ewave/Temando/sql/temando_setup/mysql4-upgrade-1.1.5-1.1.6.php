<?php

/* @var $this Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('temando_box')}
  DROP `destination_country`,
  DROP `destination_postcode`,
  DROP `destination_city`,
  DROP `ready_date`,
  DROP `ready_time`
;
");

$installer->run("
ALTER TABLE {$this->getTable('temando_box')}
  ADD `packaging` int(255) NOT NULL
;
");

$installer->endSetup();