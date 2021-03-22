<?php

/**
 * Size collection resource model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Resource_Size_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
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
        $this->_init('ave_sizechart/size');
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

    public function massDelete()
    {
        if (($ids = $this->getAllIds()) && !empty($ids)) {
            $this->getConnection()->delete(
                $this->getResource()->getMainTable(),
                $this->getResource()->getIdFieldName() . ' IN(' . implode(',', $ids) . ')'
            );
        }
    }
}
