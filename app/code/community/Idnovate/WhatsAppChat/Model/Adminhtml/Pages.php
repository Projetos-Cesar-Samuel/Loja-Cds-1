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

class Idnovate_WhatsAppChat_Model_Adminhtml_Pages
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'home', 'label'=>Mage::helper('whatsappchat')->__('Home')),
            array('value' => 'category', 'label'=>Mage::helper('whatsappchat')->__('Category')),
            array('value' => 'product', 'label'=>Mage::helper('whatsappchat')->__('Product')),
	        array('value' => 'cart', 'label'=>Mage::helper('whatsappchat')->__('Cart')),
	        array('value' => 'checkout', 'label'=>Mage::helper('whatsappchat')->__('Checkout')),
	        array('value' => 'contacts', 'label'=>Mage::helper('whatsappchat')->__('Contact')),
	        array('value' => 'customer', 'label'=>Mage::helper('whatsappchat')->__('Customer account')),
	        array('value' => 'cms', 'label'=>Mage::helper('whatsappchat')->__('CMS')),
	        array('value' => 'search', 'label'=>Mage::helper('whatsappchat')->__('Search')),
        );
    }
}
