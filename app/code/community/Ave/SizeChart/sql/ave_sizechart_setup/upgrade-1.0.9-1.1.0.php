<?php

/** @var $installer Mage_Catalog_Model_Resource_Setup */
/** @var $this Mage_Catalog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$tName = $installer->getTable('ave_sizechart/member');
if (!$installer->getConnection()->isTableExists($tName)) {
    $table = $installer->getConnection()
        ->newTable($tName)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Member ID'
        )
        ->addColumn(
            'customer_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                'unsigned'  => true,
            ),
            'Customer ID'
        )
        ->addColumn(
            'name',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            array(
                'nullable'  => false,
            ),
            'Name'
        )
        ->addColumn(
            'active',
            Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
            array('default' => '1'),
            'Enabled'
        )
        ->addIndex($installer->getIdxName('ave_sizechart/member', array('customer_id')), array('customer_id'))
        ->addForeignKey(
            $installer->getFkName('ave_sizechart/member', 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer/entity'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Member Table');
    $installer->getConnection()->createTable($table);
}


$tName = $installer->getTable('ave_sizechart/member_measure');
if (!$installer->getConnection()->isTableExists($tName)) {
    $table = $installer->getConnection()
        ->newTable($tName)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Measure ID'
        )
        ->addColumn(
            'customer_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                'unsigned'  => true,
            ),
            'Customer ID'
        )
        ->addColumn(
            'member_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                'unsigned'  => true,
            ),
            'Member ID'
        )
        ->addColumn(
            'dimension_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                'unsigned'  => true,
            ),
            'Dimension ID'
        )
        ->addColumn(
            'value',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            array(
                'nullable'  => false,
            ),
            'Dimension value'
        )
        ->addIndex($installer->getIdxName('ave_sizechart/member_measure', array('customer_id')), array('customer_id'))
        ->addIndex($installer->getIdxName('ave_sizechart/member_measure', array('member_id')), array('member_id'))
        ->addIndex($installer->getIdxName('ave_sizechart/member_measure', array('dimension_id')), array('dimension_id'))
        ->addForeignKey(
            $installer->getFkName('ave_sizechart/member_measure', 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer/entity'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $installer->getFkName('ave_sizechart/member_measure', 'member_id', 'ave_sizechart/member', 'entity_id'),
            'member_id',
            $installer->getTable('ave_sizechart/member'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'ave_sizechart/member_measure',
                'dimension_id',
                'ave_sizechart/dimension',
                'entity_id'
            ),
            'dimension_id',
            $installer->getTable('ave_sizechart/dimension'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Member Measure Table');
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
