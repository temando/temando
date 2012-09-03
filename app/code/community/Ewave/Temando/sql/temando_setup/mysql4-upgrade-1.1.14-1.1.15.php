<?php

$installer = $this;
$installer->startSetup();

$countryId = 'AU';
$states = array();
$csv_filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pcs.csv';
if (!$installer->tableExists($this->getTable('temando_pcs'))) {
    $installer->run($sql = "
            DROP TABLE IF EXISTS {$this->getTable('temando_pcs')};
            CREATE TABLE
            {$this->getTable('temando_pcs')} (
              `entity_id` int(10) unsigned NOT NULL auto_increment,
              `postcode` varchar(32) NOT NULL DEFAULT '',
              `city` varchar(64) NOT NULL DEFAULT '',
              `state` varchar(8) NOT NULL DEFAULT '',
              `country_id` varchar(2) NOT NULL DEFAULT '{$countryId}',
              `region_id` mediumint(8) NOT NULL DEFAULT 0,
              `fulltext` text NOT NULL DEFAULT '',
              PRIMARY KEY USING BTREE (`entity_id`),
              Index pcs (`postcode`(6), `city`(6), `state`(6))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
    $handle = null;
    try {
        if (($handle = fopen($csv_filename, "r")) === false) {
            throw new Exception('Cannot open CSV file');
        }

        $columns = array_map('strtolower', fgetcsv($handle, 100, ";"));
        foreach ($columns as $k => $column) {
            if ($column == 'state') {
                $statesPosition = $k;
            }
        }

        $inserts = array();
        while ($data = fgetcsv($handle, 100, ";")) {
            if (!isset($states[$data[$statesPosition]])) {
                $sql = $this->getConnection()->select()->from($this->getTable('directory_country_region'), array('default_name', 'region_id'))->where('country_id = "AU" AND code = "' . $data[$statesPosition] . '"');
                $tmp = $this->getConnection()->fetchAll($sql);
                $states[$data[$statesPosition]] = $tmp[0];
            }

            $state = $states[$data[$statesPosition]]['default_name'];
            $region_id = $states[$data[$statesPosition]]['region_id'];
            $fulltext = '';
            $insert = array();
            foreach ($data as $k => $value) {
                $insert[$columns[$k]] = $value;
                if ($k == $statesPosition) continue;
                $fulltext .= ' ' . $data[$k];
            }

            $insert['fulltext'] = $fulltext . ' ' . $state;
            $insert['region_id'] = $region_id;
            $inserts[] = $insert;
            if (count($inserts) > 1000) {
                if (method_exists($this->getConnection(), "insertMultiple")) {
                    $this->getConnection()->insertMultiple($this->getTable('temando_pcs'), $inserts);
                } else {
                    foreach ($inserts as $i) {
                        $this->getConnection()->insert($this->getTable('temando_pcs'), $i);
                    }
                }

                $inserts = array();
            }
        }

        if (count($inserts)) {
            if (method_exists($this->getConnection(), "insertMultiple")) {
                $this->getConnection()->insertMultiple($this->getTable('temando_pcs'), $inserts);
            } else {
                foreach ($inserts as $i) {
                    $this->getConnection()->insert($this->getTable('temando_pcs'), $i);
                }
            }

            $inserts = array();
        }
    } catch (Exception $e) {
        if ($handle) {
            fclose($handle);
        }
    }
}

$installer->endSetup();
