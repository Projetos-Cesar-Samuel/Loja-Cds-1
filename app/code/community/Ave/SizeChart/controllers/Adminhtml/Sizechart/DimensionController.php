<?php

/**
 * Dimension admin controller
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Adminhtml_Sizechart_DimensionController extends Mage_Adminhtml_Controller_Action
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
     * init the dimension
     *
     * @access protected 
     * @return Ave_SizeChart_Model_Dimension
     * @author averun <dev@averun.com>
     */
    protected function _initDimension()
    {
        $this->_title($this->__('Size Chart'))
             ->_title($this->__('Manage Dimensions'));

        $dimensionId  = (int) $this->getRequest()->getParam('id');
        $dimension    = Mage::getModel('ave_sizechart/dimension')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if ($dimensionId) {
            $dimension->load($dimensionId);
        }

        Mage::register('current_dimension', $dimension);
        return $dimension;
    }

    /**
     * default action for dimension controller
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function indexAction()
    {
        $this->_title($this->__('Size Chart'))
             ->_title($this->__('Manage Dimensions'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * new dimension action
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
     * edit dimension action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function editAction()
    {
        $dimensionId  = (int) $this->getRequest()->getParam('id');
        $dimension    = $this->_initDimension();
        if ($dimensionId && !$dimension->getId()) {
            $this->_getSession()->addError(
                Mage::helper('ave_sizechart')->__('This dimension no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        if ($data = Mage::getSingleton('adminhtml/session')->getDimensionData(true)) {
            $dimension->setData($data);
        }

        $this->_title($dimension->getName());
        Mage::dispatchEvent(
            'ave_sizechart_dimension_edit_action',
            array('dimension' => $dimension)
        );
        $this->loadLayout();
        if ($dimension->getId()) {
            if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
                $switchBlock->setDefaultStoreName(Mage::helper('ave_sizechart')->__('Default Values'))
                    ->setWebsiteIds($dimension->getWebsiteIds())
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
     * save dimension action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function saveAction()
    {
        $storeId        = $this->getRequest()->getParam('store');
        $redirectBack   = $this->getRequest()->getParam('back', false);
        $dimensionId   = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPost();
        if ($data) {
            $dimension     = $this->_initDimension();
            $dimensionData = $this->getRequest()->getPost('dimension', array());
            $dimension->addData($dimensionData);
            $dimension->setAttributeSetId($dimension->getDefaultAttributeSetId());
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $dimension->setData($attributeCode, false);
                }
            }

            try {
                $dimension->save();
                $dimensionId = $dimension->getId();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('Dimension was saved')
                );
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage())
                    ->setDimensionData($dimensionData);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    Mage::helper('ave_sizechart')->__('Error saving dimension')
                )
                ->setDimensionData($dimensionData);
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect(
                '*/*/edit',
                array(
                    'id'    => $dimensionId,
                    '_current'=>true
                )
            );
        } else {
            $this->_redirect('*/*/', array('store'=>$storeId));
        }
    }

    /**
     * delete dimension
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $dimension = Mage::getModel('ave_sizechart/dimension')->load($id);
            try {
                $dimension->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('The dimensions has been deleted.')
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
     * mass delete dimensions
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massDeleteAction()
    {
        $dimensionIds = $this->getRequest()->getParam('dimension');
        if (!is_array($dimensionIds)) {
            $this->_getSession()->addError($this->__('Please select dimensions.'));
        } else {
            try {
                Mage::getResourceModel('ave_sizechart/dimension_collection')
                    ->addFieldToFilter('entity_id', array('in' => $dimensionIds))
                    ->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('Total of %d record(s) have been deleted.', count($dimensionIds))
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
        $dimensionIds = $this->getRequest()->getParam('dimension');
        if (!is_array($dimensionIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select dimensions.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/dimension_collection')
                    ->addFieldToFilter('entity_id', array('in' => $dimensionIds));
                $collection->massUpdate(array('status' => $this->getRequest()->getParam('status')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d dimensions were successfully updated.', count($dimensionIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating dimensions.')
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
        return Mage::getSingleton('admin/session')->isAllowed('catalog/ave_sizechart/dimension');
    }

    /**
     * Export dimensions in CSV format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportCsvAction()
    {
        $fileName   = 'dimensions.csv';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_dimension_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export dimensions in Excel format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportExcelAction()
    {
        $fileName   = 'dimension.xls';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_dimension_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export dimensions in XML format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportXmlAction()
    {
        $fileName   = 'dimension.xml';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_dimension_grid')
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

    /**
     * mass Type change
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massTypeAction()
    {
        $dimensionIds = (array)$this->getRequest()->getParam('dimension');
        $storeId       = (int)$this->getRequest()->getParam('store', 0);
        $flag          = (int)$this->getRequest()->getParam('flag_type');
        if ($flag == 2) {
            $flag = 0;
        }

        try {
            $collection = Mage::getResourceModel('ave_sizechart/dimension_collection')
                ->addFieldToFilter('entity_id', array('in' => $dimensionIds));
            $collection->massUpdate(array('type' => $flag));
            $this->_getSession()->addSuccess(
                Mage::helper('ave_sizechart')->__('Total of %d record(s) have been updated.', count($dimensionIds))
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e,
                Mage::helper('ave_sizechart')->__('An error occurred while updating the dimensions.')
            );
        }

        $this->_redirect('*/*/', array('store'=> $storeId));
    }
}
