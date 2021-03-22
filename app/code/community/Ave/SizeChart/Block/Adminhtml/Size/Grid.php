<?php

/**
 * Size admin grid block
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Size_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     * @author averun <dev@averun.com>
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sizeGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Size_Grid
     * @author averun <dev@averun.com>
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ave_sizechart/size')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Size_Grid
     * @author averun <dev@averun.com>
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('ave_sizechart')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        $this->addColumn(
            'name',
            array(
                'header'    => Mage::helper('ave_sizechart')->__('Name'),
                'align'     => 'left',
                'index'     => 'name',
            )
        );
        $this->addColumn(
            'chart_id',
            array(
                'header'    => Mage::helper('ave_sizechart')->__('Chart'),
                'index'     => 'chart_id',
                'type'      => 'options',
                'options'   => Mage::getResourceModel('ave_sizechart/chart_collection')
                    ->addAttributeToSelect('name')->toOptionHash(),
                'renderer'  => 'ave_sizechart/adminhtml_helper_column_renderer_parent',
                'params'    => array(
                    'id'    => 'getChartId'
                ),
                'base_link' => 'adminhtml/sizechart_chart/edit'
            )
        );
        $this->addColumn(
            'dimension_id',
            array(
                'header'    => Mage::helper('ave_sizechart')->__('Dimension'),
                'index'     => 'dimension_id',
                'type'      => 'options',
                'options'   => Mage::getResourceModel('ave_sizechart/dimension_collection')
                    ->addAttributeToSelect('name')->toOptionHash(),
                'renderer'  => 'ave_sizechart/adminhtml_helper_column_renderer_parent',
                'params'    => array(
                    'id'    => 'getDimensionId'
                ),
                'base_link' => 'adminhtml/sizechart_dimension/edit'
            )
        );
        $this->addColumn(
            'position',
            array(
                'header'    => Mage::helper('ave_sizechart')->__('Position'),
                'align'     => 'left',
                'index'     => 'position',
            )
        );
        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('ave_sizechart')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    '1' => Mage::helper('ave_sizechart')->__('Enabled'),
                    '0' => Mage::helper('ave_sizechart')->__('Disabled'),
                )
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('ave_sizechart')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('ave_sizechart')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('ave_sizechart')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('ave_sizechart')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('ave_sizechart')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Size_Grid
     * @author averun <dev@averun.com>
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('size');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('ave_sizechart')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('ave_sizechart')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('ave_sizechart')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('ave_sizechart')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('ave_sizechart')->__('Enabled'),
                            '0' => Mage::helper('ave_sizechart')->__('Disabled'),
                        )
                    )
                )
            )
        );
        $values = Mage::getResourceModel('ave_sizechart/chart_collection')->toOptionHash();
        $values = array_reverse($values, true);
        $values[''] = '';
        $values = array_reverse($values, true);
        $this->getMassactionBlock()->addItem(
            'chart_id',
            array(
                'label'      => Mage::helper('ave_sizechart')->__('Change Chart'),
                'url'        => $this->getUrl('*/*/massChartId', array('_current'=>true)),
                'additional' => array(
                    'flag_chart_id' => array(
                        'name'   => 'flag_chart_id',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('ave_sizechart')->__('Chart'),
                        'values' => $values
                    )
                )
            )
        );
        $values = Mage::getResourceModel('ave_sizechart/dimension_collection')->toOptionHash();
        $values = array_reverse($values, true);
        $values[''] = '';
        $values = array_reverse($values, true);
        $this->getMassactionBlock()->addItem(
            'dimension_id',
            array(
                'label'      => Mage::helper('ave_sizechart')->__('Change Dimension'),
                'url'        => $this->getUrl('*/*/massDimensionId', array('_current'=>true)),
                'additional' => array(
                    'flag_dimension_id' => array(
                        'name'   => 'flag_dimension_id',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('ave_sizechart')->__('Dimension'),
                        'values' => $values
                    )
                )
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param Ave_SizeChart_Model_Size
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Size_Grid
     * @author averun <dev@averun.com>
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}
