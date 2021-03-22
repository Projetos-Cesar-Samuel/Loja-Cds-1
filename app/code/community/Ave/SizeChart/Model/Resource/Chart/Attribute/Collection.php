<?php

/**
 * Chart attribute collection model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Resource_Chart_Attribute_Collection
    extends Mage_Eav_Model_Resource_Entity_Attribute_Collection
{
    /**
     * init attribute select
     *
     * @access protected
     * @return Ave_SizeChart_Model_Resource_Chart_Attribute_Collection
     * @author averun <dev@averun.com>
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable()))
            ->where(
                'main_table.entity_type_id=?',
                Mage::getModel('eav/entity')->setType('ave_sizechart_chart')->getTypeId()
            )
            ->join(
                array('additional_table' => $this->getTable('ave_sizechart/eav_attribute')),
                'additional_table.attribute_id=main_table.attribute_id'
            );
        return $this;
    }

    /**
     * set entity type filter
     *
     * @access public
     * @param string $typeId
     * @return Ave_SizeChart_Model_Resource_Chart_Attribute_Collection
     * @author averun <dev@averun.com>
     */
    public function setEntityTypeFilter($typeId)
    {
        return $this;
    }

    /**
     * Specify filter by "is_visible" field
     *
     * @access public
     * @return Ave_SizeChart_Model_Resource_Chart_Attribute_Collection
     * @author averun <dev@averun.com>
     */
    public function addVisibleFilter()
    {
        return $this->addFieldToFilter('additional_table.is_visible', 1);
    }

    /**
     * Specify filter by "is_editable" field
     *
     * @access public
     * @return Ave_SizeChart_Model_Resource_Chart_Attribute_Collection
     * @author averun <dev@averun.com>
     */
    public function addEditableFilter()
    {
        return $this->addFieldToFilter('additional_table.is_editable', 1);
    }
}
