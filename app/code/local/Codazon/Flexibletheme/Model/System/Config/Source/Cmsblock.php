<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Cmsblock
{   
    protected $_excludeIds = [];
    
    public function setExcludeIds(array $excludeIds)
    {
        $this->_excludeIds = $excludeIds;
        return $this;
    }
    
    public function toOptionArray()
    {
        $collection = Mage::getModel('cms/block')->getCollection();
        $options = array(
            array('value' => '', 'label' => __('-- Select Block --'))
        );
        if ($collection->count()) {
            foreach ($collection as $object) {
                $options[] = array('value' => $object->getIdentifier(), 'label' => $object->getTitle());
            }
        }
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}