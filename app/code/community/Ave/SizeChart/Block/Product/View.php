<?php

class Ave_SizeChart_Block_Product_View extends Mage_Core_Block_Template
{

    protected $_template = 'ave/sizechart/view.phtml';

    public function getMembers()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return array();
        }

        return Mage::getModel('ave_sizechart/member')->getCustomerMembers();
    }

    public function getMembersMeasurements()
    {
        return $this->getFrontendHelper()->getMembersMeasurements();
    }

    public function getChart()
    {
        $chartId = $this->getChartId();     //get as parameter to template
        return $this->getFrontendHelper()->getChart($chartId);
    }

    /**
     * @param $chart
     * @return array
     */
    public function generateBodySizes($chart)
    {
        return $this->getFrontendHelper()->getGeneratedBodySizes($chart);
    }

    /**
     * @param $chartId
     * @return array|mixed|string
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getChartById($chartId)
    {
        return $this->getFrontendHelper()->getChartById($chartId);
    }

    public function getDimensionList()
    {
        return $this->getFrontendHelper()->getDimensionList();
    }

    public function getDefaultDimension()
    {
        return $this->getFrontendHelper()->getDefaultDimension();
    }

    /**
     * @param $description
     * @return string
     */
    public function getDescription($description)
    {
        $helper = Mage::helper('cms');
        try {
            $processor = $helper->getPageTemplateProcessor();
            $description = $processor->filter($description);
        } catch (\Exception $e) {
            $description = '';
        }
        return $description;
    }

    public function getAveSessionData()
    {
        return $this->getFrontendHelper()->getSessionData();
    }

    /**
     * @return Ave_SizeChart_Helper_Frontend_Data|Mage_Core_Helper_Abstract
     */
    protected function getFrontendHelper()
    {
        return Mage::helper('ave_sizechart/frontend_data');
    }

    protected function _toHtml()
    {
        if (($currentProduct = Mage::registry('current_product'))
            && $currentProduct->getData('ave_size_chart') == '-1') {
            return '';
        } else {
            return parent::_toHtml();
        }
    }

}
 
