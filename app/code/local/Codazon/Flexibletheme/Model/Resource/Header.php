<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Resource_Header extends Codazon_Flexibletheme_Model_Resource_Abstract
{
	
	public function __construct()
    {
        $this->setType(Codazon_Flexibletheme_Model_Header::ENTITY);
        $this->setConnection('flexibletheme_read', 'flexibletheme_write');
    }
    
    protected function _getDefaultAttributes()
    {
        return array('entity_id', 'entity_type_id', 'attribute_set_id', 'type_id', 'is_active', 'identifier', 'parent', 'variables', 'custom_fields', 'created_at', 'updated_at');
    }   
}
