<?php

    class Webkul_MobiKul_Model_Mysql4_Notification extends Mage_Core_Model_Mysql4_Abstract {

        public function _construct() {
            $this->_init("mobikul/notification", "id");
        }

    }