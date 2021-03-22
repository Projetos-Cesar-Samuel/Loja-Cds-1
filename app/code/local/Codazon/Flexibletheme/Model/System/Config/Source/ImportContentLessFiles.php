<?php
/**
 *
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_ImportContentLessFiles
{
    protected $_options;
    
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = $this->_getOptions();
        }
        return $this->_options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
    
    protected function _getOptions() {
        $model = Mage::getModel('flexibletheme/content');
        $fileList = $model->getFlexibleFileList();
        $options = array();
        foreach ($fileList as $file) {
            $options[] = array(
                'value' => $file,
                'label' => $file
            ); 
        }
        return $options;
    }
}