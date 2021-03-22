<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Flexibletheme extends Codazon_Flexibletheme_Model_Abstract
{
	const ENTITY = 'flexibletheme_content';
	
	protected function _construct()
    {
        parent::_construct();
        $this->_init('flexibletheme/content');
    }
	
}
