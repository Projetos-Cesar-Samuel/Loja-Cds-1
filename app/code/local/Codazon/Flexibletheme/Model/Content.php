<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Content extends Codazon_Flexibletheme_Model_Abstract
{
	const ENTITY = 'flexibletheme_content';
    
	protected $_projectPath = 'codazon/flexibletheme/main';
	
    protected $_eventPrefix = 'content';
	
	protected $_eventObject = 'content';
    
    protected $_fieldFileName = 'main_content.xml';
    
    protected $_variableFile = 'variables.less.css';
    
    protected $_mainFileName = 'main-styles.less.css';
    
    protected $_cssFileName = 'main-styles.css';
    
    protected $_flexibleLessDir = 'codazon/flexibletheme/main/general/flexible';
	
	protected function _construct()
    {
        parent::_construct();
        $this->_init('flexibletheme/content');
    }

}
