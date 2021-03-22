<?php

use Mage\Core\Test\Fixture\Date;

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

class Idnovate_WhatsAppChat_Block_Whatsappchat extends Mage_Core_Block_Template
{
    public function isActive()
    {
        return Mage::getStoreConfigFlag('idnovate_whatsappchat/general/enabled');
    }

    public function getWhatsAppChat()
    {
        $showablebyschedule = $this->isShowableBySchedule(false);
        $offline_message_conf = Mage::getStoreConfig('idnovate_whatsappchat/display/offline_message');
        if ($showablebyschedule === false && $offline_message_conf == '') {
            return false;
        }
        $offline_message = '';
        if ($showablebyschedule === true && $offline_message_conf != '') {
            $offline_message = '';
        }
        if ($showablebyschedule === false && $offline_message_conf != '') {
            $offline_message = $offline_message_conf;
        }
        if (Mage::getStoreConfig('idnovate_whatsappchat/general/phone') == '' && Mage::getStoreConfig('idnovate_whatsappchat/general/text') == '') {
            return false;
        }
        $this->loadMobileDetect();
        $mobile = new Mobile_Detect_WhatsApp();
        if (Mage::getStoreConfig('idnovate_whatsappchat/display/show_on_mobile') != '1' && $mobile->isMobile()) {
            return false;
        }
        if (Mage::getStoreConfig('idnovate_whatsappchat/display/show_on_pc') != '1' && (!$mobile->isMobile() && !$mobile->isTablet())) {
            return false;
        }
        if (!$this->isShowableByPage()) {
            return false;
        }
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerGroupId = Mage::getSingleton( 'customer/session' )->getCustomerGroupId();
        } else {
            $customerGroupId = 0;
        }
        if (!in_array($customerGroupId, explode(',', Mage::getStoreConfig('idnovate_whatsappchat/filters/customer_groups')))) {
            return false;
        }
        $text = '';
        if (Mage::getStoreConfig('idnovate_whatsappchat/general/message') != '') {
            $text .= Mage::getStoreConfig('idnovate_whatsappchat/general/message');
        }
	    if (Mage::getStoreConfigFlag('idnovate_whatsappchat/general/share')) {
		    $text .= ' '.$this->getProtocolUrl().$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	    }
        $url = $this->getWhatsappUrl(Mage::getStoreConfig('idnovate_whatsappchat/general/phone'), $text, Mage::getStoreConfig('idnovate_whatsappchat/general/group'));
        $whatsappchat = array(
            'text'            => Mage::getStoreConfig('idnovate_whatsappchat/general/text'),
            'message'         => Mage::getStoreConfig('idnovate_whatsappchat/general/message'),
            'offline_message' => $offline_message,
            'type'            => Mage::getStoreConfig('idnovate_whatsappchat/display/type'),
            'position'        => Mage::getStoreConfig('idnovate_whatsappchat/display/position'),
            'mobile_phone'    => Mage::getStoreConfig('idnovate_whatsappchat/general/phone'),
            'color'           => Mage::getStoreConfig('idnovate_whatsappchat/display/color'),
            'custom_css'      => Mage::getStoreConfig('idnovate_whatsappchat/display/custom_css'),
            'custom_js'       => Mage::getStoreConfig('idnovate_whatsappchat/display/custom_js'),
            'url'             => $url,
        );
        return $whatsappchat;
    }

    public function getWhatsappUrl($phone = false, $text = false, $chat_group = '')
    {
        $this->loadMobileDetect();
        $mobile = new Mobile_Detect_WhatsApp();
        if ($mobile->isMobile() || $mobile->isTablet()) {
            if ($mobile->is('AndroidOS')) {
                if ($this->isFacebookInstagramInAppBrowser() || $this->isAndroidBrowser()) {
                    return 'intent://send/'.($phone ? '&phone='.$phone : '').'#Intent;scheme=smsto;package=com.whatsapp;action=android.intent.action.SENDTO;end';
                }
            }
            $url = 'https://api.whatsapp.com/';
        } else {
            $url = 'https://web.whatsapp.com/';
        }
        if ($chat_group != '') {
            return 'https://chat.whatsapp.com/'.$chat_group;
        }
        $iso_code = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
        return $url.'send?l='.$iso_code.($phone ? '&phone='.$phone : '').($text ? '&text='.$text : '');
    }

    public function isShowableBySchedule($whatsappchat = false)
    {
        if ($whatsappchat) {
            $schedule = json_decode($whatsappchat['schedule']);
        } else {
            $schedule = json_decode(Mage::getStoreConfig('idnovate_whatsappchat/display/schedule'));
        }
        $dayOfWeek = date('w') - 1;
        if ($dayOfWeek < 0) {
            $dayOfWeek = 6;
        }
        $date = new DateTime(date('H:i'));
        $date->setTimezone(new DateTimeZone(Mage::getStoreConfig('general/locale/timezone')));
        if (is_array($schedule)) {
            if (is_object($schedule[$dayOfWeek]) && $schedule[$dayOfWeek]->isActive === true) {
                if ($schedule[$dayOfWeek]->timeFrom <= $date->format('H:i') && $schedule[$dayOfWeek]->timeTill >= $date->format('H:i')) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function isShowableByPage($whatsappchat = false)
    {
        if (Mage::getStoreConfigFlag('idnovate_whatsappchat/filters/allpages')) {
            return true;
        }
        $request = $this->getRequest();
        $controller = $request->getControllerName();
        $module = $request->getModuleName();
        if ($whatsappchat) {
            $pages = explode(',', $whatsappchat['pages']);
        } else {
            $pages = explode(',', Mage::getStoreConfig('idnovate_whatsappchat/filters/pages'));
        }
        //Home page
        if (in_array('home', $pages) && Mage::getBlockSingleton('page/html_header')->getIsHomePage()) {
            return true;
        }
        //Category page
        if (in_array('category', $pages) && $controller == 'category') {
            return true;
        }
        //Product page
        if (in_array('product', $pages) && $controller == 'product') {
            return true;
        }
        //Contact page
        if (in_array('contacts', $pages) && $module == 'contacts') {
            return true;
        }
        //Customer page
        if(in_array('customer', $pages) && $module == 'customer') {
            return true;
        }
        //Cart page
        if(in_array('cart', $pages) && $module == 'checkout' && $controller == 'cart') {
            return true;
        }
        //Checkout page
        if(in_array('checkout', $pages) && $module == 'checkout' && $controller == 'onepage') {
            return true;
        }
        //CMS page
        if (in_array('cms', $pages) && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms' && !Mage::getBlockSingleton('page/html_header')->getIsHomePage()) {
            return true;
        }
        //Search page
        if(in_array('search', $pages) && $module == 'catalogsearch') {
            return true;
        }
        return false;
    }

    protected function isFacebookInstagramInAppBrowser()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'FBAN')) {
            return true;
        } elseif (strpos($user_agent, 'Instagram')) {
            return true;
        } else {
            return false;
        }
    }

    protected function isAndroidBrowser()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'SamsungBrowser')) {
            return true;
        } elseif (strpos($user_agent, 'MiuiBrowser')) {
            return true;
        } elseif (strpos($user_agent, 'UCBrowser')) {
            return true;
        } else {
            return false;
        }
    }

	public function getProtocolUrl()
	{
		if (isset($_SERVER['HTTPS'])) {
			$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		} else {
			$protocol = 'http';
		}

		return $protocol."://";
	}

    public function loadMobileDetect()
    {
        include_once Mage::getModuleDir('', 'Idnovate_WhatsAppChat').'/Model/Mobile_Detect_WhatsApp.php';
    }
}