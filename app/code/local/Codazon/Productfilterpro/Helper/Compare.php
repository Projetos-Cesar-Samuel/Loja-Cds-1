<?php
/**
 * Copyright © 2018 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Productfilterpro_Helper_Compare extends Mage_Catalog_Helper_Product_Compare
{
	protected function _getUrlParams($product)
    {
		if($product->getBlockUrl()){
			$url = 	$product->getBlockUrl();
		}else{
			$url = null;	
		}
        return array(
            'product' => $product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl($url),
            Mage_Core_Model_Url::FORM_KEY => isset($this->_coreSession) ? $this->_coreSession->getFormKey() : Mage::getSingleton('core/session')->getFormKey()
        );
    }
}
