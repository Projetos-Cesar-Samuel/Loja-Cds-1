<?php

/**
 * attribute model
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    const SCOPE_STORE       = 0;
    const SCOPE_GLOBAL      = 1;
    const SCOPE_WEBSITE     = 2;

    const MODULE_NAME       = 'Ave_SizeChart';
    const ENTITY            = 'ave_sizechart_eav_attribute';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ave_sizechart_entity_attribute';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'attribute';

    /**
     * Array with labels
     *
     * @var array
     */
    static protected $_labels = null;

    /**
     * constructor
     *
     * @access protected
     * @return void
     * @author averun <dev@averun.com>
     */
    protected function _construct()
    {
        $this->_init('ave_sizechart/attribute');
    }

    /**
     * Processing object before save data
     *
     * @access protected
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Abstract
     * @author averun <dev@averun.com>
     */
    protected function _beforeSave()
    {
        $this->setData('modulePrefix', self::MODULE_NAME);
        if (isset($this->_origData['is_global'])) {
            if (!isset($this->_data['is_global'])) {
                $this->_data['is_global'] = self::SCOPE_GLOBAL;
            }
        }

        if ($this->getFrontendInput() == 'textarea') {
            if ($this->getIsWysiwygEnabled()) {
                $this->setIsHtmlAllowedOnFront(1);
            }
        }

        return parent::_beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @access protected
     * @return Mage_Core_Model_Abstract
     * @author averun <dev@averun.com>
     */
    protected function _afterSave()
    {
        /**
         * Fix saving attribute in admin
         */
        Mage::getSingleton('eav/config')->clear();
        return parent::_afterSave();
    }

    /**
     * Return is attribute global
     *
     * @access public
     * @return integer
     * @author averun <dev@averun.com>
     */
    public function getIsGlobal()
    {
        return $this->_getData('is_global');
    }

    /**
     * Retrieve attribute is global scope flag
     *
     * @access public
     * @return bool
     * @author averun <dev@averun.com>
     */
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }

    /**
     * Retrieve attribute is website scope website
     *
     * @access public
     * @return bool
     * @author averun <dev@averun.com>
     */
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    /**
     * Retrieve attribute is store scope flag
     *
     * @access public
     * @return bool
     * @author averun <dev@averun.com>
     */
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }

    /**
     * Retrieve store id
     *
     * @access public
     * @return int
     * @author averun <dev@averun.com>
     */
    public function getStoreId()
    {
        $dataObject = $this->getDataObject();
        if ($dataObject) {
            return $dataObject->getStoreId();
        }

        return $this->getData('store_id');
    }
    /**
     * Retrieve source model
     *
     * @access public
     * @return Mage_Eav_Model_Entity_Attribute_Source_Abstract
     * @author averun <dev@averun.com>
     */
    public function getSourceModel()
    {
        $model = $this->getData('source_model');
        if (empty($model)) {
            if ($this->getBackendType() == 'int' && $this->getFrontendInput() == 'select') {
                return $this->_getDefaultSourceModel();
            }
        }

        return $model;
    }


    /**
     * Retrieve not translated frontend label
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getFrontendLabel()
    {
        return $this->_getData('frontend_label');
    }

    /**
     * Get Attribute translated label for store
     *
     * @access protected
     * @deprecated
     * @return string
     * @author averun <dev@averun.com>
     */
    protected function _getLabelForStore()
    {
        return $this->getFrontendLabel();
    }

    /**
     * Initialize store Labels for attributes
     *
     * @access public
     * @param mixed $storeId
     * @deprecated
     * @return void
     * @author averun <dev@averun.com>
     */
    public static function initLabels($storeId = null)
    {
        if (null === self::$_labels) {
            if (null === $storeId) {
                $storeId = Mage::app()->getStore()->getId();
            }

            $attributeLabels = array();
            $attributes = Mage::getResourceSingleton('catalog/product')->getAttributesByCode();
            foreach ($attributes as $attribute) {
                if ($attribute->getData('frontend_label') != '') {
                    $attributeLabels[] = $attribute->getData('frontend_label');
                }
            }

            self::$_labels = Mage::app()->getTranslator()
                ->getResource()
                ->getTranslationArrayByStrings($attributeLabels, $storeId);
        }
    }

    /**
     * Get default attribute source model
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function _getDefaultSourceModel()
    {
        return 'eav/entity_attribute_source_table';
    }
}
