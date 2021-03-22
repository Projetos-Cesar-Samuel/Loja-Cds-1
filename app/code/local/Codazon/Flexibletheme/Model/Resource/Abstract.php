<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Resource_Abstract extends Mage_Catalog_Model_Resource_Abstract
{
	protected function _getDefaultAttributeModel()
    {
        return 'flexibletheme/attribute';
    }
}
