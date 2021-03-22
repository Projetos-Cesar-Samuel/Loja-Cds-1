<?php

/**
 * Chart admin controller
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Adminhtml_Sizechart_ChartController extends Mage_Adminhtml_Controller_Action
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
     * init the chart
     *
     * @access protected 
     * @return Ave_SizeChart_Model_Chart
     * @author averun <dev@averun.com>
     */
    protected function _initChart()
    {
        $this->_title($this->__('Size Chart'))
             ->_title($this->__('Manage Charts'));

        $chartId  = (int) $this->getRequest()->getParam('id');
        $chart    = Mage::getModel('ave_sizechart/chart')
            ->setStoreId($this->getRequest()->getParam('store', 0));

        if ($chartId) {
            $chart->load($chartId);
        }

        Mage::register('current_chart', $chart);
        return $chart;
    }

    /**
     * default action for chart controller
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function indexAction()
    {
        $this->_title($this->__('Size Chart'))
             ->_title($this->__('Manage Charts'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * new chart action
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
     * edit chart action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function editAction()
    {
        $chartId  = (int) $this->getRequest()->getParam('id');
        $chart    = $this->_initChart();
        if ($chartId && !$chart->getId()) {
            $this->_getSession()->addError(
                Mage::helper('ave_sizechart')->__('This chart no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        if ($data = Mage::getSingleton('adminhtml/session')->getChartData(true)) {
            $chart->setData($data);
        }

        $this->_title($chart->getName());
        Mage::dispatchEvent(
            'ave_sizechart_chart_edit_action',
            array('chart' => $chart)
        );
        $this->loadLayout();
        if ($chart->getId()) {
            if (!Mage::app()->isSingleStoreMode() && ($switchBlock = $this->getLayout()->getBlock('store_switcher'))) {
                $switchBlock->setDefaultStoreName(Mage::helper('ave_sizechart')->__('Default Values'))
                    ->setWebsiteIds($chart->getWebsiteIds())
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
     * save chart action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function saveAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);
        $chartId = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPost();
        if ($data) {
            $chart     = $this->_initChart();
            $chartData = $this->getRequest()->getPost('chart', array());
            $chart->addData($chartData);
            $chart->setAttributeSetId($chart->getDefaultAttributeSetId());
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $chart->setData($attributeCode, false);
                }
            }

            try {
                $chart->save();
                $chartId = $chart->getId();
                $this->saveDependencySizes($chartId);
                $this->saveDependencyCategory($chartId, $chartData['product_category']);
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('Chart was saved')
                );
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage())
                    ->setChartData($chartData);
                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError(
                    Mage::helper('ave_sizechart')->__('Error saving chart')
                )
                ->setChartData($chartData);
                $redirectBack = true;
            }
        }

        if ($redirectBack) {
            $this->_redirect(
                '*/*/edit',
                array(
                    'id'    => $chartId,
                    '_current'=>true
                )
            );
        } else {
            $this->_redirect('*/*/', array('store'=>$storeId));
        }
    }

    /**
     * delete chart
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $chart = Mage::getModel('ave_sizechart/chart')->load($id);
            try {
                $chart->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('The charts has been deleted.')
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
     * mass delete charts
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massDeleteAction()
    {
        $chartIds = $this->getRequest()->getParam('chart');
        if (!is_array($chartIds)) {
            $this->_getSession()->addError($this->__('Please select charts.'));
        } else {
            try {
                Mage::getResourceModel('ave_sizechart/chart_collection')
                    ->addFieldToFilter('entity_id', array('in' => $chartIds))
                    ->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('ave_sizechart')->__('Total of %d record(s) have been deleted.', count($chartIds))
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
        $chartIds = $this->getRequest()->getParam('chart');
        if (!is_array($chartIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select charts.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/chart_collection')
                    ->addFieldToFilter('entity_id', array('in' => $chartIds));
                $collection->massUpdate(array('status' => $this->getRequest()->getParam('status')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d charts were successfully updated.', count($chartIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating charts.')
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
        return Mage::getSingleton('admin/session')->isAllowed('catalog/ave_sizechart/chart');
    }

    /**
     * Export charts in CSV format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportCsvAction()
    {
        if (!Mage::registry('csvExport')) {
            Mage::register('csvExport', 1);
        }
        $fileName   = 'charts.csv';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_chart_grid')
            ->getCsvFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export charts in Excel format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportExcelAction()
    {
        $fileName   = 'chart.xls';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_chart_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export charts in XML format
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function exportXmlAction()
    {
        $fileName   = 'chart.xml';
        $content    = $this->getLayout()->createBlock('ave_sizechart/adminhtml_chart_grid')
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
     * mass category of sizes change - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massCategoryIdAction()
    {
        $chartIds = $this->getRequest()->getParam('chart');
        if (!is_array($chartIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select charts.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/chart_collection')
                    ->addFieldToFilter('entity_id', array('in' => $chartIds));
                $collection->massUpdate(array('category_id' => $this->getRequest()->getParam('flag_category_id')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d charts were successfully updated.', count($chartIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating charts.')
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
        $chartIds = $this->getRequest()->getParam('chart');
        if (!is_array($chartIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select charts.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/chart_collection')
                    ->addFieldToFilter('entity_id', array('in' => $chartIds));
                $collection->massUpdate(array('dimension_id' => $this->getRequest()->getParam('flag_dimension_id')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d charts were successfully updated.', count($chartIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating charts.')
                );
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * mass type change - action
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function massTypeIdAction()
    {
        $chartIds = $this->getRequest()->getParam('chart');
        if (!is_array($chartIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('ave_sizechart')->__('Please select charts.')
            );
        } else {
            try {
                $collection = Mage::getResourceModel('ave_sizechart/chart_collection')
                    ->addFieldToFilter('entity_id', array('in' => $chartIds));
                $collection->massUpdate(array('type_id' => $this->getRequest()->getParam('flag_type_id')));
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d charts were successfully updated.', count($chartIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ave_sizechart')->__('There was an error updating charts.')
                );
                Mage::logException($e);
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function saveDependencyCategory($chartId, $categoryIds = array())
    {
        $transaction = Mage::getModel('core/resource_transaction');
        $existsCategories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToFilter('ave_size_chart', $chartId)
            ->load();
        foreach ($existsCategories as $category) {
            if (empty($categoryIds) || !in_array($category->getId(), $categoryIds)) {
                $category->setData('ave_size_chart');
                $transaction->addObject($category);
            }
        }

        if (!empty($categoryIds)) {
            $collection = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('entity_id', array('in' => $categoryIds));
            foreach ($collection as $item) {
                $item->setData('ave_size_chart', $chartId);
                $transaction->addObject($item);
            }
        }

        $transaction->save();
        return $this;
    }

    protected function saveDependencySizes($chartId)
    {
        Mage::getResourceModel('ave_sizechart/size_collection')->addFieldToFilter('chart_id', $chartId)->massDelete();
        $transaction = Mage::getModel('core/resource_transaction');
        $sizes = $this->getRequest()->getPost('size', array());
        $sortByPositions = $this->getRequest()->getPost('position', array());

        function setValue(&$value, $key, &$keys)
        {
            $index = array_search($value, $keys);
            $value = $index;
            unset($keys[$index]);
        }

        $a = $sortByPositions;
        $sortedA = $a;
        $neededSort = $a;
        sort($sortedA);
        array_walk($neededSort, "setValue", $sortedA);

        foreach ($sizes as $dimensionId => $positions) {
            foreach ($positions as $position => $name) {
                if (null === $name) {
                    continue;
                }

                $sizeData = array(
                    'name'         => $name,
                    'chart_id'     => $chartId,
                    'dimension_id' => $dimensionId,
                    'position'     => $neededSort[$position],
                    'status'       => '1'
                );
                $sizeModel = Mage::getModel('ave_sizechart/size');
                $sizeModel->setData($sizeData);
                $transaction->addObject($sizeModel);
            }
        }

        $transaction->save();
    }
}
