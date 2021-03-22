<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_product', 'installment_quantity', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'Installment quantity',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'searchable' => 1,
    'filterable' => 0,
    'comparable'    => 1,
    'visible_on_front' => 1,
    'source' => 'eav/entity_attribute_source_table',
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'configurable' => 1,
));

$setup->addAttribute('catalog_product', 'attr_payment', array(
    'group'         => 'General',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => '% payment',
    'backend'       => '',
    'visible'       => 1,
    'required'      => 0,
    'user_defined' => 1,
    'searchable' => 1,
    'filterable' => 0,
    'comparable'    => 1,
    'visible_on_front' => 1,
    'source' => 'eav/entity_attribute_source_table',
    'visible_in_advanced_search'  => 0,
    'is_html_allowed_on_front' => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'configurable' => 1,
));

$setup->run("
    DROP TABLE IF EXISTS {$this->getTable('tm_parcelamento')};
        CREATE TABLE {$this->getTable('tm_parcelamento')} (
          `id` int(11) unsigned NOT NULL auto_increment,
          `public_key` int(11) unsigned NOT NULL,
          `mostrar_juros` tinyint(1) NULL,
          `quantidade_parcelas` int(11) NOT NULL,
          `texto` varchar(255) NOT NULL default '',
          `mostrar_outras_paginas` tinyint(1) NOT NULL,
          `created_at` datetime NULL,
          `update_at` datetime NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();