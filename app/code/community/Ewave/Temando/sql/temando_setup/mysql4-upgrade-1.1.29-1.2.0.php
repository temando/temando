<?php

$installer = $this;
$installer->startSetup();


//Add location/user view permissions support
$installer->run("
ALTER TABLE {$this->getTable('temando_warehouse')}
  ADD `whs_users` text NULL
;");

$installer->endSetup();