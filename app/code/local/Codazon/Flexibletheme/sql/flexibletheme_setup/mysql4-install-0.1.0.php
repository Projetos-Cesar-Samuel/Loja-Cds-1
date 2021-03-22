<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
$installer = $this;
$installer->startSetup();

$elements = array(
	'flexibletheme_content',
	'flexibletheme_header',
	'flexibletheme_footer'
);
$connection = $installer->getConnection();

foreach ($elements as $element) {
	$entity = $element . '_entity';
	$entityTable = $installer->getTable($entity);
	
	$table = $connection->newTable($entityTable)
		->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
			'identity'  => true,
			'unsigned'  => true,
			'nullable'  => false,
			'primary'   => true,
		), 'Entity ID')
		->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
			'unsigned'  => true,
			'nullable'  => false,
		), 'Entity Type ID')
		->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
			'nullable'  => false
		), 'Identifier')
		->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, 1, array(
			'nullable'  => false,
			'default'   => 1
		), 'Is active')
        ->addColumn('parent', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
			'nullable'  => true
		), 'Parent')
		->addColumn('variables', Varien_Db_Ddl_Table::TYPE_TEXT, Varien_Db_Ddl_Table::MAX_TEXT_SIZE, array(
			'nullable'  => true
		), 'Variables')
		->addColumn('custom_fields', Varien_Db_Ddl_Table::TYPE_TEXT, Varien_Db_Ddl_Table::MAX_TEXT_SIZE, array(
			'nullable'  => true
		), 'Custom Fields')
        ->addIndex(
            $installer->getIdxName(
                $entityTable,
                array('identifier'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('identifier'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
		->setComment('Flexible Theme Table');
	
	$connection->createTable($table);
	
	$types = array(
		'datetime',
		'decimal',
		'int',
		'text',
		'varchar'
	);
	
	foreach ($types as $type) {
		$entityTypeTable = $installer->getTable($entity . '_' . $type);
		
		switch ($type) {
			case 'text':
				$valueType = $type;
				$valueSize = Varien_Db_Ddl_Table::MAX_TEXT_SIZE;
				break;
			case 'varchar':
				$valueType = $type;
				$valueSize = 255;
				break;
			case 'int':
				$valueType = Varien_Db_Ddl_Table::TYPE_INTEGER;
				$valueSize = 11;
				break;
			case 'decimal':
                $valueType = Varien_Db_Ddl_Table::TYPE_DECIMAL;
				$valueSize = '12,4';
                break;
			case 'datetime':
			default:
				$valueType = $type;
				$valueSize = null;
				break;
		}
		$comment = $entity . ' - ' . $type;
		$table = $connection->newTable($entityTypeTable)
			->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
				'identity'  => true,
				'unsigned'  => true,
				'nullable'  => false,
				'primary'   => true
			), 'Value Id')
			->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
				'unsigned' => true,
				'nullable' => false,
				'default' => '0',
			), 'Entity Type Id')
			->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
				'unsigned'  => true,
				'nullable'  => false,
				'default' => '0'
			), 'Attribute Id')
			->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
				'unsigned' => true,
				'nullable' => false,
				'default' => '0',
			), 'Store ID')
			->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
				'unsigned' => true,
				'nullable' => false,
				'default' => '0',
			), 'Entity Id')
			->addColumn('value', $valueType, $valueSize, array(), 'Value')
            ->addIndex(
                $installer->getIdxName(
                    $entityTypeTable,
                    array('entity_id', 'attribute_id', 'store_id'),
                    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
                ),
                array('entity_id', 'attribute_id', 'store_id'),
                array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
            ->addIndex($installer->getIdxName($entityTypeTable, array('attribute_id')),
                array('attribute_id'))
            ->addIndex($installer->getIdxName($entityTypeTable, array('store_id')),
                array('store_id'))
            ->addIndex($installer->getIdxName($entityTypeTable, array('entity_id')),
                array('entity_id'))
            ->addForeignKey(
                $installer->getFkName($entityTypeTable, 'attribute_id', 'eav/attribute', 'attribute_id'),
                'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->addForeignKey(
                $installer->getFkName($entityTypeTable, 'entity_id', $entityTable, 'entity_id'),
                'entity_id', $entityTable, 'entity_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->addForeignKey(
                $installer->getFkName($entityTypeTable, 'store_id', 'core/store', 'store_id'),
                'store_id', $installer->getTable('core/store'), 'store_id',
                Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->setComment($comment);
		$connection->createTable($table);
	}
}

$table = $installer->getConnection()
    ->newTable($installer->getTable('flexibletheme/theme'))
    ->addColumn('theme_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Theme Id')
    ->addColumn('theme_title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Theme Title')
    ->addColumn('theme_package', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Theme Package')
    ->addColumn('theme_template', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        ), 'Theme Template')
    ->addColumn('preview_image', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => true,
        ), 'Preview Image')
    ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_BOOLEAN, 1, array(
			'nullable'  => false,
			'default'   => 1
		), 'Is active')
    ->addIndex($installer->getIdxName('flexibletheme/theme', array('theme_package', 'theme_template'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('theme_package', 'theme_template'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Themes');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('flexibletheme/config_data'))
    ->addColumn('config_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Config Id')
    ->addColumn('scope', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
        'nullable'  => false,
        'default'   => 'default',
        ), 'Config Scope')
    ->addColumn('scope_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Config Scope Id')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'nullable'  => false,
        'default'   => 'general',
        ), 'Config Path')
    ->addColumn('theme_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        ), 'Theme ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'Config Value')
    ->addIndex($installer->getIdxName('flexibletheme/config_data', array('scope', 'scope_id', 'path'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('scope', 'scope_id', 'path', 'theme_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
        $installer->getFkName('flexibletheme/config_data', 'theme_id', 'flexibletheme/theme', 'theme_id'),
        'theme_id', $installer->getTable('flexibletheme/theme'), 'theme_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Theme Config Data');
$installer->getConnection()->createTable($table);



$installer->installEntities(); 
$installer->endSetup();