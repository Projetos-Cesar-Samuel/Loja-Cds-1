<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Resource_Flexibletheme_Collection extends Mage_Catalog_Model_Resource_Collection_Abstract
{
	protected function _construct()
    {
        $this->_init('flexibletheme/content');
    }
}
