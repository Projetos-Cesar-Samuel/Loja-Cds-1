<?php

/**
 * Category of sizes admin controller
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Adminhtml_Sizechart_CategoryController extends Mage_Adminhtml_Controller_Action
{
    /**
     * constructor - set the used module name
     *
     * @access protected
     * @return void
     * @see Mage_Core_Controller_Varien_Action::_construct()
     * @author averun <dev@averun.com>
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Ave_SizeChart');
    }

    /**
     * init the category of sizes
     *
     * @access protected 
     * @return Ave_SizeChart_Model_Category
     * @author averun <dev@averun.com>
     */
    protected function _initCategory()
    {
        $this->_title($this->__('Size Chart'))
             ->_title($this->__('Manage Categories of sizes'));

        $categoryId  = (int) $this->getRequest()->getParam('id');
        $category    = Mage::getModel('ave_sizechart/category')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if ($categoryId) {
            $category->load($categoryId);
        }

        Mage::register('current_category', $category);
        return $category;
    }

    /**
     * default action for category controller
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function indexAction()
    {
        $this->_title($this->__('Size Chart'))
             ->_title($this->__('Manage Categories of sizes'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * new category action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * edit category action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function editAction()
    {
        $categoryId  = (int) $this->getRequest()->getParam('id');
        $category    = $this->_initCategory();
        if ($categoryId && !$category->getId()) {
            $this->_getSession()->addError(
                Mage::helper('ave_sizechart')->__('This category of sizes no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        if ($data = Mage::getSingleton('adminhtml/session')->getCategoryData(true)) {
            $category->setData($data);
        }

        $this->_title($category->getName());
        Mage::dispatchEvent(
            'ave_sizechart_category_edit_action',
            array('category' => $category)
        );
        $this->loadLayout();
        if ($category->getId()) {
            if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
                $switchBlock->setDefaultStoreName(Mage::helper('ave_sizechart')->__('Default Values'))
                    ->setWebsiteIds($category->getWebsiteIds())
                    ->setSwitchUrl(
                        $this->getUrl(
                            '*/*/*',
                            array(
                                '_current'=>true,
                                'active_tab'=>null,
                                'tab' => null,
                                'store'=>null
                            )
                        )
                    );
            }
        } else {
            $this->getLayout()->getBlock('left')->unsetChild('store_switcher');
        }

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    /**
     * save category of sizes action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $categoryId   = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPost();
        if ($data) {
            $category     = $this->_initCategory();
            $categoryData = $this->getRequest()->getPost('category', array());
            $category->addData($categoryData);
            $category->setAttributeSetId($category->getDefaultAttributeSetId());
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $category->setData($attributeCode, false);
                }
            }

            try {
                $category->save();
                $categoryId = $category->getId();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('Category of sizes was saved')
                );
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage())
                    ->setCategoryData($categoryData);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    Mage::helper('ave_sizechart')->__('Error saving category of sizes')
                )
                ->setCategoryData($categoryData);
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect(
                '*/*/edit',
                array(
                    'id'    => $categoryId,
                    '_current'=>true
                )
            );
        } else {
            $this->_redirect('*/*/', array('store'=>$storeId));
        }
    }

    /**
     * delete category of sizes
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $category = Mage::getModel('ave_sizechart/category')->load($id);
            try {
                $category->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('The categories of sizes has been deleted.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->getResponse()->setRedirect(
            $this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store')))
        );
    }

    /**
     * mass delete categories of sizes
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massDeleteAction()
    {
        $categoryIds = $this->getRequest()->getParam('category');
        if (!is_array($categoryIds)) {
            $this->_getSession()->addError($this->__('Please select categories of sizes.'));
        } else {
            try {
                Mage::getResourceModel('ave_sizechart/category_collection')
                    ->addFieldToFilter('entity_id', array('in' => $categoryIds))
                    ->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('Total of %d record(s) have been deleted.', count($categoryIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massStatusAction()
    {
        $categoryIds = $this->getRequest()->getParam('category');
        if (!is_array($categoryIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select categories of sizes.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/category_collection')
                    ->addFieldToFilter('entity_id', array('in' => $categoryIds));
                $collection->massUpdate(array('status' => $this->getRequest()->getParam('status')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d categories of sizes were successfully updated.', count($categoryIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating categories of sizes.')
                );
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * restrict access
     *
     * @access protected
     * @return bool
     * @see Mage_Adminhtml_Controller_Action::_isAllowed()
     * @author averun <dev@averun.com>
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/ave_sizechart/category');
    }

    /**
     * Export categories in CSV format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportCsvAction()
    {
        $fileName   = 'categories.csv';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_category_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export categories of sizes in Excel format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportExcelAction()
    {
        $fileName   = 'category.xls';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_category_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export categories of sizes in XML format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportXmlAction()
    {
        $fileName   = 'category.xml';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_category_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * wysiwyg editor action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function wysiwygAction()
    {
        $elementId     = $this->getRequest()->getParam('element_id', sha1(microtime()));
        $storeId       = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock(
            'ave_sizechart/adminhtml_sizechart_helper_form_wysiwyg_content',
            '',
            array(
                'editor_element_id' => $elementId,
                'store_id'          => $storeId,
                'store_media_url'   => $storeMediaUrl,
            )
        );
        $this->getResponse()->setBody($content->toHtml());
    }
}
