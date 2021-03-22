<?php
class Codazon_Megamenupro_Adminhtml_MegamenuproController extends Mage_Adminhtml_Controller_Action
{
		protected function _isAllowed()
		{
		//return Mage::getSingleton('admin/session')->isAllowed('megamenupro/megamenupro');
			return true;
		}

		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("megamenupro/megamenupro")->_addBreadcrumb(Mage::helper("adminhtml")->__("Megamenupro  Manager"),Mage::helper("adminhtml")->__("Megamenupro Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Megamenupro"));
			    $this->_title($this->__("Manager Megamenupro"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Megamenupro"));
				$this->_title($this->__("Megamenupro Manager"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("megamenupro/megamenupro")->load($id);
				if ($model->getId()) {
					Mage::register("megamenupro_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("megamenupro/megamenupro");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Megamenupro Manager"), Mage::helper("adminhtml")->__("Megamenupro Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Megamenupro Description"), Mage::helper("adminhtml")->__("Megamenupro Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("megamenupro")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{
			$this->_title($this->__("Megamenupro"));
			$this->_title($this->__("Megamenupro"));
			$this->_title($this->__("New Item"));
	
			$id   = $this->getRequest()->getParam("id");
			$model  = Mage::getModel("megamenupro/megamenupro")->load($id);
	
			$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
	
			Mage::register("megamenupro_data", $model);
	
			$this->loadLayout();
			$this->_setActiveMenu("megamenupro/megamenupro");	
			$this->renderLayout();

		}
		
		public function wysiwygAction(){
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

		
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {
					try {
						$stylesVars = array('css_class','dropdown_animation','dropdown_style');
						$style = array();
						foreach($stylesVars as $stylesVar){
							if(isset($post_data[$stylesVar])){
								$style[$stylesVar] = $post_data[$stylesVar];
							}
						}
						$post_data['style'] = json_encode($style);
						$model = Mage::getModel("megamenupro/megamenupro")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Megamenupro was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setMegamenuproData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						if ($this->getRequest()->getParam("duplicate")) {
							$post_data['title'] = 'Copy of '.$post_data['title'];
							$post_data['identifier'] = 'copy-of-'.$post_data['identifier'].'-'.uniqid();
							$newMenu = Mage::getModel("megamenupro/megamenupro")
								->addData($post_data)->save();
							$this->_redirect("*/*/edit", array("id" => $newMenu->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setMegamenuproData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("megamenupro/megamenupro");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('menu_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("megamenupro/megamenupro");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'megamenupro.csv';
			$grid       = $this->getLayout()->createBlock('megamenupro/adminhtml_megamenupro_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'megamenupro.xml';
			$grid       = $this->getLayout()->createBlock('megamenupro/adminhtml_megamenupro_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
		public function exportMenuDataAction(){
			$io = new Varien_Io_File();
			$path = Mage::getModuleDir('', 'Codazon_Megamenupro').DS.'fixtures'.DS;
			if(!file_exists($path)){
				$io->mkdir($path);	
			}
			$grid       = $this->getLayout()->createBlock('megamenupro/adminhtml_megamenupro_exportcsv');
			$fileName   = 'megamenupro.csv';
			$csv = $grid->getCsv();
			$file = $path . DS . $fileName;
			$io->setAllowCreateFolders(true);
			$io->open(array('path' => $path));
			$io->streamOpen($file, 'w+');
			$io->streamWrite($csv);
			$io->streamClose();
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		}
}
