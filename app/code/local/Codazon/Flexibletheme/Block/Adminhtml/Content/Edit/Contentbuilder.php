<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Content_Edit_Contentbuilder extends Mage_Adminhtml_Block_Template
{
    protected $_assetRepo;
	protected $_itemTypes = false;
    protected $_boostrapCols = false;
    protected $_object = false;
    
    public function getItemTypes()
    {
        if ($this->_itemTypes === false) {
            $this->_itemTypes = array();
            $this->_itemTypes['container'] = array(
                'name'      => 'container',
                'title'     => $this->__('Container'),
                'fields'    => array(
                    array('type' => 'select', 'name' => 'container_type', 'label' => 'Type', 'attache_header' => true, 'values' => array(
                        array('value' => 'box',  'label' => $this->__('Has Margins')),
                        array('value' => 'full', 'label' => $this->__('Full Width'))
                    )),
                    array('type' => 'text', 'name' => 'class',    'label' => $this->__('HTML Class')),
                    array('type' => 'text', 'name' => 'id',       'label' => $this->__('HTML Id')),
                    array('type' => 'text', 'name' => 'style',    'label' => $this->__('CSS Inline')),
                    array('type' => 'image', 'name' => 'background', 'label' => $this->__('Background')),
                    array('type' => 'select',    'name' => 'attach_to_section_menu',    'label' => $this->__('Attach to Section Menu'), 'values' => $this->getYesNoOptions(), 'selected_value' => 0),
                    array('type' => 'text',      'name' => 'section_menu_icon',          'label' => $this->__('Section Menu Icon')),
                    array('type' => 'text',      'name' => 'title',          'label' => $this->__('Section Title'))
                )
            );
            $this->_itemTypes['row'] = array(
                'name'      => 'row',
                'title'     => $this->__('Row'),
                'custom_class' => 'row',
                'fields'    => array(
                    array('type' => 'text', 'name' => 'class',    'label' => $this->__('HTML Class')),
                    array('type' => 'text', 'name' => 'id',       'label' => $this->__('HTML Id')),
                    array('type' => 'text', 'name' => 'style',    'label' => $this->__('CSS Inline')),
                    array('type' => 'image', 'name' => 'background', 'label' => $this->__('Background'))
                )
            );
            $this->_itemTypes['col'] = array(
                'name'      => 'col',
                'title'     => $this->__('Column'),
                'custom_class' => 'col-sm-6',
                'fields'    => array(
                    array('type' => 'text', 'name' => 'class',    'label' => $this->__('HTML Class')),
                    array('type' => 'text', 'name' => 'id',       'label' => $this->__('HTML Id')),
                    array('type' => 'text', 'name' => 'style',    'label' => $this->__('CSS Inline')),
                    array('type' => 'select', 'name' => 'width', 'label' => 'Type', 'values' => $this->getBoostrapCols(), 'attache_header' => true,
                        'selected_value' => 6
                    ),
                    array('type' => 'image', 'name' => 'background', 'label' => $this->__('Background'))
                )
            );
            $this->_itemTypes['html'] = array(
                'name'      => 'html',
                'title'     => $this->__('HTML'),
                'disable_children' => true,
                'fields'    => array(
                    array('type' => 'text', 'name' => 'title',    'label' => $this->__('Title'), 'attache_header' => true),
                    array('type' => 'editor', 'name' => 'content', 'label' => 'Content', 'attache_desc' => true)
                )
            );
            $this->_itemTypes['tabs'] = array(
                'name'      => 'tabs',
                'title'     => $this->__('Tabs'),
                'disable_children' => true,
                'fields'    => array(
                    array('type' => 'text', 'name' => 'note',   'label' => $this->__('Note'), 'attache_header' => true),
                    array('type' => 'text', 'name' => 'title',   'label' => $this->__('Title')),
                    array('type' => 'text', 'name' => 'desc',    'label' => $this->__('Description')),
                    array('type' => 'text', 'name' => 'id',    'label' => $this->__('HTML Id')),
                    array('type' => 'text', 'name' => 'class',    'label' => $this->__('CSS Class')),
                    array('type' => 'text', 'name' => 'custom_template',    'label' => $this->__('Custom Template')),
                    array('type' => 'select', 'name' => 'align',    'label' => $this->__('Algin'),
                        'values' => array(
                            array('label' => $this->__('Left'),     'value' => 'left'),
                            array('label' => $this->__('Center'),   'value' => 'center')
                        )
                    ),
                    array('type' => 'multitext', 'name' => 'items',    'label' => $this->__('Tab Items'), 'full_field' => true, 'need_title' => true, 
                        'sub_fields' => array(
                            array('type' => 'text',  'name' => 'title',  'label' => $this->__('Title'), 'field_class_prefix' => 'm', 'prefix' => 'm'),
                            array('type' => 'text', 'name' => 'icon',   'label' => $this->__('Icon'),   'field_class_prefix' => 'm', 'prefix' => 'm'),
                            array('type' => 'editor', 'name' => 'content',  'label' => $this->__('Tab Content'),  'field_class_prefix' => 'm', 'prefix' => 'm')
                        )
                    ),
                )
            );
            $this->_itemTypes['html_slider'] = array(
                'name'      => 'html_slider',
                'title'     => $this->__('HTML Slider'),
                'disable_children' => true,
                'fields'    => array(
                    array('type' => 'text', 'name' => 'title',   'label' => $this->__('Title'), 'attache_header' => true),
                    array('type' => 'text', 'name' => 'desc',    'label' => $this->__('Description')),
                    array('type' => 'text', 'name' => 'class',    'label' => $this->__('Wrapper Class')),
                    array('type' => 'text', 'name' => 'item_class',    'label' => $this->__('Item Class')),
                    array('type' => 'text', 'name' => 'settings',    'label' => $this->__('Settings')),
                    array('type' => 'text', 'name' => 'custom_template',    'label' => $this->__('Custom Template')),
                    array('type' => 'multitext', 'name' => 'items',    'label' => $this->__('Slide Items'), 'full_field' => true,
                        'sub_fields' => array(
                            array('type' => 'editor', 'name' => 'content',  'label' => $this->__('Slide HTML'))
                        )
                    ),
                )
            );
            $this->_itemTypes['images_slider'] = array(
                'name'      => 'images_slider',
                'title'     => $this->__('Image Slider'),
                'disable_children' => true,
                'fields'    => array(
                    array('type' => 'text', 'name' => 'title',   'label' => $this->__('Title'), 'attache_header' => true),
                    array('type' => 'text', 'name' => 'class',    'label' => $this->__('Wrapper Class')),
                    array('type' => 'text', 'name' => 'item_class',    'label' => $this->__('Item Class')),
                    array('type' => 'text', 'name' => 'settings',    'label' => $this->__('Settings')),
                    array('type' => 'multitext', 'name' => 'items',    'label' => $this->__('Slide Items'), 'full_field' => true,
                        'sub_fields' => array(
                            array('type' => 'text',  'name' => 'title',      'label' => $this->__('Title')),
                            array('type' => 'text',  'name' => 'link',       'label' => $this->__('Link')),
                            array('type' => 'image', 'name' => 'image',      'label' => $this->__('Image')),
                            array('type' => 'editor', 'name' => 'content',   'label' => $this->__('Description'))
                        )
                    ),
                )
            );
            $this->_itemTypes['slideshow'] = array(
                'name'      => 'slideshow',
                'title'     => $this->__('Slideshow'),
                'disable_children' => true,
                'fields'    => array(
                    array('type' => 'text', 'name' => 'title',           'label' => $this->__('Title'), 'attache_header' => true),
                    array('type' => 'multitext', 'name' => 'items',      'label' => $this->__('Slide Items'), 'full_field' => true,
                        'sub_fields' => array(
                            array('type' => 'text',  'name' => 'title',      'label' => $this->__('Title')),
                            array('type' => 'text',  'name' => 'link',       'label' => $this->__('Link')),
                            array('type' => 'image', 'name' => 'image',      'label' => $this->__('Image')),
                            array('type' => 'editor', 'name' => 'content',  'label' => $this->__('Description')),
                        )
                    ),
                    array('type' => 'text', 'name' => 'class',           'label' => $this->__('Wrapper Class')),
                    array('type' => 'text', 'name' => 'width',           'label' => $this->__('Width (px)')),
                    array('type' => 'text', 'name' => 'height',          'label' => $this->__('Height (px)')),
                    array('type' => 'select', 'name' => 'animation_in',  'label' => $this->__('Animation In'), 'values' => $this->getAnimationsArray(1)),
                    array('type' => 'select', 'name' => 'animation_out', 'label' => $this->__('Animation Out'), 'values' => $this->getAnimationsArray(2)),
                    array('type' => 'select', 'name' => 'show_nav',      'label' => $this->__('Show Arrows'), 'values' => $this->getYesNoOptions()),
                    array('type' => 'select', 'name' => 'show_dots',     'label' => $this->__('Show Dots'), 'values' => $this->getYesNoOptions()),
                    array('type' => 'select', 'name' => 'auto_play',     'label' => $this->__('Auto Play'), 'values' => $this->getYesNoOptions()),
                    array('type' => 'select', 'name' => 'loop',    'label' => $this->__('Loop'), 'values' => $this->getYesNoOptions()),
                    array('type' => 'text', 'name' => 'auto_play_timeout',   'label' => $this->__('Auto Play Timeout'), 'value' => 5000),
                )
            );
            $this->_itemTypes['video'] = array(
                'name'      => 'video',
                'title'     => $this->__('Video Frame'),
                'disable_children' => true,
                'fields'    => array(
                    array('type' => 'text',      'name' => 'title',              'label' => $this->__('Title'), 'attache_header' => true),
                    array('type' => 'image',     'name' => 'placeholder',        'label' => $this->__('Placehoder Image')),
                    array('type' => 'select',    'name' => 'use_df_placeholder', 'label' => $this->__('Use Default Placehoder'),
                        'values' => $this->getYesNoOptions(), 'selected_value' => 1,
                        'desc' => $this->__('Default Placehoder is loaded from Youtube or Vimeo')),
                    array('type' => 'text',      'name' => 'video_url',          'label' => $this->__('Video URL'), 'desc' => $this->__('Get Video from Youtube or Vimeo')),
                    array('type' => 'text',      'name' => 'ratio',              'label' => $this->__('Frame Dimension Ratio '), 
                        'desc' => $this->__('Ratio = Height/Width. Eg. 480px/854px = 0.562'))
                )
            );
        }
        return $this->_itemTypes;
    }
    
    public function getYesNoOptions()
    {
        return array(
            array('value' => 0, 'label' => __('No')),
            array('value' => 1, 'label' => __('Yes'))
        );
    }
    
    public function getAnimationsArray($type = 0)
    {
        $animations = array(
            array('label' => '-- none animation --', 'value' => ''),
            array('label' => 'bounce', 'value' => 'bounce'),
            array('label' => 'flash', 'value' => 'flash'),
            array('label' => 'pulse', 'value' => 'pulse'),
            array('label' => 'rubberBand', 'value' => 'rubberBand'),
            array('label' => 'shake', 'value' => 'shake'),
            array('label' => 'swing', 'value' => 'swing'),
            array('label' => 'tada', 'value' => 'tada'),
            array('label' => 'wobble', 'value' => 'wobble'),
            array('label' => 'jello', 'value' => 'jello'),
            array('label' => 'bounceIn', 'value' => 'bounceIn'),
            array('label' => 'bounceInDown', 'value' => 'bounceInDown'),
            array('label' => 'bounceInLeft', 'value' => 'bounceInLeft'),
            array('label' => 'bounceInRight', 'value' => 'bounceInRight'),
            array('label' => 'bounceInUp', 'value' => 'bounceInUp'),
            array('label' => 'bounceOut', 'value' => 'bounceOut'),
            array('label' => 'bounceOutDown', 'value' => 'bounceOutDown'),
            array('label' => 'bounceOutLeft', 'value' => 'bounceOutLeft'),
            array('label' => 'bounceOutRight', 'value' => 'bounceOutRight'),
            array('label' => 'bounceOutUp', 'value' => 'bounceOutUp'),
            array('label' => 'fadeIn', 'value' => 'fadeIn'),
            array('label' => 'fadeInDown', 'value' => 'fadeInDown'),
            array('label' => 'fadeInDownBig', 'value' => 'fadeInDownBig'),
            array('label' => 'fadeInLeft', 'value' => 'fadeInLeft'),
            array('label' => 'fadeInLeftBig', 'value' => 'fadeInLeftBig'),
            array('label' => 'fadeInRight', 'value' => 'fadeInRight'),
            array('label' => 'fadeInRightBig', 'value' => 'fadeInRightBig'),
            array('label' => 'fadeInUp', 'value' => 'fadeInUp'),
            array('label' => 'fadeInUpBig', 'value' => 'fadeInUpBig'),
            array('label' => 'fadeOut', 'value' => 'fadeOut'),
            array('label' => 'fadeOutDown', 'value' => 'fadeOutDown'),
            array('label' => 'fadeOutDownBig', 'value' => 'fadeOutDownBig'),
            array('label' => 'fadeOutLeft', 'value' => 'fadeOutLeft'),
            array('label' => 'fadeOutLeftBig', 'value' => 'fadeOutLeftBig'),
            array('label' => 'fadeOutRight', 'value' => 'fadeOutRight'),
            array('label' => 'fadeOutRightBig', 'value' => 'fadeOutRightBig'),
            array('label' => 'fadeOutUp', 'value' => 'fadeOutUp'),
            array('label' => 'fadeOutUpBig', 'value' => 'fadeOutUpBig'),
            array('label' => 'flip', 'value' => 'flip'),
            array('label' => 'flipInX', 'value' => 'flipInX'),
            array('label' => 'flipInY', 'value' => 'flipInY'),
            array('label' => 'flipOutX', 'value' => 'flipOutX'),
            array('label' => 'flipOutY', 'value' => 'flipOutY'),
            array('label' => 'lightSpeedIn', 'value' => 'lightSpeedIn'),
            array('label' => 'lightSpeedOut', 'value' => 'lightSpeedOut'),
            array('label' => 'rotateIn', 'value' => 'rotateIn'),
            array('label' => 'rotateInDownLeft', 'value' => 'rotateInDownLeft'),
            array('label' => 'rotateInDownRight', 'value' => 'rotateInDownRight'),
            array('label' => 'rotateInUpLeft', 'value' => 'rotateInUpLeft'),
            array('label' => 'rotateInUpRight', 'value' => 'rotateInUpRight'),
            array('label' => 'rotateOut', 'value' => 'rotateOut'),
            array('label' => 'rotateOutDownLeft', 'value' => 'rotateOutDownLeft'),
            array('label' => 'rotateOutDownRight', 'value' => 'rotateOutDownRight'),
            array('label' => 'rotateOutUpLeft', 'value' => 'rotateOutUpLeft'),
            array('label' => 'rotateOutUpRight', 'value' => 'rotateOutUpRight'),
            array('label' => 'slideInUp', 'value' => 'slideInUp'),
            array('label' => 'slideInDown', 'value' => 'slideInDown'),
            array('label' => 'slideInLeft', 'value' => 'slideInLeft'),
            array('label' => 'slideInRight', 'value' => 'slideInRight'),
            array('label' => 'slideOutUp', 'value' => 'slideOutUp'),
            array('label' => 'slideOutDown', 'value' => 'slideOutDown'),
            array('label' => 'slideOutLeft', 'value' => 'slideOutLeft'),
            array('label' => 'slideOutRight', 'value' => 'slideOutRight'),
            array('label' => 'zoomIn', 'value' => 'zoomIn'),
            array('label' => 'zoomInDown', 'value' => 'zoomInDown'),
            array('label' => 'zoomInLeft', 'value' => 'zoomInLeft'),
            array('label' => 'zoomInRight', 'value' => 'zoomInRight'),
            array('label' => 'zoomInUp', 'value' => 'zoomInUp'),
            array('label' => 'zoomOut', 'value' => 'zoomOut'),
            array('label' => 'zoomOutDown', 'value' => 'zoomOutDown'),
            array('label' => 'zoomOutLeft', 'value' => 'zoomOutLeft'),
            array('label' => 'zoomOutRight', 'value' => 'zoomOutRight'),
            array('label' => 'zoomOutUp', 'value' => 'zoomOutUp'),
            array('label' => 'hinge', 'value' => 'hinge'),
            array('label' => 'rollIn', 'value' => 'rollIn'),
            array('label' => 'rollOut', 'value' => 'rollOut')
        );
        if ($type === 1) {
            foreach ($animations as $i => $animation) {
                if ((strpos($animation['value'], 'Out') !== false) && ($animation['value'] != '')) { //stripos not differentiate uppercase/lowercase
                    unset($animations[$i]);
                }
            }
        } elseif ($type === 2) {
            foreach ($animations as $i => $animation) {
                if ((strpos($animation['value'], 'Out') === false) && ($animation['value'] != '')) { //stripos not differentiate uppercase/lowercase
                    unset($animations[$i]);
                }
            }
        }
        return $animations;
    }
    
    public function getBoostrapCols()
    {
        if ($this->_boostrapCols === false) {
            $this->_boostrapCols = [];
            for($i=1; $i <= 24; $i++) {
                $this->_boostrapCols[$i] = [
                    'value' => $i,
                    'label' => 'col-sm-' . $i
                ];
            }
        }
        return $this->_boostrapCols;
    }
    
    public function getImageUrl($path)
    {
        return $this->getSkinUrl('codazon/flexibletheme/images/' . $path, array('_secure' => true));
    }
    
    public function getMediaUrl($path = '')
    {
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$path;
	}
    
    public function getDataObject()
    {
        if ($this->_object === false) {
            $this->_object = Mage::registry('flexibletheme_data');
        }
        return $this->_object;
    }
    
    public function displayUseDefault($attributeCode)
    {
        $store = $this->getRequest()->getParam('store');
        return ($store != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
    }
    
    public function isUseDefault($attributeCode)
    {
        $object = $this->getDataObject();
        return ($object->getExistsStoreValueFlag($attributeCode) != 1);
    }
}