<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Header extends Codazon_Flexibletheme_Model_Abstract
{
	const ENTITY = 'flexibletheme_header';
    
	protected $_projectPath = 'codazon/flexibletheme/header';
	
    protected $_eventPrefix = 'header';
	
	protected $_eventObject = 'header';
    
    protected $_fieldFileName = 'header.xml';
    
    protected $_variableFile = 'variables.less.css';
    
    protected $_mainFileName = 'header-styles.less.css';
    
    protected $_cssFileName = 'header-styles.css';
	
	protected function _construct()
    {
        parent::_construct();
        $this->_init('flexibletheme/header');
    }

    protected function _getMainHtml()
    {
        return $this->getData('content') . $this->getData('content_1');
    }
}
