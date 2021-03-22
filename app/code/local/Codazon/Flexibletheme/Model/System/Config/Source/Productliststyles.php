<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Productliststyles
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
            array('value' => 'catalog/product/list/grid-styles/grid-style-01.phtml', 'label' => $helper->__('Style 01')),
            array('value' => 'catalog/product/list/grid-styles/grid-style-02.phtml', 'label' => $helper->__('Style 02')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-03.phtml', 'label' => $helper->__('Style 03')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-04.phtml', 'label' => $helper->__('Style 04')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-05.phtml', 'label' => $helper->__('Style 05')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-09.phtml', 'label' => $helper->__('Style 09')),
			//array('value' => 'catalog/product/list/grid-styles/grid-style-10.phtml', 'label' => $helper->__('Style 10')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-13.phtml', 'label' => $helper->__('Style 13')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-14.phtml', 'label' => $helper->__('Style 14')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-16.phtml', 'label' => $helper->__('Style 16')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-17.phtml', 'label' => $helper->__('Style 17')),
			//array('value' => 'catalog/product/list/grid-styles/grid-style-18.phtml', 'label' => $helper->__('Style 18')),
			//array('value' => 'catalog/product/list/grid-styles/grid-style-19.phtml', 'label' => $helper->__('Style 19')),
			array('value' => 'catalog/product/list/grid-styles/grid-style-20.phtml', 'label' => $helper->__('Style 20'))
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