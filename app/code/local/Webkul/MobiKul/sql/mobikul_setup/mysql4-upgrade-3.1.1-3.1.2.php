<?php
$installer = $this;
$installer->startSetup();

$prefix = Mage::getConfig()->getTablePrefix();

$connection = $this->getConnection();

$connection->addColumn($prefix.'mobikul_devicetoken', 'os', 'varchar(100) NOT NULL ');

$installer->endSetup(); 
