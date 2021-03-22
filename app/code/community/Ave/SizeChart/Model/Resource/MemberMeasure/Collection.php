<?php

class Ave_SizeChart_Model_Resource_MemberMeasure_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('ave_sizechart/memberMeasure');

    }
}
