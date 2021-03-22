<?php

/**
 * Dimension attributes grid
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Dimension_Attribute_Grid extends Mage_Eav_Block_Adminhtml_Attribute_Grid_Abstract
{
    /**
     * Prepare dimension attributes grid collection object
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Dimension_Attribute_Grid
     * @author averun <dev@averun.com>
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('ave_sizechart/dimension_attribute_collection')
            ->addVisibleFilter();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare dimension attributes grid columns
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Dimension_Attribute_Grid
     * @author averun <dev@averun.com>
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumnAfter(
            'is_global',
            array(
                'header'   => Mage::helper('ave_sizechart')->__('Scope'),
                'sortable' => true,
                'index'    => 'is_global',
                'type'     => 'options',
                'options'  => array(
                    Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE   =>
                        Mage::helper('ave_sizechart')->__('Store View'),
                    Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE =>
                        Mage::helper('ave_sizechart')->__('Website'),
                    Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL  =>
                        Mage::helper('ave_sizechart')->__('Global'),
                ),
                'align' => 'center',
            ),
            'is_user_defined'
        );
        return $this;
    }
}
