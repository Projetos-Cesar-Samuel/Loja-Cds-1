<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Form_Element_Image extends Varien_Data_Form_Element_Abstract
{
    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->setType('text');
    }

    /**
     * Return element html code
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';

        //if ((string)$this->getValue()) {
            
            $url = '';
            if ($this->getData('media_url')) {
                if ((string)$this->getValue()) {
                    $url = $this->getData('media_url') . (string)$this->getValue();
                }
            } else {
                $url = $this->_getUrl();
                if ((string)$this->getValue()) {
                    if( !preg_match("/^http\:\/\/|https\:\/\//", $url) ) {
                        $url = Mage::getBaseUrl('media') . $url;
                    }
                }
            }

            $html = '<a href="' . $url . '"'
                . ' onclick="imagePreview(\'' . $this->getHtmlId() . '_image\'); return false;">'
                . '<img src="' . $url . '" id="' . $this->getHtmlId() . '_image" title="' . $this->getValue() . '"'
                . ' alt="' . $this->getValue() . '" height="22" width="22" class="small-image-preview v-middle" />'
                . '</a> ';
        //}

        $this->setClass('input-file');
        $html .= parent::getElementHtml();
        $html .= '<span class="image-browser">';
        $html .= '<button type="button" onclick="CodazonMedia.ajaxUploadImage(\'' . $this->getData('upload_url') .'\', \'' . $this->getHtmlId() . '\')" id="browser_' . $this->getHtmlId() . '">';
        $html .= Mage::helper('flexibletheme')->__('Choose File');
        $html .= '</button>';
        $html .= '</span>';
        $html .= $this->_getDeleteCheckbox();
        
        
        return $html;
    }

    /**
     * Return html code of delete checkbox element
     *
     * @return string
     */
    protected function _getDeleteCheckbox()
    {
        $html = '';
        /* if ($this->getValue()) {
            $label = Mage::helper('core')->__('Delete Image');
            $html .= '<span class="delete-image">';
            $html .= '<input type="checkbox"'
                . ' name="' . parent::getName() . '[delete]" value="1" class="checkbox"'
                . ' id="' . $this->getHtmlId() . '_delete"' . ($this->getDisabled() ? ' disabled="disabled"': '')
                . '/>';
            $html .= '<label for="' . $this->getHtmlId() . '_delete"'
                . ($this->getDisabled() ? ' class="disabled"' : '') . '> ' . $label . '</label>';
            $html .= $this->_getHiddenInput();
            $html .= '</span>';
        } */

        return $html;
    }

    /**
     * Return html code of hidden element
     *
     * @return string
     */
    protected function _getHiddenInput()
    {
        return '<input type="hidden" name="' . parent::getName() . '[value]" value="' . $this->getValue() . '" />';
    }

    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        return $this->getValue();
    }

    /**
     * Return name
     *
     * @return string
     */
    public function getName()
    {
        return  $this->getData('name');
    }
}
