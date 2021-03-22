<?php

/**
 * Size model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Size extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'ave_sizechart_size';
    const CACHE_TAG = 'ave_sizechart_size';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'ave_sizechart_size';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'size';

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('ave_sizechart/size');
    }

    /**
     * before save size
     *
     * @access protected
     * @return Ave_SizeChart_Model_Size
     * @author averun <dev@averun.com>
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }

        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * Retrieve parent 
     *
     * @access public
     * @return null|Ave_SizeChart_Model_Chart
     * @author averun <dev@averun.com>
     */
    public function getParentChart()
    {
        if (!$this->hasData('_parent_chart')) {
            if (!$this->getChartId()) {
                return null;
            } else {
                $chart = Mage::getModel('ave_sizechart/chart')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getChartId());
                if ($chart->getId()) {
                    $this->setData('_parent_chart', $chart);
                } else {
                    $this->setData('_parent_chart', null);
                }
            }
        }

        return $this->getData('_parent_chart');
    }

    /**
     * Retrieve parent 
     *
     * @access public
     * @return null|Ave_SizeChart_Model_Dimension
     * @author averun <dev@averun.com>
     */
    public function getParentDimension()
    {
        if (!$this->hasData('_parent_dimension')) {
            if (!$this->getDimensionId()) {
                return null;
            } else {
                $dimension = Mage::getModel('ave_sizechart/dimension')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getDimensionId());
                if ($dimension->getId()) {
                    $this->setData('_parent_dimension', $dimension);
                } else {
                    $this->setData('_parent_dimension', null);
                }
            }
        }

        return $this->getData('_parent_dimension');
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     * @author averun <dev@averun.com>
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = 1;
        return $values;
    }
    
}
