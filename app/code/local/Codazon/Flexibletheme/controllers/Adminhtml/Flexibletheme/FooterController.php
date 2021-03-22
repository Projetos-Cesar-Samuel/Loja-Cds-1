<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Adminhtml_Flexibletheme_FooterController extends Mage_Adminhtml_Controller_Action
{
	
    protected $_idField = 'id';
    
	protected function _isAllowed()
	{	
		return true;
	}
	
	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu("flexibletheme/footer")->_addBreadcrumb(
			Mage::helper("adminhtml")->__("Manage Footers"),
			Mage::helper("adminhtml")->__("Flexible Theme")
		);
		return $this;
	}
	
	public function indexAction() 
	{
		$this->_title($this->__("Flexible Theme"));
		$this->_title($this->__("Footer"));
		
		$this->_initAction();
		$this->renderLayout();
	}
	
	public function editAction()
	{	
        $helper = Mage::helper("flexibletheme");
		$this->_title($this->__("Flexible Theme"));
		$this->_title($this->__("Footer"));
        
        
        
		$id = $this->getRequest()->getParam("id");
        $storeId = $this->getRequest()->getParam('store');
        
		$model = Mage::getModel("flexibletheme/footer")->setStoreId($storeId)->setStore($storeId)->load($id);

        if ($model->getId()) {
            $this->_title($model->getTitle());
            $scopes = array('variables', 'custom_fields');
            
            foreach ($scopes as $scope) {
                if ($fields = $model->getData($scope)) {
                    $fields = json_decode($fields, true);
                    if (is_array($fields)) {
                        foreach($fields as $key => $field) {
                            $model->setData($scope . '_' . $key, $field);
                        }
                    }
                    
                }
            }
            
		} else {
            Mage::getSingleton("adminhtml/session")->addError($helper->__("Item does not exist."));
            return $this->_redirect("*/*/");
		}
        Mage::register("flexibletheme_data", $model);
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
        $this->_addBreadcrumb($helper->__("Flexible Theme"), $helper->__("Flexible Theme"));
        $this->_addBreadcrumb($helper->__("Footer"), $helper->__("Footer"));
        $this->_setActiveMenu("flexibletheme/footer");	
        $this->renderLayout();
        
	}
	
    public function newAction()
    {
        $helper = Mage::helper("flexibletheme");
        $this->_title($this->__("Flexible Theme"));
        $this->_title($this->__("Footer"));
        $this->_title($this->__("New Item"));
        $id   = $this->getRequest()->getParam("id");
        $storeId = $this->getRequest()->getParam('store');
        $model  = Mage::getModel("flexibletheme/footer")->setStoreId($storeId)->setStore($storeId)->load($id);

        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        } else {
            $model->addData([
                'is_active' => 1
            ]);
        }
        
        Mage::register("flexibletheme_data", $model);
	
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
        $this->_setActiveMenu("flexibletheme/footer");
        $this->renderLayout();
        $this->_addBreadcrumb($helper->__("Flexible Theme"), $helper->__("Flexible Theme"));
        $this->_addBreadcrumb($helper->__("Footer"), $helper->__("Footer"));
        
    }
    
    public function uploadimageAction()
    {
        $model = Mage::getModel("flexibletheme/footer");
        $result = $model->saveUploadedImage();
        $fileData['dir_path'] = $model->getProjectImagePath();
        $fileData['file_path'] = $result['file'];
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($fileData));
    }

    protected function _encodeData($data)
    {
        $scopes = array('variables', 'custom_fields');
            
        foreach ($scopes as $scope) {
            if (isset($data[$scope])) {
                $data[$scope] = json_encode($data[$scope]);
            }
        }
        return $data;
    }
    
    protected function _updateWorkspace($data, $model, $export)
    {
        $model->updateWorkspace($export);
    }
    
    public function saveAction()
    {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            $helper = Mage::helper("flexibletheme");
            $storeId = $this->getRequest()->getParam('store');
            try {
                $model = Mage::getModel("flexibletheme/footer");
                $id = $this->getRequest()->getParam($this->_idField);
                if ($id) {
                    $model->setStoreId($storeId)->load($id);
                }
                
                $reset = $model->getId() && ($this->getRequest()->getParam('reset') == 1);
                $export = ($this->getRequest()->getParam('export') == '1');
                
                $defaultData = $model->getDefaultData();
                if ($reset && count($defaultData)) {
                    $postData = $defaultData;
                } else {
                    $postData = $this->_encodeData($postData);
                    if (isset($postData['use_default'])) {
                        foreach($postData['use_default'] as $attributeCode) {
                            $postData[$attributeCode] =  false;
                        }
                    }
                }
                
                
                if ($reset) {
                    if (count($defaultData)) {
                        $message = $helper->__("All variables were reset to default values.");
                    } else {
                        $error = true;
                        $message = $helper->__("Default data is empty.");
                        throw new Exception($message);
                    }
                } elseif ($export) {
                    $message = $helper->__("This item was saved and exported successfully.");
                } else {
                    $message = $helper->__("This item was saved successfully.");
                }

                $model->addData($postData);
                $model->save();
                $this->_updateWorkspace($postData, $model, $export);
                Mage::getSingleton("adminhtml/session")->addSuccess($message);
                Mage::getSingleton("adminhtml/session")->setFlexiblethemeData(false);
                if ($this->getRequest()->getParam("back")) {
                    $params = array(
                        'id'        => $model->getId(),
                        '_current'  => true,
                        'back'      => false,
                        'export'    => false,
                        'reset'     => false
                    );
                    $this->_redirect("*/*/edit", $params);
                    return;
                }
                $this->_redirect("*/*/");
                return;
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setFlexiblethemeData($this->getRequest()->getPost());
                $params = array(
                    'id'        => $this->getRequest()->getParam("id", false),
                    '_current'  => true,
                    'back'      => false,
                    'export'    => false,
                    'reset'     => false
                );
                if ($this->getRequest()->getParam("id")) {
                    $this->_redirect("*/*/edit", $params);
                } else {
                    $this->_redirect("*/*/new", $params);
                }
                
                return;
            }
        }
        $this->_redirect("*/*/");
    }
    
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam($this->_idField)) {
            $model = Mage::getModel("flexibletheme/footer")->load($id);
            try {
                $model->delete();
                $this->_getSession()->addSuccess($this->__('This item has been deleted.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            $this->_redirect("*/*/");
        }
    }
    
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        
        $form = new Varien_Data_Form(array('id' => 'wysiwyg_edit_form', 'action' => null, 'method' => 'post'));
        $config = $this->getEditorConfig();
        
        //Variable Settings
        $settings = Mage::getModel('core/variable_config')->getWysiwygPluginSettings($config);
        $variableUrl = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
        $settings['plugins'][0]['options']['url'] = $variableUrl;
        $settings['plugins'][0]['options']['onclick']['subject'] = 'MagentovariablePlugin.loadChooser(\''.$variableUrl.'\', \'{{html_id}}\');';
        $config->addData($settings);
        
        //Widget Settings
        $settings = Mage::getModel('widget/widget_config')->getPluginSettings($config);
        $settings['widget_window_url'] = $this->getWidgetWindowUrl($config);
        $config->addData($settings);
        
        $form->addField($elementId, 'editor', array(
            'name'      => 'content',
            'style'     => 'width:725px;height:460px',
            'required'  => true,
            'force_load' => true,
            'config'    => $config
        ));
        $this->getResponse()->setBody($form->toHtml());	
    }
    
    public function getEditorConfig($data = array())
    {
        $config = new Varien_Object();
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $config->setData(array(
            'enabled'                       => true,
            'document_base_url'				=> Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA),
            'store_id'						=> $storeId,
            'add_directives'				=> true,
            'hidden'                        => false,
            'use_container'                 => false,
            'add_variables'                 => true,
            'add_widgets'                   => true,
            'no_display'                    => false,
            'translator'                    => Mage::helper('cms'),
            'encode_directives'             => true,
            'directives_url'                => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'),
            'container_class' 				=> 'hor-scroll',
            'use_container'					=> true,
            'popup_css'                     =>
                Mage::getBaseUrl('js').'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css',
            'content_css'                   =>
                Mage::getBaseUrl('js').'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css',
            'width'                         => '100%',
            'plugins'                       => array()
        ));
    
        $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));
    
        if (Mage::getSingleton('admin/session')->isAllowed('cms/media_gallery')) {
            $config->addData(array(
                'add_images'               => true,
                'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
                'files_browser_window_width'
                    => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'),
                'files_browser_window_height'
                    => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height'),
            ));
        }
    
        if (is_array($data)) {
            $config->addData($data);
        }
        return $config;
    }
    
    /* Widget Settings */
    public function getWidgetWindowUrl($config)
    {
        $params = array();

        $skipped = is_array($config->getData('skip_widgets')) ? $config->getData('skip_widgets') : array();
        if ($config->hasData('widget_filters')) {
            $all = Mage::getModel('widget/widget')->getWidgetsXml();
            $filtered = Mage::getModel('widget/widget')->getWidgetsXml($config->getData('widget_filters'));
            $reflection = new ReflectionObject($filtered);
            foreach ($all as $code => $widget) {
                if (!$reflection->hasProperty($code)) {
                    $skipped[] = $widget->getAttribute('type');
                }
            }
        }

        if (count($skipped) > 0) {
            $params['skip_widgets'] = $this->encodeWidgetsToQuery($skipped);
        }
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/widget/index', $params);
    }
    
    public function massRemoveAction()
    {
        try {
            $ids = $this->getRequest()->getPost('entity_ids', array());
            foreach ($ids as $id) {
                $model = Mage::getModel("flexibletheme/footer");
                $model->load($id)->delete();
            }
            Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
        } catch (Exception $e) {
            Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}