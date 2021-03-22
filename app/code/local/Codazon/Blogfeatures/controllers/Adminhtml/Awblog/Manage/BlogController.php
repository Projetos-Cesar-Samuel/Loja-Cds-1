<?php
require_once "AW/Blog/controllers/Adminhtml/Awblog/Manage/BlogController.php";  
class Codazon_Blogfeatures_Adminhtml_Awblog_Manage_BlogController extends AW_Blog_Adminhtml_Awblog_Manage_BlogController{
	public function saveAction()
    {		
		
		try { 
			$mediaDir = Mage::getBaseDir('media');
			$model = Mage::getModel('blog/post');
			$request = $this->getRequest();
			if ($request->getParam('id')) {
				$model->load($request->getParam('id'));	
			}
			
			if(isset($_FILES['post_image']['name']) && $_FILES['post_image']['name'] != ''){
				
				try{
					$fileName = $mediaDir.DS.$model->getData('post_image');
					if(file_exists($fileName)){
						unlink($fileName);
					}
				}catch(Exception $e){
					 echo $e->getMessage();
				}
				$uploader = new Varien_File_Uploader('post_image');  
				$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));  
				$uploader->setAllowRenameFiles(false);  
				$uploader->setFilesDispersion(false); 
				
				$mediaPath  = $mediaDir . DS . 'codazon_blog' . DS;
				$randFileName = 'codazon_blog_'. $this->getRandFilename($_FILES['post_image']['name']);
				$uploader->save($mediaPath, $randFileName);
				$data['post_image'] = 'codazon_blog' . '/' . $randFileName;  
				
			}else {  
                $data = $request->getPost();  
                if(isset($data['post_image']['delete']) && $data['post_image']['delete']== 1) {  
					$fileName = $mediaDir . DS . $model->getData('post_image');
					unlink($fileName);
					$data['post_image'] = '';  
                } else {  
					$data['post_image'] = $model->getData('post_image');
                }  
            }  
			$request->setPost('post_image',$data['post_image']);
		}catch (Exception $e) {  
            echo $e->getMessage();
        }
		$stores = $request->getPost('stores');
		if($stores[0] == 0){
			$last = count($stores);
			$stores = array(0);
			$request->setPost('stores',$stores);
		}
		if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('blog/post');
            if (isset($data['tags'])) {
                if ($this->getRequest()->getParam('id')) {
                    $model->load($this->getRequest()->getParam('id'));
                    $originalTags = explode(",", $model->getTags());
                } else {
                    $originalTags = array();
                }

                $tags = explode(',', $data['tags']);
                array_walk($tags, 'trim');

                foreach ($tags as $key => $tag) {
                    $tags[$key] = Mage::helper('blog')->convertSlashes($tag, 'forward');
                }
                $tags = array_unique($tags);

                $commonTags = array_intersect($tags, $originalTags);
                $removedTags = array_diff($originalTags, $commonTags);
                $addedTags = array_diff($tags, $commonTags);

                if (count($tags)) {
                    $data['tags'] = trim(implode(',', $tags));
                } else {
                    $data['tags'] = '';
                }
            }
            /* if (isset($data['stores'])) {
                if ($data['stores'][0] == 0) {
                    unset($data['stores']);
                    $data['stores'] = array();
                    $stores = Mage::getSingleton('adminhtml/system_store')->getStoreCollection();
                    foreach ($stores as $store) {
                        $data['stores'][] = $store->getId();
                    }
                }
            } */

            $model
                ->setData($data)
                ->setId($this->getRequest()->getParam('id'))
            ;

            try {
                $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                if (isset($data['created_time']) && $data['created_time']) {
                    $dateFrom = Mage::app()->getLocale()->date($data['created_time'], $format);
                    $model->setCreatedTime(Mage::getModel('core/date')->gmtDate(null, $dateFrom->getTimestamp()));
                    $model->setUpdateTime(Mage::getModel('core/date')->gmtDate());
                } else {
                    $model->setCreatedTime(Mage::getModel('core/date')->gmtDate());
                }

                if ($this->getRequest()->getParam('user') == null) {
                    $model
                        ->setUser(
                            Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
                                'admin/session'
                            )->getUser()->getLastname()
                        )
                        ->setUpdateUser(
                            Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
                                'admin/session'
                            )->getUser()->getLastname()
                        )
                    ;
                } else {
                    $model
                        ->setUpdateUser(
                            Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
                                'admin/session'
                            )->getUser()->getLastname()
                        )
                    ;
                }

                $model->save();

                /* recount affected tags */
                if (isset($data['stores'])) {
                    $stores = $data['stores'];
                } else {
                    $stores = array(null);
                }

                $affectedTags = array_merge($addedTags, $removedTags);

                foreach ($affectedTags as $tag) {
                    foreach ($stores as $store) {
                        if (trim($tag)) {
                            Mage::getModel('blog/tag')->loadByName($tag, $store)->refreshCount();
                        }
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('blog')->__('Post was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find post to save'));
        $this->_redirect('*/*/');
		
		
	}
	public function getRandFilename($baseFileName) {   
        $ext = pathinfo($baseFileName, PATHINFO_EXTENSION);
		//$fileName = pathinfo($baseFileName, PATHINFO_FILENAME);
        return Mage::getModel('core/date')->timestamp(time()) . '.' . $ext;  
    }
}
				