<?php

class Codazon_Flexibletheme_Block_Adminhtml_Form_Renderer_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getHeaderTitleHtml($element)
    {
        return '<div class="entry-edit-head collapseable" ><a id="' . $element->getHtmlId()
            . '-head" href="#" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\'); return false;">' . $element->getLegend() . '</a></div>';
    }
}
