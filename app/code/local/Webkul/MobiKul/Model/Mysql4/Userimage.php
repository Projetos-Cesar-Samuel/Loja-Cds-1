<?php

    class Webkul_MobiKul_Model_Mysql4_Userimage extends Mage_Core_Model_Mysql4_Abstract {

        public function _construct() {
            $this->_init("mobikul/userimage", "id");
        }

    }