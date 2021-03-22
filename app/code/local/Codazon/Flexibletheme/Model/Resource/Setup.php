<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{
    protected $_elementTypes = array('header', 'content', 'footer');
    
    public function getDefaultEntities()
    {
        $entities = array();
        foreach ($this->_elementTypes as $type) {
            $entities['flexibletheme_' . $type] = array(
                'entity_model'                  => 'flexibletheme/' . $type,
                'attribute_model'		        => 'flexibletheme/attribute',
                'table'                         => 'flexibletheme/' . $type,
                'additional_attribute_table'    => null,
                'entity_attribute_collection'   => 'flexibletheme/attribute_collection',
                'default_group'                 => 'General Information',
                'attributes'                => array(
                    'title'   => array(
                        'type'              => 'varchar',
                        'label'             => 'Title',
                        'input'             => 'text',
                        'required'          => true,
                        'global'            => Codazon_Flexibletheme_Model_Attribute::SCOPE_STORE,
                        'sort_order'        => 10
                    ),
                    'content' => array(
                        'type'              => 'text',
                        'label'             => 'Content',
                        'input'             => 'editor',
                        'required'          => false,
                        'global'            => Codazon_Flexibletheme_Model_Attribute::SCOPE_STORE,
                        'sort_order'        => 20
                    ),
                    'layout_xml'  => array(
                        'type'              => 'text',
                        'label'             => 'Layout',
                        'input'             => 'editor',
                        'required'          => false,
                        'global'            => Codazon_Flexibletheme_Model_Attribute::SCOPE_STORE,
                        'sort_order'        => 30
                    ),
                )
            );
            if ($type == 'header') {
                $entities['flexibletheme_' . $type]['attributes']['content_1'] = array(
                    'type'              => 'text',
                    'label'             => 'Content 1',
                    'input'             => 'editor',
                    'required'          => false,
                    'global'            => Codazon_Flexibletheme_Model_Attribute::SCOPE_STORE,
                    'sort_order'        => 25
                );
            }
        }
        return $entities;
    }
    
    
}