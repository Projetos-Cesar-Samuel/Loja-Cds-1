<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Menustyles
{   
    protected $_excludeIds = [];
    
    public function setExcludeIds(array $excludeIds)
    {
        $this->_excludeIds = $excludeIds;
        return $this;
    }
    
    public function toOptionArray()
    {
        $helper = Mage::helper('flexibletheme');
        $options = array(
            array('value' => 'dropdown', 'label' => $helper->__('Dropdown')),
            array('value' => 'popup',    'label' => $helper->__('Popup')),
            array('value' => 'sidebar',  'label' => $helper->__('Sidebar'))
        );
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}