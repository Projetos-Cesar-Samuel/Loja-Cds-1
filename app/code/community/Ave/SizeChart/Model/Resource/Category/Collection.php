<?php

/**
 * Category of sizes collection resource model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Resource_Category_Collection extends Mage_Catalog_Model_Resource_Collection_Abstract
{
    protected $_joinedFields = array();

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('ave_sizechart/category');
    }

    /**
     * get categories of sizes as array
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     * @author averun <dev@averun.com>
     */
    protected function _toOptionArray($valueField='entity_id', $labelField='name', $additional=array())
    {
        $this->addAttributeToSelect('name');
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * get options hash
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @return array
     * @author averun <dev@averun.com>
     */
    protected function _toOptionHash($valueField='entity_id', $labelField='name')
    {
        $this->addAttributeToSelect('name');
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @access public
     * @return Varien_Db_Select
     * @author averun <dev@averun.com>
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Zend_Db_Select::GROUP);
        return $countSelect;
    }

    public function massUpdate(array $data)
    {
        $this->getConnection()->update(
            $this->getResource()->getMainTable(),
            $data,
            $this->getResource()->getIdFieldName() . ' IN(' . implode(',', $this->getAllIds()) . ')'
        );
        return $this;
    }
}
