<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Backend_Productviewstyle extends Codazon_Flexibletheme_Model_Themeconfig_Data
{ 
    protected function _afterSave()
    {
        $template = $this->getValue();
		$type = explode('/', $template);
		$type = $type[count($type) - 1];
		$type = str_replace(array('view-style-', '.phtml'), array('', ''), $type);
		$flexibleLess = 'product-view-style-' . $type . '.less.css';
		

		$helper = Mage::helper('flexibletheme');
		$mainContent = $helper->getMainContent();
		
		try {
			$customField = $mainContent->getData('custom_fields');
			$customField = $customField ? json_decode($customField, true) : [];

			
			$flexibleFileList = $mainContent->getFlexibleFileList();
			if (in_array($flexibleLess, $flexibleFileList)) {
				$customField['product_view_less'] = $flexibleLess;
			}		
			
			$mainContent->setData('custom_fields', json_encode($customField));
			$mainContent->save();
			$mainContent->updateWorkspace(false);
		} catch(\Exceptions $e) {
			
		}
		
        return parent::_afterSave();
    }
}