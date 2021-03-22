<?php

/**
 * Postcode Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_Postcode
 * @author      Magestore Developer
 */
class LA_Postcode_Model_Condition_Subtotalwithdiscount extends Mage_Rule_Model_Condition_Abstract {

    /**
     * @TODO for whatever this it, check it and afterwards document it!
     *
     * @return Hackathon_DiscountForATweet_Model_Condition_Tweet
     */
    public function loadAttributeOptions() {
        $attributes = array(
            'subtotalwithdiscount' => Mage::helper('postcode')->__('Subtotal With Discount')
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @TODO for whatever this it, check it and afterwards document it!
     *
     * @return mixed
     */
    public function getAttributeElement() {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    /**
     * @TODO for whatever this it, check it and afterwards document it!
     *
     * @return string
     */
    public function getInputType() {

        switch ($this->getAttribute()) {
            case 'subtotalwithdiscount':
                return 'numeric';
        }
        return 'string';
    }

    /**
     * @TODO for whatever this it, check it and afterwards document it!
     * @return string
     */
    public function getValueElementType() {
        return 'text';
    }

    /**
     * Validate FamiliencarteHessen Rule Condition
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object) {

        /* here should be something meaningful */
        $address = $object;
        if (!$address instanceof Mage_Sales_Model_Quote_Address) {
            if ($object->getQuote()->isVirtual()) {
                $address = $object->getQuote()->getBillingAddress();
            }
            else {
                $address = $object->getQuote()->getShippingAddress();
            }
        }
        $address->setSubtotalwithdiscount($address->getBaseSubtotal() + $address->getDiscountAmount());
        
        return $this->validateAttribute(trim($address->getSubtotalwithdiscount()));
    }

}