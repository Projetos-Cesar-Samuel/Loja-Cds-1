<?php

$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$installer->startSetup();
$entities = array(
    'quote_item',
    'order_item',
    'invoice_item',
    'creditmemo_item',
    'shipment_item'
);
$options = array(
    'type'     => 'text',
    'visible'  => false,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'ave_dimensions', $options);
}

$installer->endSetup();