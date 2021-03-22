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

class Idnovate_WhatsAppChat_Block_Widget extends Mage_Core_Block_Abstract implements Mage_Widget_Block_Interface
{
	protected function _toHtml()
	{
		$whatsappchat = new Idnovate_WhatsAppChat_Block_Whatsappchat();
		$showablebyschedule = $whatsappchat->isShowableBySchedule(false);
		$offline_message_conf = Mage::getStoreConfig('idnovate_whatsappchat/display/offline_message');
		if ($showablebyschedule === false && $offline_message_conf == '') {
			return '';
		}
		$offline_message = '';
		if ($showablebyschedule === true && $offline_message_conf != '') {
			$offline_message = '';
		}
		if ($showablebyschedule === false && $offline_message_conf != '') {
			$offline_message = $offline_message_conf;
		}
		if ($this->getData('phone') == '' && $this->getData('text') == '') {
			return '';
		}
		$message = '';
		if ($this->getData('message') != '') {
			$message .= $this->getData('message');
		}
		if ($this->getData('share') == '1') {
			$message .= ' '.$whatsappchat->getProtocolUrl().$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
		$url = $whatsappchat->getWhatsappUrl($this->getData('phone'), $message, $this->getData('group'));
		if ($this->getData('color') == '#' || $this->getData('color') == '') {
			$color = '#'.Mage::getStoreConfig('idnovate_whatsappchat/display/color');
		} else {
			$color = $this->getData('color');
		}
		$html = '';
		if ($offline_message == '') {
			$html .= '<a target="_blank" href="'.$url.'">';
		}
		$html .= '<div class="whatsapp whatsapp-widget'.($offline_message != '' ? ' whatsapp-offline' : '').'"';
		$html .= '>';
		$html .= '<span '.($color != '' ? 'style="background-color: '.$color.'"' : '' ).($offline_message != '' ? 'class="whatsapp-offline"' : '').'>';
		$html .= '<i class="whatsapp-icon" '.($this->getData('text') != '' ? 'style="margin-right:5px;padding-right:0px!important;"' : '').'></i>';
		if ($offline_message != '') {
			$html .= $offline_message;
		} else {
			$html .= $this->getData('text');
		}
		if ($offline_message == '') {
			$html .= '</span></div></a>';
		}
		return $html;
	}
}
