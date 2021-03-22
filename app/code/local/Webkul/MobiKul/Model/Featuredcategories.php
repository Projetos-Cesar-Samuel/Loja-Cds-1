<?php

    class Webkul_MobiKul_Model_Featuredcategories extends Mage_Core_Model_Abstract {

        public function _construct() {
            parent::_construct();
            $this->_init("mobikul/featuredcategories");
        }

    }