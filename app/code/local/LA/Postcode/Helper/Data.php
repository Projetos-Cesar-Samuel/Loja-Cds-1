<?php

/**
 * Postcode Helper
 * 
 * @category    Magestore
 * @package     Magestore_Postcode
 * @author      Magestore Developer
 */
class LA_Postcode_Helper_Data extends Mage_Core_Helper_Abstract
{



    public function getDataPostcode($zipcode = null){
        if (!Mage::getStoreConfig('postcode/general/enable')){
            return;
        }

        $PostcodeShipping = $this->convertPostcode($zipcode);

        $model = Mage::getModel('postcode/postcode')->getCollection()
            ->addFieldToFilter('status', '1');

        $shippingMethod = array();
        $payment_method = array();
        $dataPostcode = array();

        if (!count($model)) {
            return $dataPostcode;
        }

        try {
            foreach ($model as $data){
                $postcode_from = $this->convertPostcode($data->getData('postcode_from'));
                $postcode_to = $this->convertPostcode($data->getData('postcode_to'));

                if ($postcode_from <= $PostcodeShipping && $PostcodeShipping <= $postcode_to) {
                    $conditionsPostcode = $data->getData('conditions_serialized');
                    $conditions_serialized = unserialize($conditionsPostcode);
                    $conditions = $conditions_serialized['conditions'];
                    foreach ($conditions as $condition) {
                        if ($condition['attribute'] == 'payment_method') {
                            $payment_method[] = $condition['value'];
                        }
                        if ($condition['attribute'] == 'shipping_method') {
                            $shippingMethod[] = $condition['value'];
                        }
                    }

                }
            }

            $payment_method = array_unique($payment_method);
            $shippingMethod = array_unique($shippingMethod);
            $dataPostcode[$zipcode]['payment_method'] = $payment_method;
            $dataPostcode[$zipcode]['shipping_method'] = $shippingMethod;
            return $dataPostcode;

        }catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return;
        }
    }

    public function convertPostcode($postcode = null){
        $lengthTo = strpos($postcode, '-');
        if ($lengthTo) {
            $postcode = str_replace('-', '', $postcode);
        }
        $postcode = (int)$postcode;

        return $postcode;
    }

    public function checkIssetPostcode(){
        $model = Mage::getModel('postcode/postcode')->getCollection()
            ->addFieldToFilter('status', '1');
        return count($model);
    }
}