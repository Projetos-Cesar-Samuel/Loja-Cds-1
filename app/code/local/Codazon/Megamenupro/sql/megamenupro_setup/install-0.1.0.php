<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
	->newTable($installer->getTable('megamenupro/megamenupro'))
	->addColumn('menu_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Menu ID')
	->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Menu String Identifier')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Menu Title')
	->addColumn('type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'nullable'  => false,
		'default'   => '1',
		), 'Menu Type')
	->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'nullable'  => false,
		'default'   => '1',
		), 'Is Menu Active')
	->addColumn('content', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Menu Content')
	->addColumn('style', Varien_Db_Ddl_Table::TYPE_TEXT, '1M', array(
        ), 'Menu Style')
	->setComment('Codazon Mega Menu Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();