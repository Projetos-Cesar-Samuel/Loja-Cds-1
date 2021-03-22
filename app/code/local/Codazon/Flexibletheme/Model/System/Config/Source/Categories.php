<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Categories
{ 
    public function toOptionArray()
    {
        $categoryTree = Mage::getBlockSingleton('flexibletheme/widget_categorytree');
        return $categoryTree->getOptions();
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}