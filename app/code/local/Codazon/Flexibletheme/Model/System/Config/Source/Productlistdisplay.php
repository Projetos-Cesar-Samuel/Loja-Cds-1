<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Productlistdisplay
{ 
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('flexibletheme');
        return array(
        	array('value' => 'thumb', 'label' => $helper->__('Thumbnail')),
        	array('value' => 'name', 'label' => $helper->__('Name')),
        	array('value' => 'sku', 'label' => $helper->__('SKU')),
        	array('value' => 'description', 'label' => $helper->__('Description')),
        	array('value' => 'review', 'label' => $helper->__('Review')),
        	array('value' => 'price', 'label' => $helper->__('Price')),
        	array('value' => 'addtocart', 'label' => $helper->__('Add to cart')),
        	array('value' => 'wishlist', 'label' => $helper->__('Wishlist')),
            array('value' => 'compare', 'label' => $helper->__('Compare')),
            array('value' => 'quickshop', 'label' => $helper->__('Quick Shop')),
            array('value' => 'label', 'label' => $helper->__('Label'))
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toOptionArray();
    }
}