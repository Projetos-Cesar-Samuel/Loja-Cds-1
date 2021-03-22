<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Productfilterpro_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function loadproductsAction()
    {
        $data = $this->getRequest()->getParams();
		$data['use_ajax'] = 0;
		$data['after_load'] = 1;
		$block = Mage::app()->getLayout()->createBlock('productfilterpro/widget_productlist', 'ajax_products_block', $data);
		$result['html'] = $block->toHtml();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(json_encode($result));
    }
    
    public function infiniteloadAction()
    {
        $data = $this->getRequest()->getParams();
		$data['use_ajax'] = 0;
		$data['is_next_page'] = 1;
        $data['is_next_page'] = 1;
		$block = Mage::app()->getLayout()->createBlock('productfilterpro/widget_productlist', 'ajax_products_block', $data);
		$result['html'] = $block->toHtml();
        $result['last_page'] = $block->createCollection()->getLastPageNumber();
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(json_encode($result));
    }
    
}


