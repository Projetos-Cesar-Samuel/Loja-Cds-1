<?php
$installer = $this;
$installer->startSetup();
$installer->run(
    "CREATE TABLE IF NOT EXISTS {$this->getTable('mobikul_bannerimage')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `filename` varchar(255) NOT NULL default '',
        `status` smallint(6) NOT NULL default '0',
        `type` varchar(255) NOT NULL default '',
        `pro_cat_id` int(11) NOT NULL default '0',
        `store_id` varchar(255) NOT NULL default '',
        `sort_order` int(11) NOT NULL default '0',
        `created_time` DATETIME NULL,
        `update_time` DATETIME NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobikul_notification')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL DEFAULT '',
        `content` text NOT NULL,
        `type` varchar(255) NOT NULL DEFAULT '',
        `filename` varchar(255) NOT NULL DEFAULT '',
        `collection_type` varchar(255) NOT NULL,
        `filter_data` text NOT NULL,
        `pro_cat_id` varchar(11) DEFAULT ' ',
        `store_id` varchar(255) NOT NULL DEFAULT '',
        `status` smallint(6) NOT NULL DEFAULT '0',
        `created_time` datetime DEFAULT NULL,
        `update_time` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobikul_featuredcategories')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `filename` varchar(255) NOT NULL default '',
        `category_id` int(11) NOT NULL default '0',
        `store_id` varchar(255) NOT NULL default '',
        `sort_order` int(11) NOT NULL default '0',
        `status` int(11) NOT NULL default '1',
        `created_time` DATETIME NULL,
        `update_time` DATETIME NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobikul_userimage')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `profile` varchar(255) NOT NULL DEFAULT '',
        `banner` varchar(255) NOT NULL DEFAULT '',
        `customer_id` int(11) NOT NULL DEFAULT '0',
        `is_social` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobikul_categoryimages')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `icon` varchar(255) NOT NULL DEFAULT '',
        `banner` varchar(255) NOT NULL DEFAULT '',
        `category_id` int(11) NOT NULL DEFAULT '0',
        `category_name` varchar(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobikul_devicetoken')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) NOT NULL DEFAULT '0',
        `token` text NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobikul_customer_mobile')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) NOT NULL DEFAULT '0',
        `mobile` varchar(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);
$installer->endSetup();
$installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup("core_setup");
$installer->removeAttribute("catalog_product", "as_featured");
$installer->addAttribute("catalog_product", "as_featured", array(
    "input"                      => "boolean",
    "label"                      => "Is featured for Mobikul",
    "global"                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "comparable"                 => 0,
    "searchable"                 => 0,
    "user_defined"               => 1,
    "visible_on_front"           => 0,
    "visible_in_advanced_search" => 0,
    "is_html_allowed_on_front"   => 0,
    "required"                   => 0,
    "unique"                     => false,
    "is_configurable"            => false
));
$entityTypeId = Mage::getResourceModel("catalog/product")->getTypeId();
$attributeSetCollection = Mage::getResourceModel("eav/entity_attribute_set_collection")->setEntityTypeFilter($entityTypeId);
foreach ($attributeSetCollection as $attributeSet)
    $installer->addAttributeToSet("catalog_product", $attributeSet->getAttributeSetName(), "General", "as_featured");