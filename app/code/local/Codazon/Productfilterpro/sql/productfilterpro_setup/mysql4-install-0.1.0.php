<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$installer = $this;
$installer->startSetup();
$installer->addAttribute("catalog_product", "codazon_featured",  array(
    "type"          => "int",
    "backend"       => "",
    "frontend"      => "",
    "label"         => "Featured",
    "input"         => "boolean",
    "class"         => "",
    "global"        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"       => true,
    "required"      => false,
    "user_defined"  => false,
    "default"       => "",
    "searchable"    => false,
    "filterable"    => false,
    "comparable"    => false,
	'user_defined'  => true,
    "visible_on_front"  => false,
    "unique"        => false,
    "note"          => ""
));

$installer->addAttribute("catalog_product", "codazon_hot",  array(
    "type"     		=> "int",
    "backend"  		=> "",
    "frontend" 		=> "",
    "label"			=> "Hot",
    "input"			=> "boolean",
    "class"    		=> "",
    "global"   		=> Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    "visible"  		=> true,
    "required" 		=> false,
    "user_defined"  => false,
    "default" 		=> "",
    "searchable" 	=> false,
    "filterable" 	=> false,
    "comparable" 	=> false,
	'user_defined'  => true,
    "visible_on_front"  => false,
    "unique"        => false,
    "note"          => ""
));

$entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();

$attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')->load();
foreach ($attributeSetCollection as $attributeSetId => $attributeSet) {
	$installer->addAttributeToSet($entityTypeId, $attributeSetId, 'General', 'codazon_featured', 10);
	$installer->addAttributeToSet($entityTypeId, $attributeSetId, 'General', 'codazon_hot', 10);
}