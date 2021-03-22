<?php

    class Webkul_MobiKul_Model_Salesrule_Rule_Condition_Product_Combine extends Mage_SalesRule_Model_Rule_Condition_Product_Combine     {

        protected function _addAttributeToConditionGroup($conditionType, $conditionModel, $attributeCode, $attributeLabel)      {
            if (!array_key_exists($conditionType, $this->_productAttributesInfo)) {
                $this->_productAttributesInfo[$conditionType] = array();
            }
            $conditionKey = $attributeCode;
            $this->_productAttributesInfo[$conditionType][] = array(
                "label" => $attributeLabel,
                "value" => $conditionKey
            );
            return $this;
        }

        public function getNewChildSelectOptions()      {
            $conditions = parent::getNewChildSelectOptions();
            // $conditions = array_merge_recursive(
            //     $conditions,
            //     array(
            //         array(
            //             "label" => Mage::helper("catalog")->__("Conditions Combination"),
            //             "value" => "salesrule/rule_condition_product_combine"
            //         ),
            //         array(
            //             "label" => Mage::helper("catalog")->__("Cart Item Attribute"),
            //             "value" => $this->_getAttributeConditions(self::PRODUCT_ATTRIBUTES_TYPE_QUOTE_ITEM)
            //         ),
            //         array(
            //             "label" => Mage::helper("catalog")->__("Product Attribute"),
            //             "value" => $this->_getAttributeConditions(self::PRODUCT_ATTRIBUTES_TYPE_PRODUCT),
            //         ),
            //         array(
            //             "label" => $this->_getHelper()->__("Product Attribute Assigned"),
            //             "value" => $this->_getAttributeConditions(self::PRODUCT_ATTRIBUTES_TYPE_ISSET)
            //         )
            //     )
            // );
            return $conditions;
        }

    }