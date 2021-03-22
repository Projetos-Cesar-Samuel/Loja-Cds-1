<?php

/**
 * Type image field renderer helper
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Type_Helper_Image extends Varien_Data_Form_Element_Image
{
    /**
     * get the url of the image
     *
     * @access protected
     * @return string
     * @author averun <dev@averun.com>
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = Mage::helper('ave_sizechart/type_image')->getImageBaseUrl().
                $this->getValue();
        }

        return $url;
    }
}
