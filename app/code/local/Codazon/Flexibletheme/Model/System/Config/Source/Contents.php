<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Contents
{   
    protected $_excludeIds = [];
    
    public function setExcludeIds(array $excludeIds)
    {
        $this->_excludeIds = $excludeIds;
        return $this;
    }
    
    public function toOptionArray()
    {
        $collection = Mage::getModel('flexibletheme/content')->getCollection()
            ->addAttributeToSelect(['title']);
        $options = array(
            array('value' => '', 'label' => __('-- Select Main Content --'))
        );
        if ($collection->count()) {
            foreach ($collection as $object) {
                if (in_array($object->getId(), $this->_excludeIds)) {
                    continue;
                }
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