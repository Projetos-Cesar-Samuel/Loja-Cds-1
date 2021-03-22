<?php

/**
 * Postcode Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Postcode
 * @author      Magestore Developer
 */
class LA_Postcode_Model_Observer extends Mage_Core_Model_Abstract {

    /**
     * Event: salesrule_rule_condition_combine
     *
     * @param $observer
     */
    public function addConditionToSalesRule($observer) {

        $additional = $observer->getAdditional();
        $conditions = (array) $additional->getConditions();

        $conditions = array_merge_recursive($conditions, array(
            array('label'=>Mage::helper('postcode')->__('Subtotal With Discount'), 'value'=>'postcode/condition_subtotalwithdiscount'),
        ));

        $additional->setConditions($conditions);
        $observer->setAdditional($additional);

        return $observer;
    }
}