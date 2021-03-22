<?php

$installer = $this;
/** @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->addAttribute(
    'ave_sizechart_dimension',
    'main',
    array(
        'group'          => 'General',
        'type'           => 'int',
        'backend'        => '',
        'frontend'       => '',
        'label'          => 'Main dimension',
        'input'          => 'select',
        'source'         => 'eav/entity_attribute_source_boolean',
        'global'         => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'required'       => '1',
        'user_defined'   => false,
        'default'        => '0',
        'unique'         => false,
        'position'       => '45',
        'note'           => '',
        'visible'        => '1',
        'wysiwyg_enabled'=> '0',
    )
);
