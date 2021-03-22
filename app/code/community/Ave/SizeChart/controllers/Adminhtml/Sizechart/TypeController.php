<?php

/**
 * Type admin controller
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Adminhtml_Sizechart_TypeController extends Mage_Adminhtml_Controller_Action
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
     * init the type
     *
     * @access protected 
     * @return Ave_SizeChart_Model_Type
     * @author averun <dev@averun.com>
     */
    protected function _initType()
    {
        $this->_title($this->__('Size Chart'))
             ->_title($this->__('Manage Types'));

        $typeId  = (int) $this->getRequest()->getParam('id');
        $type    = Mage::getModel('ave_sizechart/type')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if ($typeId) {
            $type->load($typeId);
        }

        Mage::register('current_type', $type);
        return $type;
    }

    /**
     * default action for type controller
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function indexAction()
    {
        $this->_title($this->__('Size Chart'))
             ->_title($this->__('Manage Types'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * new type action
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
     * edit type action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function editAction()
    {
        $typeId  = (int) $this->getRequest()->getParam('id');
        $type    = $this->_initType();
        if ($typeId && !$type->getId()) {
            $this->_getSession()->addError(
                Mage::helper('ave_sizechart')->__('This type no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        if ($data = Mage::getSingleton('adminhtml/session')->getTypeData(true)) {
            $type->setData($data);
        }

        $this->_title($type->getName());
        Mage::dispatchEvent(
            'ave_sizechart_type_edit_action',
            array('type' => $type)
        );
        $this->loadLayout();
        if ($type->getId()) {
            if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
                $switchBlock->setDefaultStoreName(Mage::helper('ave_sizechart')->__('Default Values'))
                    ->setWebsiteIds($type->getWebsiteIds())
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
     * save type action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $typeId   = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPost();
        if ($data) {
            $type     = $this->_initType();
            $typeData = $this->getRequest()->getPost('type', array());
            $type->addData($typeData);
            $type->setAttributeSetId($type->getDefaultAttributeSetId());
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $type->setData($attributeCode, false);
                }
            }

            try {
                $type->save();
                $typeId = $type->getId();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('Type was saved')
                );
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage())
                    ->setTypeData($typeData);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    Mage::helper('ave_sizechart')->__('Error saving type')
                )
                ->setTypeData($typeData);
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect(
                '*/*/edit',
                array(
                    'id'    => $typeId,
                    '_current'=>true
                )
            );
        } else {
            $this->_redirect('*/*/', array('store'=>$storeId));
        }
    }

    /**
     * delete type
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $type = Mage::getModel('ave_sizechart/type')->load($id);
            try {
                $type->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('The types has been deleted.')
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
     * mass delete types
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massDeleteAction()
    {
        $typeIds = $this->getRequest()->getParam('type');
        if (!is_array($typeIds)) {
            $this->_getSession()->addError($this->__('Please select types.'));
        } else {
            try {
                Mage::getResourceModel('ave_sizechart/type_collection')
                    ->addFieldToFilter('entity_id', array('in' => $typeIds))
                    ->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('Total of %d record(s) have been deleted.', count($typeIds))
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
        $typeIds = $this->getRequest()->getParam('type');
        if (!is_array($typeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select types.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/type_collection')
                    ->addFieldToFilter('entity_id', array('in' => $typeIds));
                $collection->massUpdate(array('status' => $this->getRequest()->getParam('status')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d types were successfully updated.', count($typeIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating types.')
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
        return Mage::getSingleton('admin/session')->isAllowed('catalog/ave_sizechart/type');
    }

    /**
     * Export types in CSV format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportCsvAction()
    {
        $fileName   = 'types.csv';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_type_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export types in Excel format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportExcelAction()
    {
        $fileName   = 'type.xls';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_type_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export types in XML format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportXmlAction()
    {
        $fileName   = 'type.xml';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_type_grid')
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
