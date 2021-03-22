<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Form_Element_Color extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('text');
        $this->setExtType('textfield');
    }

    public function getHtml()
    {
        $this->addClass('input-text');
        return parent::getHtml();
    }

    public function getHtmlAttributes()
    {
        return array('type', 'title', 'class', 'style', 'onclick', 'onchange', 'onkeyup', 'disabled', 'readonly', 'maxlength', 'tabindex');
    }
    
    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        $html .= $this->getColorPicker();
        return $html;
    }
    
    public function getColorPicker()
    {
        if ($this->getEscapedValue()) {
            $value = $this->getEscapedValue();
        } else {
            $value = '#000000';
        }
        $html =     '<span class="color-picker">';
        $html .=        '<input id="color_' . $this->getHtmlId() . '" type="color"value="' . $value . '" onchange="$(\'' . $this->getHtmlId() . '\').value = this.value;" />';
        $html .=    '</span>';
        return $html;
    }
}
