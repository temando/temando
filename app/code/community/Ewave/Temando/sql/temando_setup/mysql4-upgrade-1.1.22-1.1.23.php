<?php

set_time_limit(0);

/* @var $this Mage_Eav_Model_Entity_Setup */
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer = $this;

$installer->startSetup();



$installer->removeAttribute('catalog_product', 'temando_packaging_mode');
for($i = 1; $i <= 5; $i++){
    $installer->removeAttribute('catalog_product', "temando_package_{$i}_type");
    $installer->removeAttribute('catalog_product', "temando_package_{$i}_description");
    $installer->removeAttribute('catalog_product', "temando_package_{$i}_fragile");
    $installer->removeAttribute('catalog_product', "temando_package_{$i}_weight");
    $installer->removeAttribute('catalog_product', "temando_package_{$i}_length");
    $installer->removeAttribute('catalog_product', "temando_package_{$i}_width");
    $installer->removeAttribute('catalog_product', "temando_package_{$i}_height");
    
}

/**
 * Add extra attributes to support multi-packaging 
 */
$installer->addAttribute('catalog_product', 'temando_packaging_mode',
        array(
            'type' => 'int',
            'label' => 'Packaging Mode',
            'group' => 'Temando',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'source' => 'eav/entity_attribute_source_boolean',
            'input' => 'select',
            'default' => false,
            'visible' => false,
            'required' => false,
            'user_defined' => false,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'unique' => false
        )
);

for($i = 1; $i <= 5; $i++) {
    $installer->addAttribute('catalog_product', "temando_package_{$i}_type",
	    array(
		'type' => 'int',
		'label' => 'Packaging Type',
		'group' => 'Temando',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'source' => 'temando/entity_attribute_source_packaging',
		'input' => 'select',
		'visible' => false,
		'required' => false,
		'user_defined' => false,
		'searchable' => false,
		'filterable' => false,
		'comparable' => false,
		'visible_on_front' => false,
		'unique' => false
	    )
    );
    
    $installer->addAttribute('catalog_product', "temando_package_{$i}_description",
	    array(
		'type' => 'varchar',
		'label' => 'Description',
		'group' => 'Temando',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible' => false,
		'required' => false,
		'user_defined' => false,
		'searchable' => false,
		'filterable' => false,
		'comparable' => false,
		'visible_on_front' => false,
		'unique' => true
	    )
    );

    $installer->addAttribute('catalog_product', "temando_package_{$i}_fragile",
	    array(
		'type' => 'int',
		'label' => 'Fragile',
		'group' => 'Temando',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'source' => 'eav/entity_attribute_source_boolean',
		'input' => 'select',
		'default' => false,
		'visible' => false,
		'required' => false,
		'user_defined' => false,
		'searchable' => false,
		'filterable' => false,
		'comparable' => false,
		'visible_on_front' => false,
		'unique' => false
	    )
    );
    
    $installer->addAttribute('catalog_product', "temando_package_{$i}_weight",
	    array(
		'type' => 'decimal',
		'label' => 'Weight',
		'group' => 'Temando',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible' => false,
		'required' => false,
		'user_defined' => false,
		'searchable' => false,
		'filterable' => false,
		'comparable' => false,
		'visible_on_front' => false,
		'unique' => false
	    )
    );

    $installer->addAttribute('catalog_product', "temando_package_{$i}_length",
	    array(
		'type' => 'decimal',
		'label' => 'Length',
		'group' => 'Temando',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible' => false,
		'required' => false,
		'user_defined' => false,
		'searchable' => false,
		'filterable' => false,
		'comparable' => false,
		'visible_on_front' => false,
		'unique' => false
	    )
    );

    $installer->addAttribute('catalog_product', "temando_package_{$i}_width",
	    array(
		'type' => 'decimal',
		'label' => 'Width',
		'group' => 'Temando',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible' => false,
		'required' => false,
		'user_defined' => false,
		'searchable' => false,
		'filterable' => false,
		'comparable' => false,
		'visible_on_front' => false,
		'unique' => false
	    )
    );

    $installer->addAttribute('catalog_product', "temando_package_{$i}_height",
	    array(
		'type' => 'decimal',
		'label' => 'Height',
		'group' => 'Temando',
		'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'visible' => false,
		'required' => false,
		'user_defined' => false,
		'searchable' => false,
		'filterable' => false,
		'comparable' => false,
		'visible_on_front' => false,
		'unique' => false
	    )
    );
}

/**
 * Hide attributes ('visible' => false does not work on some CE versions) 
 */

$installer->run("
    UPDATE {$this->getTable('catalog_eav_attribute')} SET `is_visible` = 0
    WHERE `attribute_id` IN(SELECT `attribute_id`
    FROM {$this->getTable('eav_attribute')}
    WHERE `attribute_code` LIKE 'temando_package_%');
");

$installer->endSetup();
