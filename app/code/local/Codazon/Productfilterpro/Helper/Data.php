<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Productfilterpro_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    protected $_defaultData;
    
    protected $_sliderDefaultData;
        
    public function getSliderDefaultData()
    {
        if ($this->_sliderDefaultData === null) {
            $this->_sliderDefaultData = array(
                'items_1900'    => 6, 
                'items_1600'    => 5,
                'items_1420'    => 5,
                'items_1280'    => 5,
                'items_980'     => 5,
                'items_768'     => 4,
                'items_480'     => 3,
                'items_320'     => 2,
                'items_0'       => 1.5,
                'slider_dots'   => 0,
                'slider_nav'    => 0,
                'slider_margin' => 0,
            );
        }
        return $this->_sliderDefaultData;
    }
    
    public function getProductListDefaultData()
    {
        if ($this->_defaultData === null) {
            $this->_defaultData = array(
                'use_ajax'          => 0,
                'filter_type'       => '0',
                'order_by'          => 'entity_id',
                'order'             => 'desc',
                'products_count'    => 12,
                'total_rows'        => 1,
                'total_cols'        => 4,
                'thumb_width'       => 280,
                'thumb_height'      => 280,
                'custom_template'   => 'codazon_productfilterpro/product-style-01.phtml',
                'show'              => 'name,price,description,addtocart,wishlist,compare,quickshop',
            );
            $this->_defaultData = array_merge($this->_defaultData, $this->getSliderDefaultData());
        }
        return $this->_defaultData;
    }
}