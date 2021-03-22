<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Productviewstyles
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
            array('value' => 'catalog/product/view/view-styles/view-style-01.phtml', 'label' => $helper->__('Style 01')),
            array('value' => 'catalog/product/view/view-styles/view-style-02.phtml', 'label' => $helper->__('Style 02')),
            array('value' => 'catalog/product/view/view-styles/view-style-03.phtml', 'label' => $helper->__('Style 03')),
            array('value' => 'catalog/product/view/view-styles/view-style-04.phtml', 'label' => $helper->__('Style 04')),
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