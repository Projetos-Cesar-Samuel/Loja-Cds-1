<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_wysiwygConfig;
    
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );
        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('flexibletheme/adminhtml_form_renderer_fieldset_element')
        );
    }
    
    protected function _setFieldset($attributes, $fieldset, $exclude = array())
    {
        $this->_addElementTypes($fieldset);
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            if (!$attribute || ($attribute->hasIsVisible() && !$attribute->getIsVisible())) {
                continue;
            }
            $inputType = false;
            if ($attribute->getCustomInputType()) {
                $inputType = $attribute->getCustomInputType();
            } else {
                $inputType = $attribute->getFrontend()->getInputType();
            }
            
            if ($inputType && !in_array($attribute->getAttributeCode(), $exclude)) {
                $fieldType      = $inputType;
                $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                $fieldData = array(
                    'name'      => $attribute->getAttributeCode(),
                    'label'     => $attribute->getLabel() ? : $attribute->getFrontend()->getLabel(),
                    'class'     => $attribute->getFrontend()->getClass(),
                    'required'  => $attribute->getIsRequired(),
                    'note'      => $attribute->getNote(),
                );
                if ($attribute->getWysiwyg()) {
                    $fieldData['wysiwyg'] = true;
                    $fieldData['config'] = $this->_getWysiwygConfig();
                }
                
                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType,
                    $fieldData
                )->setEntityAttribute($attribute);

                $element->setAfterElementHtml($this->_getAdditionalElementHtml($element));

                if ($inputType == 'select') {
                    $element->setValues($attribute->getSource()->getAllOptions(true, true));
                } else if ($inputType == 'multiselect') {
                    $element->setValues($attribute->getSource()->getAllOptions(false, true));
                    $element->setCanBeEmpty(true);
                } else if ($inputType == 'date') {
                    $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
                    $element->setFormat(Mage::app()->getLocale()->getDateFormatWithLongYear());
                } else if ($inputType == 'multiline') {
                    $element->setLineCount($attribute->getMultilineCount());
                }
            }
        }
    }
    
    protected function _getWysiwygConfig()
    {
        if ($this->_wysiwygConfig === null) {
            $this->_wysiwygConfig =  Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $this->_wysiwygConfig['height'] = '350px';
        }
        return $this->_wysiwygConfig;
    }
}
