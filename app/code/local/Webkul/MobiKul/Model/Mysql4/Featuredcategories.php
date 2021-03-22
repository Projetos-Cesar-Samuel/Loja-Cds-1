<?php

    class Webkul_MobiKul_Model_Mysql4_Featuredcategories extends Mage_Core_Model_Mysql4_Abstract {

        public function _construct() {
            $this->_init("mobikul/featuredcategories", "id");
        }

    }