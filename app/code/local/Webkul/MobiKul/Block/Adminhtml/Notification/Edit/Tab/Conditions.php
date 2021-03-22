<?php

    class Webkul_MobiKul_Block_Adminhtml_Notification_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form {

        protected function _construct() {
            parent::_construct();
            $this->setTemplate("mobikul/conditions.phtml");
        }

        public function getAssignedAttributes(){
            $options = Mage::getModel("mobikul/salesrule_rule_condition_product_combine")->getNewChildSelectOptions();
            return $options[3]["value"];
        }

        public function getProductsJson(){
            $notification = Mage::registry("notification_data");
            if($notification->getCollectionType() == "product_ids"){
                $filterData = unserialize($notification->getFilterData());
                $productIds = explode(",", $filterData);
            }
            else
                $productIds = array();
            return Mage::helper("core")->jsonEncode($productIds);
        }

    }