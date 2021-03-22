<?php 

/**
 * Category of sizes helper
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Helper_Category extends Mage_Core_Helper_Abstract
{

    /**
     * get base files dir
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getFileBaseDir()
    {
        return Mage::getBaseDir('media').DS.'category'.DS.'file';
    }

    /**
     * get base file url
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getFileBaseUrl()
    {
        return Mage::getBaseUrl('media').'category'.'/'.'file';
    }

    /**
     * get category attribute source model
     *
     * @access public
     * @param string $inputType
     * @return mixed (string|null)
     * @author averun <dev@averun.com>
     */
     public function getAttributeSourceModelByInputType($inputType)
     {
         $inputTypes = $this->getAttributeInputTypes();
         if (!empty($inputTypes[$inputType]['source_model'])) {
             return $inputTypes[$inputType]['source_model'];
         }

         return null;
     }

    /**
     * get attribute input types
     *
     * @access public
     * @param string $inputType
     * @return array()
     * @author averun <dev@averun.com>
     */
    public function getAttributeInputTypes($inputType = null)
    {
        $inputTypes = array(
            'multiselect' => array(
                'backend_model' => 'eav/entity_attribute_backend_array'
            ),
            'boolean'     => array(
                'source_model'  => 'eav/entity_attribute_source_boolean'
            ),
            'file'          => array(
                'backend_model' => 'ave_sizechart/category_attribute_backend_file'
            ),
            'image'          => array(
                'backend_model' => 'ave_sizechart/category_attribute_backend_image'
            ),
        );

        if (null === $inputType) {
            return $inputTypes;
        } else if (isset($inputTypes[$inputType])) {
            return $inputTypes[$inputType];
        }

        return array();
    }

    /**
     * get category attribute backend model
     *
     * @access public
     * @param string $inputType
     * @return mixed (string|null)
     * @author averun <dev@averun.com>
     */
    public function getAttributeBackendModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['backend_model'])) {
            return $inputTypes[$inputType]['backend_model'];
        }

        return null;
    }

    /**
     * filter attribute content
     *
     * @access public
     * @param Ave_SizeChart_Model_Category $category
     * @param string $attributeHtml
     * @param string @attributeName
     * @return string
     * @author averun <dev@averun.com>
     */
    public function categoryAttribute($category, $attributeHtml, $attributeName)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute(
            Ave_SizeChart_Model_Category::ENTITY,
            $attributeName
        );
        if ($attribute && $attribute->getId() && !$attribute->getIsWysiwygEnabled()) {
            if ($attribute->getFrontendInput() == 'textarea') {
                $attributeHtml = nl2br($attributeHtml);
            }
        }

        if ($attribute->getIsWysiwygEnabled()) {
            $attributeHtml = $this->_getTemplateProcessor()->filter($attributeHtml);
        }

        if ($category) {
            $attributeHtml .= ''; //yes it is plug
        }

        return $attributeHtml;
    }

    /**
     * get the template processor
     *
     * @access protected
     * @return Mage_Catalog_Model_Template_Filter
     * @author averun <dev@averun.com>
     */
    protected function _getTemplateProcessor()
    {
        if (null === $this->_templateProcessor) {
            $this->_templateProcessor = Mage::helper('catalog')->getPageTemplateProcessor();
        }

        return $this->_templateProcessor;
    }
}
