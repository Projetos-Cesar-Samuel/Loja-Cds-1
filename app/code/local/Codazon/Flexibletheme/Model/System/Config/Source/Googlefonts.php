<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_System_Config_Source_Googlefonts
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    protected $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyCWBE3G0k9qbhJYmml65yfuPXP9KsmLZMo';
    
    public function fetchData($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    public function getFontList()
    {
        $fontJson = $this->fetchData($this->url);
        $font     = json_decode($fontJson);
        if (isset($font->items)) {
            return $font->items;
        } else {
            return array();
        }
    }
    
    public function toOptionArray()
    {
        $fontList = $this->getFontList();
        $options = array();
        if (count($fontList)) {
            foreach ($fontList as $font) {
                $options[] = array('value' => $font->family, 'label' => $font->family);
            }
        }
        return $options;
    }
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}