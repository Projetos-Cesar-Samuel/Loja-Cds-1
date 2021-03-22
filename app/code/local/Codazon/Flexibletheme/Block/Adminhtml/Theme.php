<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Theme extends Mage_Adminhtml_Block_Template
{	
    protected $_themeModel;
    
    protected $_helper;
    
    protected $_themeList;
    
    protected $_activeTheme;
    
    protected $_store;
    
    protected $_scope;
    
	public function __construct()
	{
		parent::__construct();
        $this->_themeModel = Mage::getModel('flexibletheme/theme');
        $this->_helper = Mage::helper('flexibletheme');

	}
    
    public function getThemeList()
    {
        if ($this->_themeList === null) {
            if (!Mage::registry(Codazon_Flexibletheme_Model_Theme::LIST_REGISTER_KEY)) {
                Mage::register(Codazon_Flexibletheme_Model_Theme::LIST_REGISTER_KEY, $this->_themeModel->getThemeList());
            }
            $this->_themeList = Mage::registry(Codazon_Flexibletheme_Model_Theme::LIST_REGISTER_KEY);
        }
        return $this->_themeList;
    }
    
    public function findActiveThemeInThemeList($themeList)
    {
        foreach ($themeList as $theme) {
            if ($this->isActiveTheme($theme)) {
                return $theme;
            }
        }
        return false;
    }
    
    public function getActiveTheme()
    {
        if ($this->_activeTheme === null) {
            if ($store = $this->getRequest()->getParam('store')) {
                $scope = Mage::app()->getStore($store);
                $package = $scope->getConfig('design/package/name') ?  : 'base';
                $template = $scope->getConfig('design/theme/template')? : 'default';
            } elseif($website = $this->getRequest()->getParam('website')) {
                $scope = Mage::app()->getWebsite($website);
                $package = $scope->getConfig('design/package/name') ? : 'base';
                $template = $scope->getConfig('design/theme/template')? : 'default';
            } else {
                $package = 'base';
                $template = 'default';
                $configItems = Mage::getModel('core/config_data')->getCollection()
                    ->addFieldToFilter('scope', 'default')
                    ->addFieldToFilter('path', array('in' => array(
                        'design/package/name',
                        'design/theme/template'
                    )));
                $configItems->getSelect()->group('path');
                if ($configItems->count()) {
                    foreach ($configItems as $item) {
                        $path = $item->getPath();
                        if ($path == 'design/package/name') {
                            $package = $item->getValue() ? : 'base';
                        }
                        if ($path == 'design/theme/template') {
                            $template = $item->getValue() ? : 'default';
                        }
                    }
                }
            }
            $this->_activeTheme = array(
                'package'   => $package,
                'template'  => $template
            );
        }
        return $this->_activeTheme;
    }
    
    public function isActiveTheme($theme)
    {
        $activeTheme = $this->getActiveTheme();
        return ($theme->getData('theme_package') == $activeTheme['package']) && ($theme->getData('theme_template') == $activeTheme['template']); 
    }
    
    public function getConfigUrl($theme)
    {
        if ($theme->getId()) {
            return $this->getUrl('adminhtml/flexibletheme_config/edit', array('_current' => true, 'theme_id' => $theme->getId()));
        }
        return '';
    }
    
    public function getActivateThemeUrl($theme)
    {
        if ($theme->getId()) {
            return $this->getUrl('adminhtml/flexibletheme_config/activatetheme', array('_current' => true, 'theme_id' => $theme->getId()));
        }
        return '';
    }
}