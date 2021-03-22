<?php

class Ave_SizeChart_Model_Resource_Member_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('ave_sizechart/member');
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
