<?php

/**
 * Type admin grid block
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Type_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setId('typeGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Type_Grid
     * @author averun <dev@averun.com>
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ave_sizechart/type')
            ->getCollection()
            ->addAttributeToSelect('position')
            ->addAttributeToSelect('status');
        
        $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
        $store = $this->_getStore();
        $collection->joinAttribute(
            'name', 
            'ave_sizechart_type/name', 
            'entity_id', 
            null, 
            'inner', 
            $adminStore
        );
        if ($store->getId()) {
            $collection->joinAttribute(
                'ave_sizechart_type_name',
                'ave_sizechart_type/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Type_Grid
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
        
        if ($this->_getStore()->getId()) {
            $this->addColumn(
                'ave_sizechart_type_name', 
                array(
                    'header'    => Mage::helper('ave_sizechart')->__('Name in %s', $this->_getStore()->getName()),
                    'align'     => 'left',
                    'index'     => 'ave_sizechart_type_name',
                )
            );
        }

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
            'position',
            array(
                'header' => Mage::helper('ave_sizechart')->__('Position'),
                'index'  => 'position',
                'type'=> 'number',

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
     * get the selected store
     *
     * @access protected
     * @return Mage_Core_Model_Store
     * @author averun <dev@averun.com>
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Type_Grid
     * @author averun <dev@averun.com>
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('type');
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
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param Ave_SizeChart_Model_Type
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
}
