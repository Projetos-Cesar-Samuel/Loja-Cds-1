<?php

$installer = $this;
/** @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->addAttribute(
    'catalog_product',
    'ave_size_chart',
    array(
        'group'             => 'General',
        'label'             => 'Size Chart',
        'input'             => 'select',
        'type'              => 'int',
        'source'            => 'ave_sizechart/chart_source',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'required'          => 0,
        'unique'            => 0,
        'user_defined'      => 1,
    )
);
