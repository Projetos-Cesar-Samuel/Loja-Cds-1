<?php

/**
 * SizeChart module install script
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */

/** @var $this Mage_Core_Model_Resource_Setup */

$isCreatedDimensions = false;

$this->startSetup();
$tName = $this->getTable('ave_sizechart/size');
if (!$this->getConnection()->isTableExists($tName)) {
    $table = $this->getConnection()
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
            'Size ID'
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
            'chart_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array(
                'unsigned'  => true,
            ),
            'Chart ID'
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
            'position',
            Varien_Db_Ddl_Table::TYPE_INTEGER, null,
            array('default' => '0',),
            'Position'
        )
        ->addColumn(
            'status',
            Varien_Db_Ddl_Table::TYPE_SMALLINT, null,
            array('default' => '1',),
            'Enabled'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Size Modification Time'
        )
        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Size Creation Time'
        )
        ->addIndex($this->getIdxName('ave_sizechart/chart', array('chart_id')), array('chart_id'))
        ->addIndex($this->getIdxName('ave_sizechart/dimension', array('dimension_id')), array('dimension_id'))
        ->setComment('Size Table');
    $this->getConnection()->createTable($table);
}

$tName = $this->getTable('ave_sizechart/chart');
if (!$this->getConnection()->isTableExists($tName)) {
    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Entity ID'
        )
        ->addColumn(
            'entity_type_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity Type ID'
        )
        ->addColumn(
            'attribute_set_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Attribute Set ID'
        )

        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null, array(),
            'Creation Time'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Update Time'
        )
        ->addIndex(
            $this->getIdxName(
                'ave_sizechart/chart',
                array('entity_type_id')
            ),
            array('entity_type_id')
        )
        ->addIndex(
            $this->getIdxName(
                'ave_sizechart/chart',
                array('attribute_set_id')
            ),
            array('attribute_set_id')
        )
        ->addForeignKey(
            $this->getFkName(
                'ave_sizechart/chart',
                'entity_type_id',
                'eav/entity_type',
                'entity_type_id'
            ),
            'entity_type_id',
            $this->getTable('eav/entity_type'),
            'entity_type_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Chart Table');
    $this->getConnection()->createTable($table);
    $this->addAttribute(
        'catalog_category',
        'ave_size_chart',
        array(
            'group'             => 'General Information',
            'backend'           => '',
            'frontend'          => '',
            'class'             => '',
            'default'           => '',
            'label'             => 'Size Chart',
            'input'             => 'select',
            'type'              => 'int',
            'source'            => 'ave_sizechart/chart_source',
            'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'required'          => 0,
            'unique'            => 0,
            'user_defined'      => 1,
        )
    );
}

$chartEav = array();
$chartEav['int'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length'    => null,
    'comment'   => 'Chart Datetime Attribute Backend Table'
);

$chartEav['varchar'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 255,
    'comment'   => 'Chart Varchar Attribute Backend Table'
);

$chartEav['text'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => '64k',
    'comment'   => 'Chart Text Attribute Backend Table'
);

$chartEav['datetime'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
    'length'    => null,
    'comment'   => 'Chart Datetime Attribute Backend Table'
);

$chartEav['decimal'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'length'    => '12,4',
    'comment'   => 'Chart Datetime Attribute Backend Table'
);

