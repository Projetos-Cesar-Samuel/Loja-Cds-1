<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
class Codazon_Flexibletheme_Block_Footer extends Codazon_Flexibletheme_Block_AbstractFlexibletheme
{
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $footer = $this->helper->getFooter();
        $content = $footer->getContent();
        if ($content) {
            return $html . $this->filter($content);
        } else {
            return $html;
        }
    }
}