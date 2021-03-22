<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Footer extends Codazon_Flexibletheme_Model_Abstract
{
	const ENTITY = 'flexibletheme_footer';
    
	protected $_projectPath = 'codazon/flexibletheme/footer';
	
    protected $_eventPrefix = 'footer';
	
	protected $_eventObject = 'footer';
    
    protected $_fieldFileName = 'footer.xml';
    
    protected $_variableFile = 'variables.less.css';
    
    protected $_mainFileName = 'footer-styles.less.css';
    
    protected $_cssFileName = 'footer-styles.css';
	
	protected function _construct()
    {
        parent::_construct();
        $this->_init('flexibletheme/footer');
    }
    
    protected function _getMainHtml()
    {
        return $this->getData('content');
    }

}
