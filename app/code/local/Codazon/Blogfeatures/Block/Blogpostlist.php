<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Blogfeatures_Block_Blogpostlist extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    protected $_postCollection;
    
    protected $_dataArray;
    
    protected $_showInFront;
    
    protected $_sliderData;
    
    protected $_blogRoute;
    
    public function _construct()
    {        
        parent::_construct();
        $this->addData(array_replace(
            array(
                'blog_title'    => '',
                'categories'    => '',
                'orderby'       => 'created_time',
                'order'         => 'desc',
                'post_count'    => 8,
                'desc_length'   => 100,
                'show_slider'   => true,
                'thumb_width'   => 300,
                'thumb_height'  => 200,
                'date_format'   => 'Y-m-d H:i:s',
                'custom_template'      => 'codazon_blogfeatures/post/widget/grid.phtml',
                'show_in_front' => 'post_image,title,short_content,created_time,user',
            ),
            $this->getData())
        );
        $this->_showInFront = explode(',', $this->getData('show_in_front'));
        $this->_blogRoute = Mage::helper('blog')->getRoute();
        return $this;
    }
    
    public function getSliderData()
    {
        $this->_sliderData = array(
            'nav'        => (bool) $this->getData('slider_nav'),
            'dots'       => (bool) $this->getData('slider_dots'),
            'margin'     => (float)$this->getData('slider_margin'),
        );
        $adapts = array('1900', '1600', '1420', '1280','980','768','480','320','0');
        foreach ($adapts as $adapt) {        	
            if ($this->getData('items_' . $adapt)) {
                $this->_sliderData['responsive'][$adapt] = array('items' => (float) $this->getData('items_' . $adapt));
            }
        }

        return $this->_sliderData;
    }

    public function setPostCollection($collection)
    {
        $this->_postCollection = $collection;
    }

    public function getPostCollection()
    {
        if (!$this->_postCollection) {
            $this->_postCollection = $this->_prepareCollection();
        }
        return $this->_postCollection;
    }
    
    protected function _toHtml()
    {
        $template = $this->getData('custom_template');
        $this->setTemplate($template);
        $html = parent::_toHtml();
        return $html;
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('blog/blog')->getCollection();

        if ($this->getData('categories') !== '') {
            
            $postCatTable = Mage::getSingleton('core/resource')->getTableName('blog/post_cat');
            
            $collection->getSelect()->joinLeft(
                array('pc' => $postCatTable), 'pc.post_id = main_table.post_id',
                array('cat_id' => 'pc.cat_id')
            )->group('main_table.post_id');
            
            $collection->addFieldToFilter('pc.cat_id', array('in' => explode(',', trim($this->getData('categories')))));
        }

        $collection->addPresentFilter()
            ->addEnableFilter(AW_Blog_Model_Status::STATUS_ENABLED)
            ->addStoreFilter();

        $collection->setOrder($this->getOrderby(), $this->getOrder());
        $collection->setPageSize($this->getPostCount())->setCurPage(1);

        return $collection;
    }
    public function isShow($field)
    {
        return in_array($field, $this->_showInFront);
    }
    
    public function subString($str, $strLenght)
    {
        $str = $this->stripTags($str);
        if(strlen($str) > $strLenght) {
            $strCutTitle = substr($str, 0, $strLenght);
            $str = substr($strCutTitle, 0, strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
    }
    
    public function getBlogUrl($route = null, $params = array())
    {
        $blogRoute = $this->_blogRoute;
        if (is_array($route)) {
            foreach ($route as $item) {
                $item = urlencode($item);
                $blogRoute .= "/{$item}";
            }
        } else {
            $blogRoute .= "/{$route}";
        }

        foreach ($params as $key => $value) {
            $value = urlencode($value);
            $blogRoute .= "{$key}/{$value}/";
        }

        return $this->getUrl($blogRoute);
    }
    
    public function getPostUrl($post)
    {
        return $this->getUrl($this->_blogRoute . '/' . $post->getIdentifier());
    }
    
    public function getPostCategory($post)
    {
        $route = Mage::getUrl($this->_blogRoute);
        $cat = Mage::getModel('blog/cat')->getCollection()
            ->addPostFilter($post->getId())
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->getFirstItem();
        $cat->setCategoryUrl($route . "cat/" . $cat->getIdentifier());
        return $cat;
    }
}