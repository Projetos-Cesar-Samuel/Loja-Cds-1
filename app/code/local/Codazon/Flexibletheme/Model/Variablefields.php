<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Variablefields
{
    protected $_configPath = 'adminhtml/variables';
    
    protected $_fieldFile;
    
    protected $_defaultValueFile;
    
    protected $_variableFileName;
    
    protected $_projectPath;
    
    protected $_module = 'Codazon_Flexibletheme';
    
    protected $_xmlManager;
    
    protected $_configDir;
    
    protected $_helper;
    
    public function init($variableFileName, $projectPath, $fieldFileName = null)
    {
        $this->_helper = Mage::helper('flexibletheme');
        $this->_xmlManager = new Varien_Simplexml_Config();
        $this->_variableFileName = $variableFileName;
        $this->_projectPath = $projectPath;
        $this->_fieldFileName = $fieldFileName;
    }
    
    public function getConfigDir()
    {
        if ($this->_configDir === null) {
            $this->_configDir = str_replace('/', DS, Mage::getModuleDir('etc', $this->_module) . DS . $this->_configPath);
        }
        return $this->_configDir;
    }
    
    public function getFieldFile()
    {
        if ($this->_fieldFile === null) {
            $this->_fieldFile = $this->getConfigDir() . DS . $this->_fieldFileName;
        }
        return $this->_fieldFile;
    }
    
    public function loadVariableFields($form, $model)
    {
        $fieldFile = $this->getFieldFile();
        $this->_xmlManager->loadFile($fieldFile);
        $system = $this->_xmlManager->getNode('system');
        if (is_object($system)) {
            $section = $system->section;
            $defautScope = $section->getAttribute('id');
            $groups = $section->group;
            $fieldsetRenderer = Mage::getBlockSingleton('flexibletheme/adminhtml_form_renderer_fieldset');
            $parentFieldSet = $form->addFieldset('settings', array('legend' => __('Font/Color Variables'), 'class' => 'variable-fieldset'));
            $projectImagePath = $model->getProjectImagePath();
            $uploadUrl = Mage::getUrl('adminhtml/flexibletheme_content/uploadimage');
            $defaultData = $model->getDefaultData(false);

            if (count($groups)) {
                foreach ($groups as $group) {
                    $name = $group->getAttribute('id');
                    $label = isset($group->label) ? $this->_helper->__($group->label[0]->__toString()) : '';
                    
                    $fieldset = $parentFieldSet->addFieldset($name, array('legend' => $label));
                    $fieldset->setRenderer($fieldsetRenderer);
                    $fieldset->addType('color', Codazon_Flexibletheme_Block_Adminhtml_Form_Element_Color::class);
                    $fieldset->addType('ajax_image', Codazon_Flexibletheme_Block_Adminhtml_Form_Element_Image::class);
                    $fields = @$group->xpath('field');
                    
                    foreach ($fields as $field) {
                        $orgName = $field->getAttribute('id');
                        if ($scope = $field->getAttribute('dataScope')) {
                            $isVariable = false;
                            $fieldName = $field->getAttribute('dataScope') . '[' . $orgName . ']';
                            $fieldId = $field->getAttribute('dataScope') . '_' . $orgName;
                        } else {
                            $scope = $defautScope;
                            $isVariable = true;
                            $fieldName = $scope . '[' . $orgName . ']';
                            $fieldId = $scope . '_' . $orgName;
                        }
                        $type = $field->getAttribute('type');
                        $sortOrder = $field->getAttribute('sortOrder');
                        $soureModel = $field->getAttribute('sourceModel');
                        $label = isset($field->label) ? $this->_helper->__($field->label[0]->__toString()) : '';
                        $require = $field->getAttribute('require')? : false ;
                        $fieldData = array(
                            'label'     => $label,
                            'name'      => $fieldName,
                            'required'  => $require
                        );
                        if (!empty($defaultData[$scope][$orgName])) {
                            if (is_array($defaultData[$scope][$orgName])) {
                                $defaultData[$scope][$orgName] = implode(', ', $defaultData[$scope][$orgName]);
                            }
                            $fieldData['note'] = $this->_helper->__('Default: [%1s]', $defaultData[$scope][$orgName]);
                        }
                        if ($isVariable) {
                            $fieldData['after_element_html'] = ' <a class="cdz-tooltip"><span><span>@'.$orgName.'</span></span></a>';
                        }
                        if (!empty($field->note)) {
                            $fieldData['note'] = $field->note;
                        }
                        if ($field->getAttribute('sourceModel')) {
                            $modelClass = $field->getAttribute('sourceModel');
                            $fieldData['values'] = Mage::getSingleton($modelClass)->toOptionArray();
                        }
                        
                        
                        switch ($type) {
                            case 'ajax_image':
                                $fieldData['upload_url'] = $uploadUrl;
                                $fieldData['media_url'] = $projectImagePath;
                                $fieldData['onchange'] = 'CodazonMedia.updatePreview(this, \'' . $projectImagePath . '\');';
                                break;
                            case 'color' :
                                $fieldData['onchange'] = '$(\'color_' . $fieldId . '\').value = this.value';
                                break;
                            default:
                        }
                        $fieldset->addField($fieldId, $type, $fieldData);
                    }
                }
            }
        }
    }
}
