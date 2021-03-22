<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Resource_Attribute_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Collection
{
	/**
     * Resource model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('flexibletheme/attribute', 'eav/entity_attribute');
    }
}