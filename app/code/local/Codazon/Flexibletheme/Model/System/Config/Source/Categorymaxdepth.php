<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Categorymaxdepth
{ 
    public function toOptionArray()
    {
        $options = array(
            array('value' => '', 'label' => __('All Levels')),
        );
        for ($i = 1; $i <= 3; $i++) {
            $options[] = array('value' => $i, 'label' => $i);
        }
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}