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

class Idnovate_WhatsAppChat_Model_Adminhtml_Types
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'floating_ball', 'label'=>Mage::helper('whatsappchat')->__('Floating ball')),
            array('value' => 'badge', 'label'=>Mage::helper('whatsappchat')->__('Floating button')),
            array('value' => 'sticky', 'label'=>Mage::helper('whatsappchat')->__('Sticky')),
        );
    }
}
