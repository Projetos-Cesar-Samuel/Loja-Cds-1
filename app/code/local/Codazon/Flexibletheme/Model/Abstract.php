<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Abstract extends Mage_Catalog_Model_Abstract
{
    protected $_fieldManager;
    
    protected $_fieldFileName = 'main_content.xml';
    protected $_varFileName = '_variables.less.css';
    protected $_elementsFileName = '_elements.less.css'; //file can be edited
    protected $_tsprImg = "~'codazon/flexibletheme/images/tspr.png'";
    protected $_defaultFileName = 'variables.xml';
    
    protected $_projectPath = 'codazon/flexibletheme/main';
    protected $_imagesPath = 'codazon/flexibletheme/images';
    protected $_fontPath = 'codazon/flexibletheme/fonts';
    
    protected $_mainFileName = 'main-styles.less.css';
    protected $_cssFileName = 'main-styles.css';
    protected $_defaultValues = array();
    protected $_flexibleLessDir = 'codazon/flexibletheme/main/general/flexible';
    protected $io;
    protected $_defaultData = false;
    
    protected $_projectImgUrl;
    protected $_projectDir;
    protected $_mediaPath;
    protected $initData;
    protected $_useAsBlock;
    protected $_autoImportLessFiles;
    
    protected function _construct()
    {
        $this->_mediaPath = Mage::getBaseDir('media');
        $this->_projectDir = $this->_mediaPath . DS . $this->_projectPath . DS;
        
        $this->io = new Varien_Io_File();
        return $this;
    }
    
    public function loadVariableFields($form)
    {
        $fieldManger = $this->getVariableFieldsManager();
        $fieldManger->loadVariableFields($form, $this);
    }
    
    public function getVariableFieldsManager()
    {
        if ($this->_fieldManager === null) {
            $this->_fieldManager = Mage::getSingleton('flexibletheme/variablefields');
            $this->_fieldManager->init($this->_varFileName, $this->_projectPath, $this->_fieldFileName);
        }
        return $this->_fieldManager;
    }
    
    public function saveUploadedImage()
    {
        $uploader = new Mage_Core_Model_File_Uploader('datafile');
        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);
        $result = $uploader->save(
            Mage::getBaseDir('media') . DS . $this->_imagesPath
        );
        return $result;
    }
    
    public function getProjectImageUrl($file = '')
    {
        return Mage::getBaseUrl('media') . $this->_imagesPath .'/' . $file;
    }
    
    public function getProjectImagePath()
    {
        return Mage::getBaseUrl('media') . $this->_imagesPath;
    }
       
    protected function _getElementDirName()
    {
        return $this->getData('identifier');
    }
    
    public function updateWorkspace($export = false)
    {
        
        if (!file_exists($this->_projectDir)) {
            $this->io->mkdir($this->_projectDir, 0777, true);
        }
        $elDirName = $this->_getElementDirName();                                       //Name of current project (folder name) 
        $elDir = str_replace('/', DS, $this->_projectDir) . $elDirName . DS;                                  //Folder of current project
        $elMainFile = $elDir . $this->_mainFileName;                                    //main file
        
        $elVarFile = $elDir . $this->_varFileName;                                      //variables file
        $elElementsFile = $elDir . $this->_elementsFileName;                            //file can be edited
        
        if (!$this->io->fileExists($elDir, false)) {
            $this->io->mkdir($elDir, 0777, true);
        }
        
        $this->write($elMainFile, $this->_getMainFileContent(), 0666);  //write content
        
        if (!$this->io->fileExists($elElementsFile, true)) {
            $this->write($elElementsFile, '', 0666);
        }
        
        $content = $this->_getVarFileContent() . "\n" . (string)$this->getData('custom_variables');

        $this->write($elVarFile, $content, 0666);
        
        
        $parser = new Less_Parser(
            array(
                'relativeUrls' => false,
                'compress' => true
            )
        );
        $content = $this->read($elMainFile);
        $elCssFile = $elDir . $this->_cssFileName;
        $this->write($elCssFile, $content, 0666);
        
        
        try {
            gc_disable();
            $parser->parseFile($elCssFile, '');
            $content = $parser->getCss();
            gc_enable();
            $mediaUrl = Mage::getBaseUrl('media');
            
            $content = str_replace($this->_imagesPath, '../../../../' . $this->_imagesPath, $content);
            $content = str_replace($this->_fontPath, '../../../../' . $this->_fontPath, $content);
            $this->write($elCssFile, $content, 0666);
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
        }
        
        if ($export) {
            $elDefaultFile = $elDir . $this->_defaultFileName;
            $this->exportVariables($elDefaultFile);
        }
        
    }
   
    protected function read($filename, $dest = null)
    {
        try {
            return @$this->io->read($filename, $dest);
        } catch (Exception $e) {
            
        }
    }
    
    public function getDefaultData($encode = true)
    {
        if ($this->_defaultData === false) {
            $elDirName = $this->_getElementDirName();
            $elDir = $this->_projectDir . $elDirName . '/';
            $elDefaultFile = $elDir . $this->_defaultFileName;
            
            if ($this->io->fileExists($elDefaultFile, false)) {
                $xmlConfig = new Varien_Simplexml_Config();
                $xmlConfig->loadFile($elDefaultFile);
                $data = Mage::helper('core')->xmlToAssoc($xmlConfig->getNode());
                unset($data['identifier']);
                unset($data['title']);
                if ($encode) {
                    $data['variables'] = json_encode($data['variables']);
                }
                if (isset($data['custom_fields']) && $encode) {
                    $data['custom_fields'] = json_encode($data['custom_fields']);
                }
                $this->_defaultData = $data;
            } else {
                $this->_defaultData = array();
            }
        }
        return $this->_defaultData;
    }
    
    public function getVersion()
    {
        $customField = json_decode($this->getData('custom_fields'), true);
        return empty($customField['version']) ? '1' : $customField['version'];
    }
    
    protected function exportVariables($file)
    {
        $xmlConfig = new Varien_Simplexml_Config();
        $variables = json_decode($this->getData('variables'), true);
        ksort($variables);
        $content = array(
            'identifier'            => $this->getData('identifier'),
            'title'                 => $this->getData('title'),
            'variables'             => $variables,
            'layout_xml'            => $this->getData('layout_xml'),
            'custom_variables'      => $this->getData('custom_variables'),
            'custom_fields'         => json_decode($this->getData('custom_fields'), true),
            'parent'                => $this->getData('parent'),
            'content'               => $this->getData('content')
        );
        $xml = Mage::helper('core')->assocToXml($content, 'config');
        $xmlConfig->loadString($xml->saveXml());
        $this->write($file, $xmlConfig->getNode()->asNiceXml() , 0666);    
    }
    
    protected function write($filename, $src, $mode = null)
    {
        try {
            @$this->io->write($filename, $src, $mode);
        } catch (Exception $e) {
            
        }
    }
    
    public function getFlexibleLessDir()
    {
        return $this->_mediaPath . DS . $this->_flexibleLessDir . DS;
    }
    
    public function getFlexibleFileList()
    {
        $lessDir = $this->getFlexibleLessDir();
        $lessFiles = array_filter(glob($lessDir . '*.less.css'), 'is_file');
        $list = array();
        foreach ($lessFiles as $lessfile) {
            $fileName = explode(DS, $lessfile);
            $list[] = $fileName[count($fileName) - 1];
        }
        return $list;
    }
    
    protected function _getMainFileContent()
    {
        $this->autoImportLessFiles();
        $content = "@import (optional,less)'../_default_variables.less.css';\n";
        $content = "@import (less)'" . $this->_varFileName . "';\n";
        if ($this->useAsBlock()) {
            $content .= "@import (less)'../_mini-general.less.css';\n";
        } else {
            $content .= "@import (less)'../_general.less.css';\n";
        }
        
        if ($this->getData('parent')) {
            $parentModel = $this;
            $parent = $parentModel->getData('parent');
            $import = array();
            while($parent) {
                $import[] = "@import (less)'../{$parent}/" . $this->_elementsFileName . "';\n";
                $parentModel = Mage::getModel(get_class($this))->getCollection()->addFieldToFilter('identifier', $parent)->getFirstItem();
                $parent = $parentModel->getData('parent');
            }
            for ($i = count($import); $i > 0; $i--) {
                $content .= $import[$i - 1];
            }
        }
        
        if ($customField = $this->getData('custom_fields')) {
            $customField = json_decode($customField, true);
            $usedLess = [];
            
            if (!empty($customField['flexible_less'])) {
                $usedLess = array_merge($usedLess, $customField['flexible_less']);
            }
            if (!empty($customField['category_view_less'])) {
                $usedLess = array_merge($usedLess, array($customField['category_view_less']));
            }
            if (!empty($customField['product_view_less'])) {
                $usedLess = array_merge($usedLess, array($customField['product_view_less']));
            }
            if (!empty($customField['auto_detect_files'])) {
                $usedLess = array_merge($usedLess, $customField['auto_detect_files']);
            }
            $usedLess = array_unique($usedLess);
            if (count($usedLess)) {
                $flexibleFileList = $this->getFlexibleFileList();
                foreach ($usedLess as $flexibleLess) {
                    //if (in_array($flexibleLess, $flexibleFileList)) {
                        $content .= "@import (optional,less)'../general/flexible/" . $flexibleLess .  "';\n";
                    //}
                }
            }
        }
        
        $content .= "@import (less)'" . $this->_elementsFileName . "';";
        return $content;
    }
    
    public function useAsBlock()
    {
        if ($this->_useAsBlock === null) {
            if ($customField = $this->getData('custom_fields')) {
                $customField = json_decode($customField, true);
                $this->_useAsBlock = isset($customField['use_as_block']) ? (bool)$customField['use_as_block'] : false;
            } else {
                $this->_useAsBlock = false;
            }
        }
        return $this->_useAsBlock;
    }
    
    protected function _getVarFileContent()
    {
        $variables = json_decode($this->getData('variables'), true);
        if (!$variables) {
            $variables = array();
        }
        ksort($variables);
        $content = '';
        foreach ($variables as $varName => $varValue) {
            $content .= $this->_assignLessVar($varName, $varValue);
        }
        return $content;
    }
    
    protected function _assignLessVar($varName, $varValue)
    {
		if (is_array($varValue)) {
			return '';
		}
        $varValue = trim($varValue);
        if (strpos($varValue, ' ') !== false) {
            $varValue = "~'{$varValue}'";
        }
        if (!$varValue) {
            $varValue = "''";
        }
        if ($varValue == "''" && (strpos($varName, 'background_file')!== false)) {
            $varValue = $this->_tsprImg;
        } elseif (strpos($varName, 'background_file')!== false) {
            $varValue = "~'" .$this->_imagesPath . "{$varValue}'";
        }
        return "@{$varName}:{$varValue};";
    }
    
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }
	
    public function save()
    {
        $initData = $this->getInitialData();
        if (!empty($initData['variables'])) {
            $defaultVariables = (array)json_decode($initData['variables'], true);
            $variables = (array)json_decode($this->getData('variables'), true);
            $variables = array_replace($defaultVariables, $variables);
            $this->setData('variables', json_encode($variables));
        }
        if ($customFields = $this->getData('custom_fields')) {
            
            if (!is_array($customFields)) {
                $customFields = (array)json_decode($customFields, true);
            }
        } else {
            $customFields = [];
        }
        if (!empty($initData['custom_fields'])) {
            $defaultCustomFields = (array)json_decode($initData['custom_fields'], true);
            $customFields = array_replace($defaultCustomFields, $customFields);
        }
        $customFields['version'] = uniqid(); 
        $this->setData('custom_fields', json_encode($customFields));
        $this->autoImportLessFiles();
        
        return parent::save();
    }
    
    protected function _getMainHtml()
    {
        return $this->getData('content');
    }
    
    public function autoImportLessFiles()
    {
        if ($this->_autoImportLessFiles === null) {
            $this->_autoImportLessFiles = true;
            $mappingFile = $this->_projectDir . 'styles_mapping.xml';
            if ($this->io->fileExists($mappingFile, false)) {
                $customFields = (array)json_decode($this->getData('custom_fields'), true);
                $autoImport = isset($customFields['auto_import_less_files']) ? (int)$customFields['auto_import_less_files'] : 1;
                if ($autoImport) {
                    $xmlConfig = new Varien_Simplexml_Config();
                    $xmlConfig->loadFile($mappingFile);
                    $styles = Mage::helper('core')->xmlToAssoc($xmlConfig->getNode());
                    $content = $this->_getMainHtml();
                    $importFiles = [];
                    foreach ($styles as $class => $file) {
                        if (stripos($content, $class) !== false) {
                            $importFiles[$file] = $file;
                        }
                    }
                    if (count($importFiles)) {
                        $customFields['auto_detect_files'] = [];
                        foreach ($importFiles as $file) {
                            if (!in_array($file, $customFields['flexible_less'])) {
                                $customFields['auto_detect_files'][] = $file;
                            }
                        }
                        $this->setData('custom_fields', json_encode($customFields));
                    }
                }
            }
        }
    }
    
    public function getInitialData()
    {
        if ($this->initData === null) {
            $elDefaultFile = $this->_projectDir . 'default.xml';
            if ($this->io->fileExists($elDefaultFile, false)) {
                $xmlConfig = new Varien_Simplexml_Config();
                $xmlConfig->loadFile($elDefaultFile);
                $data = Mage::helper('core')->xmlToAssoc($xmlConfig->getNode());
                
                unset($data['identifier']);
                unset($data['title']);
                $data['variables'] = json_encode($data['variables']);
                
                if (isset($data['custom_fields'])) {
                    if ($data['custom_fields'] != '0') {
                        $data['custom_fields'] = array();
                    }
                    $data['custom_fields'] = (array)$data['custom_fields'];
                    foreach ($data['custom_fields'] as $name => $field) {
                        if (isset($data['custom_fields'][$name]['item'])) {
                            $data['custom_fields'][$name] = $data['custom_fields'][$name]['item'];
                        }
                    }
                    $data['custom_fields'] = json_encode($data['custom_fields']);
                }
                $this->initData = $data;
            } else {
                $this->initData = array();
            }
        }
        return $this->initData;
    }
    
	public function getAttributes($group = array())
    {
        $postAttributes = $this->getResource()
            ->loadAllAttributes($this)
            ->getSortedAttributes();
		$attributes = array();	
		if(count($group)){
			foreach ($postAttributes as $attribute) {
				if (in_array($attribute->getAttributeCode(), $group)) {
					$attributes[$attribute->getAttributeCode()] = $attribute;
				}
			}
		}
		else
			$attributes = $postAttributes;
        return $attributes;
    }
    
    public function delete()
    {
        $children = Mage::getModel(get_class($this))
            ->getCollection()
            ->addFieldToFilter('parent', $this->getIdentifier());
        if ($children->count()) {
            $childIdentifier = array();
            foreach ($children as $child) {
                $childIdentifier[] = '"'.$child->getIdentifier().'"';
            }
            throw new Exception(
                Mage::helper('flexibletheme')->__('Cannot delete %s because it is parent of %s. Please unassigned "Extends CSS from" value for its children first.', '"'.$this->getIdentifier().'"', implode(', ', $childIdentifier))
            );
        }
        
        $elDirName = $this->_getElementDirName();
        $elDir = $this->_projectDir . $elDirName . '/';
        @$this->io->rmdir($elDir, true);
        parent::delete();
    }
    

        
    public function getMainCssFileRelativePath()
    {
        return $this->_projectPath .'/'. $this->_getElementDirName() . '/' . $this->_cssFileName;
    }
    
    public function getMainLessFileAbsolutePath()
    {
        return $this->_projectDir . $this->_getElementDirName() . '/' . $this->_mainFileName;
    }
    
    public function getMainLessFileRelativePath()
    {
        return $this->_projectPath .'/'. $this->_getElementDirName() . '/' . $this->_mainFileName;
    }
    
    public function getMainCssFileAbsolutePath()
    {
        return $this->_projectDir . $this->_getElementDirName() . '/' . $this->_cssFileName;
    }
    
    public function cssFileExisted()
    {
        return $this->io->fileExists($this->getMainCssFileAbsolutePath(), true);
    }
    
}
