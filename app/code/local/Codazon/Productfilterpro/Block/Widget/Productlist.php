<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
class Codazon_Productfilterpro_Block_Widget_Productlist  extends Mage_Catalog_Block_Product_Abstract implements Mage_Widget_Block_Interface
{
    protected $_helper;
    
    protected $_dataArray;
        
    protected $_show;    
    
    protected $_sliderData;
    
    public function _construct()
    {
        parent::_construct();
        $this->_helper = Mage::helper('productfilterpro');
        $this->addData(array_replace(
            $this->_helper->getProductListDefaultData(),
            $this->getData()
        ));
        $this->_show = explode(',', $this->getData('show'));
        
        return $this;
    }
    
    
    
    public function getCacheKeyInfo()
    {
        return array(
           'PRODUCTFILTERPRO_PRODUCTLIST',
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           Mage::app()->getStore()->getCurrentCurrencyCode(),
           'template' => $this->getTemplate(),
           md5(json_encode($this->getData()))
        );
    }
    
    public function addCategoriesToFilter($collection, array $categories = null)
    {
		$resource = Mage::getSingleton('catalog/resource_eav_mysql4_product_collection');
		if(count($categories) > 0){
			$collection->joinField('category_id',
				'catalog/category_product',
				'category_id',
				'product_id = entity_id',
				NULL,
				'left'
			);
			if($resource->isEnabledFlat()){
			    $collection->getSelect()->where('at_category_id.category_id in ('.implode(',', $categories).')');
			}else{
			    $collection->addAttributeToFilter('category_id', array('in' => $categories ));
			}
		}
	}
    
    protected function _getFullCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices($collection)->addStoreFilter();
        if ($categories = $this->getData('categories')) {
            $this->addCategoriesToFilter($collection, explode(',', $categories));
        }
        if ($attribute = $this->getData('attribute')) {
            $collection->addAttributeToFilter($attribute, '1');
        }        
        return $collection;
    }
    
    protected function _getNewCollection()
    {
        $collection = $this->_getFullCollection();
        $todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $collection->addAttributeToFilter('news_from_date', array('or'=> array(
            0 => array('date' => true, 'to' => $todayEndOfDayDate),
            1 => array('is' => new Zend_Db_Expr('null')))
        ), 'left')
        ->addAttributeToFilter('news_to_date', array('or'=> array(
            0 => array('date' => true, 'from' => $todayStartOfDayDate),
            1 => array('is' => new Zend_Db_Expr('null')))
        ), 'left')
        ->addAttributeToFilter(
            array(
                array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
            )
        )
        ->addAttributeToSort('news_from_date', 'desc');
        return $collection;
    }
    
    protected function _getBestSellingCollection()
    {
        $orderItemCol = Mage::getResourceModel('sales/order_item_collection')
            ->addFieldToSelect(array('product_id'))
            ->addFieldToFilter('parent_item_id', array('null' => true));
        $orderItemCol->getSelect()
            ->columns(array('ordered_qty' => 'SUM(`main_table`.`qty_ordered`)'))
            ->group('main_table.product_id')
            ->joinInner(
                array('sfo' => $orderItemCol->getTable('sales/order')),
                "(main_table.order_id = sfo.entity_id) AND (sfo.state <> 'canceled')",
                []
            );
        $collection = $this->_getFullCollection();
        $collection->getSelect()
            ->joinLeft(
                array('sfoi' => $orderItemCol->getSelect()),
                'e.entity_id = sfoi.product_id',
                array('ordered_qty' => 'sfoi.ordered_qty')
            )
            ->where('sfoi.ordered_qty > 0')
            ->order('ordered_qty desc');
        return $collection;
    }
    
    protected function _getProductCollection()
    {        
        $filterType = $this->getData('filter_type');
        
        switch ($filterType) {
            default :
            case '0' :  //categories
            case '4' :  //attribute
                $collection = $this->_getFullCollection()
                    ->addAttributeToSort($this->getData('order_by'), $this->getData('order'));
                break;
            case '1' :  //new
                $collection = $this->_getNewCollection();
                break;
            case '2': //best selling
                $collection = $this->_getBestSellingCollection();
                break;
            case '3': //most view
        }
        $curpage = (int)$this->getData('cur_page') ? : 1;
        //$collection->getSelect()->group('e.entity_id');
        $collection->distinct(true);
        $collection->setPageSize($this->getData('products_count'))->setCurPage($curpage);
        
        return $collection;
    }
    
    public function createCollection() {
        return $this->_getProductCollection();
    }
    
    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->_getProductCollection());
        return parent::_beforeToHtml();
    }
    
    protected function _toHtml()
    {
        if($this->getData('use_ajax') == 1){
            $template = 'codazon_productfilterpro/placeholder.phtml';
        } else {
            $template = $this->getData('custom_template');
        }
        $this->setTemplate($template);
        return parent::_toHtml();
    }
    
    public function getFilterData()
    {
        $filterData = $this->getData();
        unset($filterData['type']);
        unset($filterData['product_collection']);
        unset($filterData['module_name']);
        $filterData['use_ajax'] = 0;
        return $filterData;
    }
    
    public function isShow($element){
		return in_array($element, $this->_show);
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
    
    public function getSliderData()
    {
        if (!$this->_sliderData) {
            $this->_sliderData = [
                'nav'  => (bool)$this->getData('slider_nav'),
                'dots' => (bool)$this->getData('slider_dots')
            ];
            $adapts = array('1900', '1600', '1420', '1280','980','768','480','320','0');
            foreach ($adapts as $adapt) {
                 $this->_sliderData['responsive'][$adapt] = array('items' => (float)$this->getData('items_' . $adapt));
            }
            $this->_sliderData['margin'] = (float)$this->getData('slider_margin');
        }
        return $this->_sliderData;
    }
    
    public function getAddToCompareUrl($product)
    {
        
        $url = $this->getData('current_url');
		if($url){
			$product->setData('block_url', $url);
		}
        return $this->helper('productfilterpro/compare')->getAddUrl($product, $url);
        //return $this->helper('catalog/product_compare')->getAddUrl($product);
    }
    
    public function getLabelHelper()
    {
        return Mage::helper('flexibletheme');
    }
}