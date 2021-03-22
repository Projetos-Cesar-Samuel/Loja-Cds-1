<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Productfilterpro_Model_Source_Display
{
    protected $_display;
    
	public function toOptionArray()
    {
        if ($this->_display === null) {
            $helper = Mage::helper('productfilterpro');
            $this->_display = array(
                array('value' => 'name',            'label' => $helper->__('Product name')),
                array('value' => 'label',           'label' => $helper->__('Product label')),
                array('value' => 'price',           'label' => $helper->__('Product price')),
                array('value' => 'sku',             'label' => $helper->__('SKU')),
                array('value' => 'review',          'label' => $helper->__('Rating')),
                array('value' => 'description',     'label' => $helper->__('Description')),
                array('value' => 'addtocart',       'label' => $helper->__('Add to cart button')),
                array('value' => 'wishlist' ,       'label' => $helper->__('Wishlist button')),
                array('value' => 'quickshop',       'label' => $helper->__('Quick Shop')),
                array('value' => 'compare',         'label' => $helper->__('Compare button'))
            );
		}
        return $this->_display;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}