foreach ($chartEav as $type => $options) {
    $tName = $this->getTable(array('ave_sizechart/chart', $type));
    if ($this->getConnection()->isTableExists($tName)) {
        continue;
    }

    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'value_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Value ID'
        )
        ->addColumn(
            'entity_type_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity Type ID'
        )
        ->addColumn(
            'attribute_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Attribute ID'
        )
        ->addColumn(
            'store_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Store ID'
        )
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity ID'
        )
        ->addColumn(
            'value',
            $options['type'],
            $options['length'], array(),
            'Value'
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/chart', $type),
                array('entity_id', 'attribute_id', 'store_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('entity_id', 'attribute_id', 'store_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/chart', $type),
                array('store_id')
            ),
            array('store_id')
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/chart', $type),
                array('entity_id')
            ),
            array('entity_id')
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/chart', $type),
                array('attribute_id')
            ),
            array('attribute_id')
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/chart', $type),
                'attribute_id',
                'eav/attribute',
                'attribute_id'
            ),
            'attribute_id',
            $this->getTable('eav/attribute'),
            'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/chart', $type),
                'entity_id',
                'ave_sizechart/chart',
                'entity_id'
            ),
            'entity_id',
            $this->getTable('ave_sizechart/chart'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/chart', $type),
                'store_id',
                'core/store',
                'store_id'
            ),
            'store_id',
            $this->getTable('core/store'),
            'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment($options['comment']);
    $this->getConnection()->createTable($table);
}

$tName = $this->getTable('ave_sizechart/category');
if (!$this->getConnection()->isTableExists($tName)) {
    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Entity ID'
        )
        ->addColumn(
            'entity_type_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity Type ID'
        )
        ->addColumn(
            'attribute_set_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Attribute Set ID'
        )

        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null, array(),
            'Creation Time'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Update Time'
        )
        ->addIndex(
            $this->getIdxName(
                'ave_sizechart/category',
                array('entity_type_id')
            ),
            array('entity_type_id')
        )
        ->addIndex(
            $this->getIdxName(
                'ave_sizechart/category',
                array('attribute_set_id')
            ),
            array('attribute_set_id')
        )
        ->addForeignKey(
            $this->getFkName(
                'ave_sizechart/category',
                'entity_type_id',
                'eav/entity_type',
                'entity_type_id'
            ),
            'entity_type_id',
            $this->getTable('eav/entity_type'),
            'entity_type_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Category of sizes Table');
    $this->getConnection()->createTable($table);
}

$categoryEav = array();
$categoryEav['int'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length'    => null,
    'comment'   => 'Category of sizes Datetime Attribute Backend Table'
);

$categoryEav['varchar'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 255,
    'comment'   => 'Category of sizes Varchar Attribute Backend Table'
);

$categoryEav['text'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => '64k',
    'comment'   => 'Category of sizes Text Attribute Backend Table'
);

$categoryEav['datetime'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
    'length'    => null,
    'comment'   => 'Category of sizes Datetime Attribute Backend Table'
);

$categoryEav['decimal'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'length'    => '12,4',
    'comment'   => 'Category of sizes Datetime Attribute Backend Table'
);

foreach ($categoryEav as $type => $options) {
    $tName = $this->getTable(array('ave_sizechart/category', $type));
    if ($this->getConnection()->isTableExists($tName)) {
        continue;
    }

    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'value_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Value ID'
        )
        ->addColumn(
            'entity_type_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity Type ID'
        )
        ->addColumn(
            'attribute_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Attribute ID'
        )
        ->addColumn(
            'store_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Store ID'
        )
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity ID'
        )
        ->addColumn(
            'value',
            $options['type'],
            $options['length'], array(),
            'Value'
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/category', $type),
                array('entity_id', 'attribute_id', 'store_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('entity_id', 'attribute_id', 'store_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/category', $type),
                array('store_id')
            ),
            array('store_id')
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/category', $type),
                array('entity_id')
            ),
            array('entity_id')
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/category', $type),
                array('attribute_id')
            ),
            array('attribute_id')
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/category', $type),
                'attribute_id',
                'eav/attribute',
                'attribute_id'
            ),
            'attribute_id',
            $this->getTable('eav/attribute'),
            'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/category', $type),
                'entity_id',
                'ave_sizechart/category',
                'entity_id'
            ),
            'entity_id',
            $this->getTable('ave_sizechart/category'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/category', $type),
                'store_id',
                'core/store',
                'store_id'
            ),
            'store_id',
            $this->getTable('core/store'),
            'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment($options['comment']);
    $this->getConnection()->createTable($table);
}

