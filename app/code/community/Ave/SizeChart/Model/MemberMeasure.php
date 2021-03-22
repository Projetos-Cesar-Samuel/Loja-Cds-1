<?php

class Ave_SizeChart_Model_MemberMeasure extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('ave_sizechart/memberMeasure');
    }

    public function loadByFields($bindFields)
    {
        $this->getResource()->loadByFields($this, $bindFields);
        return $this;
    }
}
