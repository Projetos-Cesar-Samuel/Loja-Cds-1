<?php

/**
 * Size admin controller
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Adminhtml_Sizechart_SizeController extends Ave_SizeChart_Controller_Adminhtml_SizeChart
{
    /**
     * init the size
     *
     * @access protected
     * @return Ave_SizeChart_Model_Size
     */
    protected function _initSize()
    {
        $sizeId  = (int) $this->getRequest()->getParam('id');
        $size    = Mage::getModel('ave_sizechart/size');
        if ($sizeId) {
            $size->load($sizeId);
        }

        Mage::register('current_size', $size);
        return $size;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('ave_sizechart')->__('Size Chart'))
             ->_title(Mage::helper('ave_sizechart')->__('Sizes'));
        $this->renderLayout();
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
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit size - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function editAction()
    {
        $sizeId    = $this->getRequest()->getParam('id');
        $size      = $this->_initSize();
        if ($sizeId && !$size->getId()) {
            $this->_getSession()->addError(
                Mage::helper('ave_sizechart')->__('This size no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getSizeData(true);
        if (!empty($data)) {
            $size->setData($data);
        }

        Mage::register('size_data', $size);
        $this->loadLayout();
        $this->_title(Mage::helper('ave_sizechart')->__('Size Chart'))
             ->_title(Mage::helper('ave_sizechart')->__('Sizes'));
        if ($size->getId()) {
            $this->_title($size->getName());
        } else {
            $this->_title(Mage::helper('ave_sizechart')->__('Add size'));
        }

        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->renderLayout();
    }

    /**
     * new size action
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
     * save size - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('size')) {
            try {
                $size = $this->_initSize();
                $size->addData($data);
                $size->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('ave_sizechart')->__('Size was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $size->getId()));
                    return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setSizeData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was a problem saving the size.')
                );
                Mage::getSingleton('adminhtml/session')->setSizeData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('ave_sizechart')->__('Unable to find size to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete size - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $size = Mage::getModel('ave_sizechart/size');
                $size->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('ave_sizechart')->__('Size was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error deleting size.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('ave_sizechart')->__('Could not find size to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete size - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massDeleteAction()
    {
        $sizeIds = $this->getRequest()->getParam('size');
        if (!is_array($sizeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select sizes to delete.')
            );
        } else {
            try {
                Mage::getResourceModel('ave_sizechart/size_collection')
                    ->addFieldToFilter('entity_id', array('in' => $sizeIds))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('ave_sizechart')->__('Total of %d sizes were successfully deleted.', count($sizeIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error deleting sizes.')
                );
                Mage::logException($e);
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
        $sizeIds = $this->getRequest()->getParam('size');
        if (!is_array($sizeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select sizes.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/size_collection')
                    ->addFieldToFilter('entity_id', array('in' => $sizeIds));
                $collection->massUpdate(array('status' => $this->getRequest()->getParam('status')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d sizes were successfully updated.', count($sizeIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating sizes.')
                );
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * mass chart change - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massChartIdAction()
    {
        $sizeIds = $this->getRequest()->getParam('size');
        if (!is_array($sizeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select sizes.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/size_collection')
                    ->addFieldToFilter('entity_id', array('in' => $sizeIds));
                $collection->massUpdate(array('chart_id' => $this->getRequest()->getParam('flag_chart_id')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d sizes were successfully updated.', count($sizeIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating sizes.')
                );
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * mass dimension change - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massDimensionIdAction()
    {
        $sizeIds = $this->getRequest()->getParam('size');
        if (!is_array($sizeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select sizes.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/size_collection')
                    ->addFieldToFilter('entity_id', array('in' => $sizeIds));
                $collection->massUpdate(array('dimension_id' => $this->getRequest()->getParam('flag_dimension_id')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d sizes were successfully updated.', count($sizeIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating sizes.')
                );
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportCsvAction()
    {
        $fileName   = 'size.csv';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_size_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportExcelAction()
    {
        $fileName   = 'size.xls';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_size_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportXmlAction()
    {
        $fileName   = 'size.xml';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_size_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author averun <dev@averun.com>
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/ave_sizechart/size');
    }
}
