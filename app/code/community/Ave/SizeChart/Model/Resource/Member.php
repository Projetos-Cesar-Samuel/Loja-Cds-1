<?php

class Ave_SizeChart_Model_Resource_Member extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('ave_sizechart/member', 'entity_id');
    }

    public function loadByFields($model, $bindFields)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from($this->getMainTable(), '*');
        foreach ($bindFields as $key => $value) {
            $select->where($key . ' = ' . $value);
        }

        $modelId = $adapter->fetchOne($select);
        if ($modelId) {
            $this->load($model, $modelId);
        } else {
            $model->setData(array());
        }

        return $this;
    }
}