$tName = $this->getTable('ave_sizechart/dimension');
if (!$this->getConnection()->isTableExists($tName)) {
    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Entity ID'
        )
        ->addColumn(
            'entity_type_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity Type ID'
        )
        ->addColumn(
            'attribute_set_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Attribute Set ID'
        )

        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null, array(),
            'Creation Time'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Update Time'
        )
        ->addIndex(
            $this->getIdxName(
                'ave_sizechart/dimension',
                array('entity_type_id')
            ),
            array('entity_type_id')
        )
        ->addIndex(
            $this->getIdxName(
                'ave_sizechart/dimension',
                array('attribute_set_id')
            ),
            array('attribute_set_id')
        )
        ->addForeignKey(
            $this->getFkName(
                'ave_sizechart/dimension',
                'entity_type_id',
                'eav/entity_type',
                'entity_type_id'
            ),
            'entity_type_id',
            $this->getTable('eav/entity_type'),
            'entity_type_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Dimension Table');
    $this->getConnection()->createTable($table);
    $isCreatedDimensions = true;
}

$dimensionEav = array();
$dimensionEav['int'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length'    => null,
    'comment'   => 'Dimension Datetime Attribute Backend Table'
);

$dimensionEav['varchar'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 255,
    'comment'   => 'Dimension Varchar Attribute Backend Table'
);

$dimensionEav['text'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => '64k',
    'comment'   => 'Dimension Text Attribute Backend Table'
);

$dimensionEav['datetime'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
    'length'    => null,
    'comment'   => 'Dimension Datetime Attribute Backend Table'
);

$dimensionEav['decimal'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'length'    => '12,4',
    'comment'   => 'Dimension Datetime Attribute Backend Table'
);

foreach ($dimensionEav as $type => $options) {
    $tName = $this->getTable(array('ave_sizechart/dimension', $type));
    if ($this->getConnection()->isTableExists($tName)) {
        continue;
    }

    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'value_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Value ID'
        )
        ->addColumn(
            'entity_type_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity Type ID'
        )
        ->addColumn(
            'attribute_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Attribute ID'
        )
        ->addColumn(
            'store_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Store ID'
        )
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity ID'
        )
        ->addColumn(
            'value',
            $options['type'],
            $options['length'], array(),
            'Value'
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/dimension', $type),
                array('entity_id', 'attribute_id', 'store_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('entity_id', 'attribute_id', 'store_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/dimension', $type),
                array('store_id')
            ),
            array('store_id')
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/dimension', $type),
                array('entity_id')
            ),
            array('entity_id')
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/dimension', $type),
                array('attribute_id')
            ),
            array('attribute_id')
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/dimension', $type),
                'attribute_id',
                'eav/attribute',
                'attribute_id'
            ),
            'attribute_id',
            $this->getTable('eav/attribute'),
            'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/dimension', $type),
                'entity_id',
                'ave_sizechart/dimension',
                'entity_id'
            ),
            'entity_id',
            $this->getTable('ave_sizechart/dimension'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/dimension', $type),
                'store_id',
                'core/store',
                'store_id'
            ),
            'store_id',
            $this->getTable('core/store'),
            'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment($options['comment']);
    $this->getConnection()->createTable($table);
}

$tName = $this->getTable('ave_sizechart/type');
if (!$this->getConnection()->isTableExists($tName)) {
    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Entity ID'
        )
        ->addColumn(
            'entity_type_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity Type ID'
        )
        ->addColumn(
            'attribute_set_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Attribute Set ID'
        )

        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null, array(),
            'Creation Time'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            array(),
            'Update Time'
        )
        ->addIndex(
            $this->getIdxName(
                'ave_sizechart/type',
                array('entity_type_id')
            ),
            array('entity_type_id')
        )
        ->addIndex(
            $this->getIdxName(
                'ave_sizechart/type',
                array('attribute_set_id')
            ),
            array('attribute_set_id')
        )
        ->addForeignKey(
            $this->getFkName(
                'ave_sizechart/type',
                'entity_type_id',
                'eav/entity_type',
                'entity_type_id'
            ),
            'entity_type_id',
            $this->getTable('eav/entity_type'),
            'entity_type_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment('Type Table');
    $this->getConnection()->createTable($table);
}

