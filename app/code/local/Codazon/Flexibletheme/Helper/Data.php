<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_themeConfig;
    
    protected $_currentThemeId;
    
    protected $_configCache;
    
    protected $_scopeCode;
    
    protected $_scopeId;
    
    protected $_design;
    
    protected $_themeHeader;
    
    protected $_themeFooter;
    
    protected $_themeMainContent;
    
    protected $_lessFilesSet;
    
    protected $_isDevMode;
    
    protected $_isRTL;
    
    protected $_settingArray;
    
    protected $_blockFilter;
    
    protected $_displayOnList;
    
    protected $_labelBlock;
    
    protected $_showLabel;
    
    const HEADER_STYLE = 'flexibletheme/header/header';
    const HEADER_LEFT_MENU_STYLE = 'flexibletheme/header/left_menu_style';
    const HEADER_WISHLIST_STYLE = 'flexibletheme/header/wishlist_style';
    const HEADER_MINI_CART_STYLE = 'flexibletheme/header/mini_cart_style';
    const HEADER_ACCOUNT_PANEL_STYLE = 'flexibletheme/header/account_panel_style';
    const ENABLE_DEV_MODE = 'flexibletheme/env/enable_dev_mode';
    const ENABLE_RTL = 'flexibletheme/general/enable_rtl';
    const FOOTER_STYLE = 'flexibletheme/footer/footer';
    const MAIN_CONTENT_STYLE = 'flexibletheme/main_content/main_content';
    const PRODUCT_VIEW_STYLE = 'pages/product_view/layout';
    
    public function getDesign()
    {
        if ($this->_design === null) {
            $this->_design = Mage::getSingleton('core/design_package');
        }
        return $this->_design;
    }
    
    public function getHeaderStyle()
    {
        return $this->getConfig(self::HEADER_STYLE);
    }
    
    public function getFooterStyle()
    {
        return $this->getConfig(self::FOOTER_STYLE);
    }
    
    public function getMainContentStyle()
    {
        return $this->getConfig(self::MAIN_CONTENT_STYLE);
    }
    
    public function getHeader()
    {
        if ($this->_themeHeader === null) {
            if (!Mage::registry('flexibletheme_header')) {
                $object = Mage::getModel('flexibletheme/header')->getCollection()
                    ->setStoreId($this->getStoreId())
                    ->addFieldToFilter('identifier', $this->getHeaderStyle())
                    ->addFieldToFilter('is_active', 1)
                    ->addAttributeToSelect(['content', 'content_1', 'layout_xml'])
                    ->getFirstItem();
                Mage::register('flexibletheme_header', $object);
            }
            $this->_themeHeader = Mage::registry('flexibletheme_header');
        }
        return $this->_themeHeader;
    }
    
    public function getFooter()
    {
        if ($this->_themeFooter === null) {
            if (!Mage::registry('flexibletheme_footer')) {
                $object = Mage::getModel('flexibletheme/footer')->getCollection()
                    ->setStoreId($this->getStoreId())
                    ->addFieldToFilter('identifier', $this->getFooterStyle())
                    ->addFieldToFilter('is_active', 1)
                    ->addAttributeToSelect(['content', 'layout_xml'])
                    ->getFirstItem();
                Mage::register('flexibletheme_footer', $object);
            }
            $this->_themeFooter = Mage::registry('flexibletheme_footer');
        }
        return $this->_themeFooter;
    }
    
     public function isDeveloperMode() {
        if($this->_isDevMode === null) {
            $this->_isDevMode = (bool)Mage::getStoreConfig('codazon_developer/env/enable_dev_mode');
        }
        return $this->_isDevMode;
    }
    
    public function addFontScripts($head)
    {
        $mainContent = $this->getMainContent();
        $customeFields = json_decode($mainContent->getCustomFields(), true);
        if (isset($customeFields['google_web_fonts']) && $customeFields['google_web_fonts']) {
            $fontUrl = '//fonts.googleapis.com/css?family=';
            $separate = '';
            foreach($customeFields['google_web_fonts'] as $font) {
                $font = str_replace(' ', '+', trim($font));
                $fontUrl .= $separate . $font;
                $fontUrl .= ':' . $customeFields['google_font_weights'];
                $separate = '|';
            }
            $fontUrl .= '&subset=' . $customeFields['google_font_subset'];
            $head->addLinkRel('stylesheet', $fontUrl);
        }
    }
    
    public function getMainContent()
    {
        if ($this->_themeMainContent === null) {
            if (!Mage::registry('flexibletheme_content')) {
                $object = Mage::getModel('flexibletheme/content')->getCollection()
                    ->setStoreId($this->getStoreId())
                    ->addFieldToFilter('identifier', $this->getMainContentStyle())
                    ->addFieldToFilter('is_active', 1)
                    ->addAttributeToSelect(['content', 'layout_xml'])
                    ->getFirstItem();
                Mage::register('flexibletheme_content', $object);
            }
            $this->_themeMainContent = Mage::registry('flexibletheme_content');
        }
        return $this->_themeMainContent;
    }
    
    public function getLessFilesSet() {
        if (!$this->_lessFilesSet) {
            $mediaUrl = Mage::getBaseUrl('media');
            if ($this->isDeveloperMode()) {
                $header = $this->getHeader();
                $lessFile = $mediaUrl . $header->getMainLessFileRelativePath(); 
                $this->_lessFilesSet[] = $lessFile;
                
                $footer = $this->getFooter();
                $lessFile = $mediaUrl . $footer->getMainLessFileRelativePath();
                $this->_lessFilesSet[] = $lessFile;
                
                $mainContent = $this->getMainContent();
                $lessFile = $mediaUrl . $mainContent->getMainLessFileRelativePath();
                $this->_lessFilesSet[] = $lessFile;
            }
        }
        
        return $this->_lessFilesSet;
    }
    
    public function isRTL()
    {
        if ($this->_isRTL === null) {
            $this->_isRTL = $this->getConfig(self::ENABLE_RTL);
        }
        return $this->_isRTL;
    }
    
    public function getSettingArray()
    {
        if ($this->_settingArray === null) {
            $this->_settingArray = array(
                'now'                   => date("Y-m-d H:i:s"),
                'rtl'                   => (bool)$this->isRTL(),
                'enableStikyMenu'       => (bool)$this->getConfig('flexibletheme/header/enable_sticky_menu'),
                'enableAjaxCart'        => (bool)Mage::getStoreConfig('cdz_ajax_block/ajax_cart/enable'),
                'enableAjaxWishlist'    => (bool)Mage::getStoreConfig('cdz_ajax_block/ajax_wishlist/enable'),
                'enableAjaxCompare'     => (bool)Mage::getStoreConfig('cdz_ajax_block/ajax_compare/enable'),
                'wishlistRemoveConfirmMsg'  => $this->__('Are you sure you would like to remove this item from the wishlist?'),
                'compareRemoveConfirmMsg'   => $this->__('Are you sure you would like to remove this item from the compare products?'),
                'compareClearConfirmMsg'    => $this->__('Are you sure you would like to remove all products from your comparison?'),
            );
        }
        return $this->_settingArray;
    }
    
    public function addScripts($layout)
    {
        $head = $layout->getBlock('head');
        if (!$head) {
            return false;
        }
        
        $root = $layout->getBlock('root');
        $this->addFontScripts($head);
        if ($this->isRTL()) {
            $root->addBodyClass('rtl-layout');
        }
        
        if ($bodyClass = $this->getConfig('flexibletheme/general/custom_body_class')) {
            $root->addBodyClass($bodyClass);
        }
        
        if (!$this->isDeveloperMode()) {
            $mediaUrl = Mage::getBaseUrl('media');
            if ($this->getHeader()->cssFileExisted()) {
                $head->addItem('link_rel', $mediaUrl . $this->getHeader()->getMainCssFileRelativePath() . '?version=' . $this->getHeader()->getVersion(), 'id="cdz-header-css" rel="stylesheet" type="text/css" media="all" ');
            }
            if ($this->getFooter()->cssFileExisted()) {
                $head->addItem('link_rel', $mediaUrl . $this->getFooter()->getMainCssFileRelativePath() . '?version=' . $this->getFooter()->getVersion(), 'id="cdz-footer-css" rel="stylesheet" type="text/css" media="all" ');
            }
            if ($this->getMainContent()->cssFileExisted()) {
                $head->addItem('link_rel', $mediaUrl . $this->getMainContent()->getMainCssFileRelativePath() . '?version=' . $this->getMainContent()->getVersion(), 'id="cdz-maincontent-css" rel="stylesheet" type="text/css" media="all" ');
            }
        }
    }
    
    public function bootstrapCssFile()
    {
        if($this->isRTL()){
			return 'codazon/bootstrap/css/bootstrap-rtl.min.css';
		}else{
			return 'codazon/bootstrap/css/bootstrap.min.css';
		}
    }
    
    public function getCurrentThemeId()
    {
        if ($this->_currentThemeId === null) {
            if (Mage::app()->getStore()->isAdmin()) {
                $this->_currentThemeId = Mage::app()->getRequest()->getParam('theme_id', 0);
            } else {
                $storeId = (int)$this->getStoreId();
                $design = $this->getDesign()->setStore($storeId);
                $package = $design->getPackageName();
                $template = $design->getTheme('template');
                $collection = Mage::getModel('flexibletheme/theme')->getCollection()
                    ->addFieldToFilter('theme_package', $package)
                    ->addFieldToFilter('theme_template', $template);
                if ($collection->count()) {
                    $this->_currentThemeId = $collection->getFirstItem()->getId();
                } else {
                    $this->_currentThemeId = 0;
                }
            }
        }
        return $this->_currentThemeId;
    }
    
    public function getThemeConfig()
    {
        if ($this->_themeConfig === null) {
            if (!Mage::registry("flexibletheme_config")) {
                $themeId = $this->getCurrentThemeId();
                $config = new Codazon_Flexibletheme_Model_Themeconfig();
                $config->setThemeId($themeId);
                $config->init();
                Mage::register("flexibletheme_config", $config);
            }
            $this->_themeConfig = Mage::registry("flexibletheme_config");
        }
        
        return $this->_themeConfig;
    }
    
    public function getCode()
    {
        if ($this->_scopeCode === null) {
            $this->_scopeCode = Mage::app()->getStore()->getCode();
        }
        return $this->_scopeCode;
    }
    
    public function getStoreId()
    {
        if ($this->_scopeId === null) {
            $this->_scopeId = Mage::app()->getStore()->getId();
        }
        return $this->_scopeId;
    }
    
    public function getConfig($path)
    {
        if (isset($this->_configCache[$path])) {
            return $this->_configCache[$path];
        }

        $config = $this->getThemeConfig();

        $fullPath = 'stores/' . $this->getCode() . '/' . $path;
        $data = $config->getNode($fullPath);
        
        if (!$data) {
            $data = $config->getNode('default/' . $path);
        }
        if (!$data) {
            return null;
        }
        return $this->_processConfigValue($fullPath, $path, $data);
    }
    
    protected function _processConfigValue($fullPath, $path, $node)
    {
        
        if (isset($this->_configCache[$path])) {
            return $this->_configCache[$path];
        }

        if ($node->hasChildren()) {
            $aValue = array();
            foreach ($node->children() as $k => $v) {
                $aValue[$k] = $this->_processConfigValue($fullPath . '/' . $k, $path . '/' . $k, $v);
            }
            $this->_configCache[$path] = $aValue;
            return $aValue;
        }

        $sValue = (string) $node;
        if (!empty($node['backend_model']) && !empty($sValue)) {
            $backend = Mage::getModel((string) $node['backend_model']);
            $backend->setPath($path)->setValue($sValue)->afterLoad();
            $sValue = $backend->getValue();
        }

        if (is_string($sValue) && strpos($sValue, '{{') !== false) {
            if (strpos($sValue, '{{unsecure_base_url}}') !== false) {
                $unsecureBaseUrl = $this->getConfig(self::XML_PATH_UNSECURE_BASE_URL);
                $sValue = str_replace('{{unsecure_base_url}}', $unsecureBaseUrl, $sValue);
            } elseif (strpos($sValue, '{{secure_base_url}}') !== false) {
                $secureBaseUrl = $this->getConfig(self::XML_PATH_SECURE_BASE_URL);
                $sValue = str_replace('{{secure_base_url}}', $secureBaseUrl, $sValue);
            } elseif (strpos($sValue, '{{base_url}}') !== false) {
                $sValue = Mage::getConfig()->substDistroServerVars($sValue);
            }
        }

        $this->_configCache[$path] = $sValue;

        return $sValue;
    }
    
    public function getBlockFilter()
    {
        if ($this->_blockFilter === null) {
            $this->_blockFilter = Mage::helper('cms')->getBlockTemplateProcessor();
        }
        return $this->_blockFilter;
    }
    
    public function htmlFilter($content)
    {
        return $this->getBlockFilter()->filter($content);
    }
    
    public function getAccountPanelStyle() {
        return $this->getConfig(self::HEADER_ACCOUNT_PANEL_STYLE);
    }
    
    public function getHeaderWishlistStyle() {
        return $this->getConfig(self::HEADER_WISHLIST_STYLE);
    }
    
    public function getMiniCartStyle() {
        return $this->getConfig(self::HEADER_MINI_CART_STYLE);
    }
    
    public function getGeocodeByAddress($address)
    {
		return json_decode(file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false'));
	}
    
	public function getGoogleMapJavascriptUrl()
    {
        $key = $this->getConfig('pages/contact/google_api_key');
        if ($key) {
            return "//maps.googleapis.com/maps/api/js?v=weekly&key={$key}";
        } else {
            return 'https://maps.googleapis.com/maps/api/js?key';
        }
	}
    
    public function getMapAdditionalMarkers()
    {
        $markers = $this->getConfig('pages/contact/map_additional_markers');
        if ($markers) {
            $result = [];
            $markers = explode("\n", $markers);
            foreach ($markers as $marker) {
                $marker = explode('|', $marker);
                if (isset($marker[1]) && isset($marker[2])) {
                    $result[] = [
                        'title'     => !empty($marker[0]) ? $marker[0] : '',
                        'latitude'  => !empty($marker[1]) ? (float)$marker[1] : '',
                        'longitude'  => !empty($marker[2]) ? (float)$marker[2] : '',
                        'address'  => !empty($marker[2]) ? $marker[3] : '',
                    ];
                }
            }
            return $result;
        }
        return [];
    }
    
    public function getCategoryItemsPerRowArray($layout = '1')
	{
		if (empty($this->_itemsPerRow)) {
			$this->_itemsPerRow = [];
			$breakPoints = [1900, 1600, 1420, 1280, 980, 768, 480, 320, 0];
			foreach ($breakPoints as $point) {
				$this->_itemsPerRow[$point] = (float)($this->getConfig("category_view/items_per_row_{$layout}/items_" . $point) ? : 4);
			}
		}
		return $this->_itemsPerRow;
	}
	
	public function getColumnStyle($gridWrap, $gridItem, $generalItem, $layout = '1')
	{
		$itemsPerRows = $this->getCategoryItemsPerRowArray($layout);
		$style = '';
		$prevPoint = 1900;
		$mobileRightMargin = (float)($this->getConfig("category_view/design/margin_right_mobile") ? : 0);
		$mobileBottomMargin = (float)($this->getConfig("category_view/design/margin_bottom_mobile") ? : 0);
		$desktopRightpMargin = (float)($this->getConfig("category_view/design/margin_right_desktop") ? : 0);
		$desktopBottomMargin = (float)($this->getConfig("category_view/design/margin_bottom_desktop") ? : 0);
		$style .= "{$gridWrap}{margin-left:0}";
		$style .= "{$gridItem}{margin-left:0}";
		foreach ($itemsPerRows as $point => $columns) {
			$marginRight = ($point < 768) ? $mobileRightMargin : $desktopRightpMargin;
			$marginBottom = ($point < 768) ? $mobileBottomMargin : $desktopBottomMargin;
			
			if ($point > 0 && $point < 1900) {
				$style .= "@media (min-width: {$point}px) and (max-width: {$prevPoint}px){";
			} else {
				if ($point == 0) {
					$style .= "@media (max-width:{$prevPoint}px){";
				} else {
					$style .= "@media (min-width:{$point}px){";
				}
			}
			$prevPoint = $point - 1;
			
			$width = 100/$columns;
			$style .= "{$gridItem}{width:calc({$width}% - {$marginRight}px)}";
			$style .= "}";
		}
		
		$style .= "@media(min-width: 768px){";
		$style .= "{$gridWrap}{margin-right:-{$desktopRightpMargin}px}";
		$style .= "{$generalItem}{margin-bottom:{$desktopBottomMargin}px}";
		$style .= "{$gridItem}{margin-right:{$desktopRightpMargin}px}";
		$style .= "}";
		$style .= "@media(max-width: 767px){";
		$style .= "{$gridWrap}{margin-right:-{$mobileRightMargin}px}";
		$style .= "{$generalItem}{margin-bottom:{$mobileBottomMargin}px}";
		$style .= "{$gridItem}{margin-right:{$mobileRightMargin}px}";
		$style .= "}";
		
		return $style;
	}
    
    public function getBlockPageLayout($block)
	{
		$pageLayout = 'page/2columns-left.phtml';
        $root = $block->getLayout()->getBlock('root');
        if ($root) {
            $pageLayout = $root->getTemplate();
        }
		switch($pageLayout) {
			case 'page/1column.phtml':
				$layout = 1; break;
			case 'page/3columns.phtml':
				$layout = 3; break;
			case 'page/2columns-left.phtml':
			case 'page/2columns-right.phtml':
			default:
				$layout = 2; break;
		}
		return $layout;
	}
    
    public function getDisplayOnListArray()
	{
		if ($this->_displayOnList === null) {
			$this->_displayOnList = explode(',', $this->getConfig('category_view/design/show'));
		}
		return $this->_displayOnList;
	}
    
    public function isDisplayOnList($attribute)
	{
		return in_array($attribute, $this->getDisplayOnListArray());
	}
    
    public function subString($str, $strLenght)
	{
		$str = strip_tags($str);
        if(strlen($str) > $strLenght) {
            $strCutTitle = substr($str, 0, $strLenght);
            $str = substr($strCutTitle, 0, strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
	}
    
    protected function _getLabelBlock($template)
    {
		if ($this->_labelBlock === null) {
			$this->_labelBlock = Mage::app()->getLayout()->createBlock('core/template')->setTemplate($template);
		}
		return $this->_labelBlock;
	}
    
	protected function _showLabel()
    {
		if ($this->_showLabel === null) {
			$this->_showLabel = (bool)$this->getConfig('category_view/design/show_label');
		}
		return $this->_showLabel;
	}
    
	public function showLabel($product, $template = 'codazon_flexibletheme/product/label.phtml')
    {
		if ($this->_showLabel()) {
			$labelBlock = $this->_getLabelBlock($template)->setProduct($product);
			$labels = array();
			if($this->isNewProduct($product)){
				$labels[] = array('label' => $this->__('New'), 'html_class' => 'new');
			}
			if($this->isSaleProduct($product)){
				$html = $this->__('Sale');
				$labels[] = array('label' => $html, 'html_class' => 'sale');
			}
			$labelBlock->setLabels($labels);
			echo $labelBlock->toHtml();
		}
	}
    
    public function isSaleProduct(Mage_Catalog_Model_Product $product)
    {
		return ($product->getFinalPrice() < $product->getPrice());
	}
    
	public function isNewProduct(Mage_Catalog_Model_Product $product)
	{
		$newsFromDate = $product->getNewsFromDate();
		$newsToDate   = $product->getNewsToDate();
		if (!$newsFromDate && !$newsToDate) {
			return false;
		}
		return Mage::app()->getLocale()
				->isStoreDateInInterval($product->getStoreId(), $newsFromDate, $newsToDate);
	}
    
    public function getLabelHelper()
    {
        return $this;
    }
}