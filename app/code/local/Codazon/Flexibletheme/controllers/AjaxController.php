<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function cmsblockAction()
    {
        if ($identifier = $this->getRequest()->getParam('block_identifier')) {
            $block = Mage::getModel('Mage_Cms_Model_Block')->load($identifier, 'identifier');
            if ($block->getId()) {
                echo Mage::helper('flexibletheme')->htmlFilter($block->getContent());
            }
        }
    }
}