$typeEav = array();
$typeEav['int'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length'    => null,
    'comment'   => 'Type Datetime Attribute Backend Table'
);

$typeEav['varchar'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 255,
    'comment'   => 'Type Varchar Attribute Backend Table'
);

$typeEav['text'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => '64k',
    'comment'   => 'Type Text Attribute Backend Table'
);

$typeEav['datetime'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
    'length'    => null,
    'comment'   => 'Type Datetime Attribute Backend Table'
);

$typeEav['decimal'] = array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'length'    => '12,4',
    'comment'   => 'Type Datetime Attribute Backend Table'
);

foreach ($typeEav as $type => $options) {
    $tName = $this->getTable(array('ave_sizechart/type', $type));
    if ($this->getConnection()->isTableExists($tName)) {
        continue;
    }

    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'value_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Value ID'
        )
        ->addColumn(
            'entity_type_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity Type ID'
        )
        ->addColumn(
            'attribute_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Attribute ID'
        )
        ->addColumn(
            'store_id',
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Store ID'
        )
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ),
            'Entity ID'
        )
        ->addColumn(
            'value',
            $options['type'],
            $options['length'], array(),
            'Value'
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/type', $type),
                array('entity_id', 'attribute_id', 'store_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('entity_id', 'attribute_id', 'store_id'),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/type', $type),
                array('store_id')
            ),
            array('store_id')
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/type', $type),
                array('entity_id')
            ),
            array('entity_id')
        )
        ->addIndex(
            $this->getIdxName(
                array('ave_sizechart/type', $type),
                array('attribute_id')
            ),
            array('attribute_id')
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/type', $type),
                'attribute_id',
                'eav/attribute',
                'attribute_id'
            ),
            'attribute_id',
            $this->getTable('eav/attribute'),
            'attribute_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/type', $type),
                'entity_id',
                'ave_sizechart/type',
                'entity_id'
            ),
            'entity_id',
            $this->getTable('ave_sizechart/type'),
            'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->addForeignKey(
            $this->getFkName(
                array('ave_sizechart/type', $type),
                'store_id',
                'core/store',
                'store_id'
            ),
            'store_id',
            $this->getTable('core/store'),
            'store_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE,
            Varien_Db_Ddl_Table::ACTION_CASCADE
        )
        ->setComment($options['comment']);
    $this->getConnection()->createTable($table);
}

$tName = $this->getTable('ave_sizechart/eav_attribute');
if (!$this->getConnection()->isTableExists($tName)) {
    $table = $this->getConnection()
        ->newTable($tName)
        ->addColumn(
            'attribute_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'identity'  => true,
                'nullable'  => false,
                'primary'   => true,
            ),
            'Attribute ID'
        )
        ->addColumn(
            'is_global',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(),
            'Attribute scope'
        )
        ->addColumn(
            'position',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(),
            'Attribute position'
        )
        ->addColumn(
            'is_wysiwyg_enabled',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(),
            'Attribute uses WYSIWYG'
        )
        ->addColumn(
            'is_visible',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(),
            'Attribute is visible'
        )
        ->setComment('SizeChart attribute table');
    $this->getConnection()->createTable($table);
}

$this->installEntities();


if ($isCreatedDimensions) {
    $attribute = Mage::getSingleton('eav/config')->getAttribute('ave_sizechart_dimension', 'type');
    $options = $attribute->getSource()->getAllOptions(false);
    foreach ($options as $option) {
        if ($option['label'] == 'Region') {
            $this->updateAttribute('ave_sizechart_dimension', 'type', 'default_value', $option['value']);
        }
    }
}

$this->endSetup();
