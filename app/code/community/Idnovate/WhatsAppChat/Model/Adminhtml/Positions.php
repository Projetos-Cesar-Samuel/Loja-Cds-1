<?php
/**
 * idnovate.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Idnovate
 * @package    Idnovate_WhatsAppChat
 * @version    Release: 1.0.0
 * @author     idnovate.com (info@idnovate.com)
 * @copyright  Copyright (c) 2017 idnovate.com (http://www.idnovate.com)
 */

class Idnovate_WhatsAppChat_Model_Adminhtml_Positions
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'top-left', 'label'=>Mage::helper('whatsappchat')->__('Top left')),
            array('value' => 'top-center', 'label'=>Mage::helper('whatsappchat')->__('Top center')),
            array('value' => 'top-right', 'label'=>Mage::helper('whatsappchat')->__('Top right')),
	        array('value' => 'left', 'label'=>Mage::helper('whatsappchat')->__('Mid left')),
	        array('value' => 'center', 'label'=>Mage::helper('whatsappchat')->__('Mid center')),
	        array('value' => 'right', 'label'=>Mage::helper('whatsappchat')->__('Mid right')),
	        array('value' => 'bottom-left', 'label'=>Mage::helper('whatsappchat')->__('Bottom left')),
	        array('value' => 'bottom-center', 'label'=>Mage::helper('whatsappchat')->__('Bottom center')),
	        array('value' => 'bottom-right', 'label'=>Mage::helper('whatsappchat')->__('Bottom right')),
        );
    }
}
