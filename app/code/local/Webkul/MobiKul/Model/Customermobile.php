<?php

    class Webkul_MobiKul_Model_Customermobile extends Mage_Core_Model_Abstract {

        public function _construct() {
            parent::_construct();
            $this->_init("mobikul/customermobile");
        }

    }