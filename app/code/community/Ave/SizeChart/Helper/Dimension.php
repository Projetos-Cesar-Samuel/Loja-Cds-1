<?php 

/**
 * Dimension helper
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Helper_Dimension extends Mage_Core_Helper_Abstract
{

    protected $_dimensions;

    /**
     * get base files dir
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getFileBaseDir()
    {
        return Mage::getBaseDir('media').DS.'dimension'.DS.'file';
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
        return Mage::getBaseUrl('media').'dimension'.'/'.'file';
    }

    /**
     * get dimension attribute source model
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
                'backend_model' => 'ave_sizechart/dimension_attribute_backend_file'
            ),
            'image'          => array(
                'backend_model' => 'ave_sizechart/dimension_attribute_backend_image'
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
     * get dimension attribute backend model
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
     * @param Ave_SizeChart_Model_Dimension $dimension
     * @param string $attributeHtml
     * @param string @attributeName
     * @return string
     * @author averun <dev@averun.com>
     */
    public function dimensionAttribute($dimension, $attributeHtml, $attributeName)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute(
            Ave_SizeChart_Model_Dimension::ENTITY,
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

        if ($dimension) {
            $attributeHtml .= ''; //yes it is plug
        }

        return $attributeHtml;
    }

    /**
     * @param $dimensionsStr
     * @return string
     */
    public function parseDimensions($dimensionsStr)
    {
        if (empty($dimensionsStr)) {
            return '';
        }

        $dimensions = explode(';', $dimensionsStr);
        $measure = $this->getDefaultMeasure();
        $dimensionsParsed = array();
        foreach ($dimensions as $value) {
            if (empty($value)) {
                continue;
            }

            $dim = explode('=', $value);
            if ($this->isCorrectDimension($dim)) {
                continue;
            }

            $id = substr($dim[0], strlen('ave_sizechart_dimension_'));
            if ($id == 'main') {
                $dimensionsParsed['main'] = $dim[1];
            } else if ($id == 'select') {
                $measure = $dim[1];
            } else {
                $dimensionsParsed[(int) $id] = $dim[1];
            }
        }

        $dimensionNames = $this->getDimensions();
        $html = '<ul>';
        if (key_exists('main', $dimensionsParsed)) {
            $html .= $this->getItemHtml($this->__('Recommended size'), $dimensionsParsed['main']);
        }

        foreach ($dimensionsParsed as $id => $value) {
            if (key_exists($id, $dimensionNames)) {
                $label = $dimensionNames[$id];
                $html .= $this->getItemHtml($label, $value, $measure);
            }
        }

        $html .= '</ul>';
        return $html;
    }

    public function getDefaultMeasure()
    {
        $defaultDimension = Mage::getStoreConfig('ave_sizechart/general/dimension');
        if (empty($defaultDimension)) {
            $defaultDimension = Ave_SizeChart_Model_Source_Dimension::DIMENSION_DEFAULT;
        }

        return $defaultDimension;
    }

    /**
     * @return array
     */
    protected function getDimensions()
    {
        if (empty($this->_dimensions)) {
            $this->_dimensions =
                Mage::getResourceModel('ave_sizechart/dimension_collection')->addAttributeToSelect('name')
                    ->toOptionHash();
        }

        return $this->_dimensions;
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

    /**
     * @param $label
     * @param $value
     * @param null $measure
     * @return string
     */
    protected function getItemHtml($label, $value, $measure = null)
    {
        $html = '<li>';
        $html .= '<span class="dimension-name">' . $label . '</span> - <span class="dimension-value">' . $value;
        if ($measure) {
            $html .= ' ' . $measure;
        }

        $html .= '</span>';
        $html .= '</li>';
        return $html;
    }

    /**
     * @param $dim
     * @return bool
     */
    protected function isCorrectDimension($dim)
    {
        return count($dim) != 2;
    }
}
