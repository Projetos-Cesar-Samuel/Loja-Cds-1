<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Moreviewstyles
{   
    protected $_excludeIds = [];
    
    public function toOptionArray()
    {
        $helper = Mage::helper('flexibletheme');
        $options = array(
            array('value' => 'horizontal',  'label' => $helper->__('Horizontal')),
            array('value' => 'vertical',    'label' => $helper->__('Vertical')),
        );
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}