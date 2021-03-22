<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Theme extends Mage_Core_Model_Abstract
{
    const DEFAULT_THEME_CODE = 'codazon_unlimited/default';
    const LIST_REGISTER_KEY = 'codazon_themes';
    protected $_themeList;
    protected $_defaultTheme;
    protected $_mediaUrl;
    
    public function __construct()
    {
        parent::__construct();
        $this->_mediaUrl = Mage::getBaseUrl('media');
        $this->_previewDirUrl = $this->_mediaUrl . 'codazon/flexibletheme/theme/preview/';
    }
    
    protected function _construct()
    {
        $this->_init("flexibletheme/theme");
    }
    
    public function getThemeList()
    {
        if ($this->_themeList === null)
        {
            $this->_themeList = Mage::getModel(self::class)->getCollection()->addFieldToFilter('is_active', 1);
        }
        return $this->_themeList;
    }
    
    public function getPreviewImageUrl()
    {
        if (!$this->getData('preview_image_url')) {
            $preview = $this->getPreviewImage();
            $preview = $this->_previewDirUrl . $preview;
            $this->setData('preview_image_url', $preview);
        }
        return $this->getData('preview_image_url');
    }
    
    
}