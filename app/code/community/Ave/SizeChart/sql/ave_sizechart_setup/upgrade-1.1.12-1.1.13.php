<?php

$installer = $this;
/** @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->addAttribute(
    'ave_sizechart_dimension',
    'priority',
    array(
        'group'          => 'General',
        'type'           => 'int',
        'backend'        => '',
        'frontend'       => '',
        'label'          => 'Priority',
        'global'         => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'required'       => '0',
        'user_defined'   => false,
        'default'        => '0',
        'unique'         => false,
        'position'       => '43',
        'note'           => '',
        'visible'        => '1',
        'wysiwyg_enabled'=> '0',
    )
);
