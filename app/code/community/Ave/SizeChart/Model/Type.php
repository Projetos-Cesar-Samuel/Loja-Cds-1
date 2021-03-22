<?php

/**
 * Type model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Type extends Mage_Catalog_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'ave_sizechart_type';
    const CACHE_TAG = 'ave_sizechart_type';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'ave_sizechart_type';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'type';

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
        $this->_init('ave_sizechart/type');
    }

    /**
     * before save type
     *
     * @access protected
     * @return Ave_SizeChart_Model_Type
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
     * Retrieve  collection
     *
     * @access public
     * @return Ave_SizeChart_Model_Chart_Collection
     * @author averun <dev@averun.com>
     */
    public function getSelectedChartsCollection()
    {
        if (!$this->hasData('_chart_collection')) {
            if (!$this->getId()) {
                return new Varien_Data_Collection();
            } else {
                $collection = Mage::getResourceModel('ave_sizechart/chart_collection')->addAttributeToSelect('*')
                        ->addAttributeToFilter('type_id', $this->getId());
                $this->setData('_chart_collection', $collection);
            }
        }

        return $this->getData('_chart_collection');
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
    
}
