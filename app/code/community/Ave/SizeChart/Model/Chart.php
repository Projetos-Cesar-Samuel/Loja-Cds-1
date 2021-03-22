<?php

/**
 * Chart model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Chart extends Mage_Catalog_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'ave_sizechart_chart';
    const CACHE_TAG = 'ave_sizechart_chart';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'ave_sizechart_chart';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'chart';

    protected $_chartId;

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
        $this->_init('ave_sizechart/chart');
    }

    public function getSizes()
    {
        if (empty($this->_chartId)) {
            return null;
        }

        $this->load($this->_chartId);
        if (($dimensionIds = $this->getData('dimension_id'))) {
            $dimensionIds = explode(',', $dimensionIds);
        }

        /** @var $sizeCollection Ave_SizeChart_Model_Resource_Size_Collection */
        $sizeCollection = Mage::getModel('ave_sizechart/size')
            ->getCollection()
            ->addFieldToFilter('chart_id', $this->_chartId)
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('dimension_id', array('in' => $dimensionIds))
            ->addFieldToSelect('name')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('dimension_id')
            ->addFieldToSelect('position')
            ->addOrder('position', Varien_Data_Collection::SORT_ORDER_ASC)
            ->load();
        return $sizeCollection->getData();
    }

    /**
     * before save chart
     *
     * @access protected
     * @return Ave_SizeChart_Model_Chart
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
     * get the chart Description
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getDescription()
    {
        $description = $this->getData('description');
        $helper = Mage::helper('cms');
        $processor = $helper->getBlockTemplateProcessor();
        $html = $processor->filter($description);
        return $html;
    }

    /**
     * Retrieve  collection
     *
     * @access public
     * @return Ave_SizeChart_Model_Size_Collection
     * @author averun <dev@averun.com>
     */
    public function getSelectedSizesCollection()
    {
        if (!$this->hasData('_size_collection')) {
            if (!$this->getId()) {
                return new Varien_Data_Collection();
            } else {
                $collection = Mage::getResourceModel('ave_sizechart/size_collection')
                        ->addFieldToFilter('chart_id', $this->getId());
                $this->setData('_size_collection', $collection);
            }
        }

        return $this->getData('_size_collection');
    }

    /**
     * Retrieve parent 
     *
     * @access public
     * @return null|Ave_SizeChart_Model_Category
     * @author averun <dev@averun.com>
     */
    public function getParentCategory()
    {
        if (!$this->hasData('_parent_category')) {
            if (!$this->getCategoryId()) {
                return null;
            } else {
                $category = Mage::getModel('ave_sizechart/category')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getCategoryId());
                if ($category->getId()) {
                    $this->setData('_parent_category', $category);
                } else {
                    $this->setData('_parent_category', null);
                }
            }
        }

        return $this->getData('_parent_category');
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
     * Retrieve parent 
     *
     * @access public
     * @return null|Ave_SizeChart_Model_Type
     * @author averun <dev@averun.com>
     */
    public function getParentType()
    {
        if (!$this->hasData('_parent_type')) {
            if (!$this->getTypeId()) {
                return null;
            } else {
                $type = Mage::getModel('ave_sizechart/type')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getTypeId());
                if ($type->getId()) {
                    $this->setData('_parent_type', $type);
                } else {
                    $this->setData('_parent_type', null);
                }
            }
        }

        return $this->getData('_parent_type');
    }

    /**
     * Retrieve default attribute set id
     *
     * @access public
     * @return int
     * @author averun <dev@averun.com>
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * get attribute text value
     *
     * @access public
     * @param $attributeCode
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getAttributeText($attributeCode)
    {
        $text = $this->getResource()
            ->getAttribute($attributeCode)
            ->getSource()
            ->getOptionText($this->getData($attributeCode));
        if (is_array($text)) {
            return implode(', ', $text);
        }

        return $text;
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
    
    /**
      * get Product Category
      *
      * @access public
      * @return array
      * @author averun <dev@averun.com>
      */
    public function getProductCategory()
    {
        if (!$this->getData('product_category')) {
            return explode(',', $this->getData('product_category'));
        }

        return $this->getData('product_category');
    }

    protected function _beforeLoad($id, $field = null)
    {
        $result = parent::_beforeLoad($id, $field);
        $this->_chartId = $id;
        return $result;
    }

    public function getSortSizes()
    {
        $items = $this->getSizes();
        $dimensions = $this->getDimensions();
        $sizes = array();
        $maxPosition = 0;
        if ($dimensions) {
            foreach ($dimensions as $dimension) {
                $sizes[$dimension['id']] = array('sizes' => array());
            }
        }

        if ($items) {
            foreach ($items as $item) {
                if (!isset($sizes[$item['dimension_id']])) {
                    continue;
                }

                if (empty($sizes[$item['dimension_id']])) {
                    $sizes[$item['dimension_id']] = array('sizes' => array());
                }

                $sizes[$item['dimension_id']]['sizes'][$item['position']] = array(
                    'name'     => $item['name'],
                    'id'       => $item['entity_id'],
                    'position' => $item['position']
                );
                if ($maxPosition < (int)$item['position']) {
                    $maxPosition = (int)$item['position'];
                }
            }
        }

        return array('sizes' => $sizes, 'dimensions' => $dimensions, 'maxSizeAmount' => $maxPosition);
    }

    /**
     * @return array
     */
    public function getDimensions()
    {
        if (empty($this->_chartId)) {
            return null;
        }

        $this->load($this->_chartId);
        $dimensions = array();
        if (($dimensionIds = $this->getData('dimension_id'))) {
            $dimensionIds = explode(',', $dimensionIds);
            $dimensionCollection = Mage::getResourceModel('ave_sizechart/dimension_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $dimensionIds))
                ->addAttributeToFilter('status', array('in' => 1))
                ->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC)
                ->load();
            foreach ($dimensionCollection as $dim) {
                $dimensions['dimension_' . $dim['entity_id']] = array(
                    'name'     => $dim['name'],
                    'id'       => $dim['entity_id'],
                    'main'     => $dim['main'],
                    'type'     => $dim['type'],
                    'priority' => $dim['priority']
                );
            }
        }

        return $dimensions;
    }
}
