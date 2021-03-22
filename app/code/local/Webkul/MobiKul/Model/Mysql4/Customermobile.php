<?php

    class Webkul_MobiKul_Model_Mysql4_Customermobile extends Mage_Core_Model_Mysql4_Abstract {

        public function _construct() {
            $this->_init("mobikul/customermobile", "id");
        }

    }