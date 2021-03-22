<?php
/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
 
class Codazon_Flexibletheme_Block_Widget_Categorytree extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
	/**
     * Top menu data tree
     *
     * @var Varien_Data_Tree_Node
     */
    protected $_menu;

    /**
     * Current entity key
     *
     * @var string|int
     */
    protected $_currentEntityKey;
	protected $_categoryModel;
	protected $_storeCategories = array();
    /**
     * Init top menu tree structure and cache
     */
    public function _construct()
    {
        $this->_menu = new Varien_Data_Tree_Node(array(), 'root', new Varien_Data_Tree());
		$this->_categoryModel = Mage::getModel('catalog/category');
		
		$this->_categoryHelper = Mage::helper('catalog/category');
		$this->_flatHelper = Mage::helper('catalog/category_flat');
		$this->_flat = $this->_flatHelper->isEnabled() && $this->_flatHelper->isBuilt(true);
		$this->_urlModel = Mage::getSingleton('core/url_rewrite')->getResource();
		$this->_storeId = Mage::app()->getStore()->getId();
        /*
        * setting cache to save the topmenu block
        */
        $this->setCacheTags(array(Mage_Catalog_Model_Category::CACHE_TAG));
        $this->setCacheLifetime(86400);
    }

    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return string
     */
	
    protected function _toHtml()
    {
        parent::_toHtml();
        return $this->getHtml();
    }
    
    
	public function addCatalogToTopmenuItems($menu)
    {
		if($this->getParentId()){
			$parentId = str_replace('category/', '', $this->getParentId());
		}else{
			$parentId = Mage::app()->getStore()->getRootCategoryId();
		}
        $this->addCacheTag(Mage_Catalog_Model_Category::CACHE_TAG);
        $this->_addCategoriesToMenu(
			$this->getStoreCategories($parentId), $menu
        );
    }
	
	public function getStoreCategories($parent = false, $sorted=false, $asCollection=false, $toLoad=true)
	{
		$this->_categoryTree = Mage::getResourceModel('catalog/category_tree');
		if($parent === false){
			$parent = Mage::app()->getStore()->getRootCategoryId();
		}
        $cacheKey   = sprintf('%d-%d-%d-%d', $parent, $sorted, $asCollection, $toLoad);
        if (isset($this->_storeCategories[$cacheKey])) {
            return $this->_storeCategories[$cacheKey];
        }

        /* @var $category Mage_Catalog_Model_Category */
        if (!$this->_categoryModel->checkId($parent)) {
            if ($asCollection) {
                return new Varien_Data_Collection();
            }
            return array();
        }

        $recursionLevel  = max(0, (int)$this->getData('max_depth') );
        //$storeCategories =  $this->_categoryModel->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);
		
		$categories = Mage::getResourceModel('catalog/category_collection')
			->setStore(Mage::app()->getStore())
			->addAttributeToSelect('name')
			->addAttributeToSelect('url_key')
			->addFieldToFilter('is_active', 1)
			->addAttributeToFilter('include_in_menu', 1);
		$storeCategories = $this->_categoryTree->loadNode($parent)->loadChildren($recursionLevel)->getChildren();
		
		$this->_categoryTree->addCollectionData($categories, $sorted, $parent, $toLoad, true);
		if ($asCollection) {
            $storeCategories = $this->_categoryTree->getCollection();
        }
		
        $this->_storeCategories[$cacheKey] = $storeCategories;
        return $storeCategories;	
	}
	

	protected function _addCategoriesToMenu($categories, $parentCategoryNode, $addTags = false)
    {
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            $nodeId = 'category-node-' . $category->getId();
            $this->_categoryModel->setId($category->getId());
            $tree = $parentCategoryNode->getTree();
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                'url' => $this->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category)
            );
            $categoryNode = new Varien_Data_Tree_Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);            
            /*if ($this->_flat){
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }*/
			$subcategories = $category->getChildren();
			$this->_addCategoriesToMenu($subcategories, $categoryNode);
        }
    }
	public function getCategoryUrl($category)
    {
        if ($category instanceof Mage_Catalog_Model_Category) {
            return $category->getUrl();
        }
		$requestPath = $this->_urlModel->getRequestPathByIdPath('category/' .$category->getId(), $this->_storeId);
		if(!empty($requestPath)){
			return Mage::getBaseUrl() . $requestPath;
		} else {
			return $this->_categoryModel->setData($category->getData())->getUrl();
		}
    }
	protected function _isActiveMenuCategory($category)
    {
        $catalogLayer = Mage::getSingleton('catalog/layer');
        if (!$catalogLayer) {
            return false;
        }

        $currentCategory = $catalogLayer->getCurrentCategory();
        if (!$currentCategory) {
            return false;
        }

        $categoryPathIds = explode(',', $currentCategory->getPathInStore());
        return in_array($category->getId(), $categoryPathIds);
    }
    
    public function getHtml($outermostClass = '')
    {
		$this->addCatalogToTopmenuItems($this->_menu);
        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass();
		$this->_menu->setLevel(0);
        $html = $this->_getHtml($this->_menu);
        return $html;
    }
    
    public function getOptions()
    {
        $this->setParentId(Mage_Catalog_Model_Category::TREE_ROOT_ID);
        $this->addCatalogToTopmenuItems($this->_menu);
        $options = $this->_getOptions($this->_menu, array());
        return $options;
    }

    protected function _getOptions(Varien_Data_Tree_Node $menuTree, $options = array(), $level = 0)
    {
        $parentLevel = $menuTree->getLevel();
        foreach ($menuTree->getChildren() as $child) {
            $options[] = array(
                'value'     =>  str_replace('category-node-', '', $child->getId()),
                'label'     =>  str_repeat('––', $level) .' '. $child->getName()
            );
            if ($child->hasChildren()) {
                $options = $this->_getOptions($child, $options, $level + 1);
            }
        }
        return $options;
    }
    
    protected function _getHtml(Varien_Data_Tree_Node $menuTree)
    {
        $html = '';
        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;
        foreach ($children as $child) {
            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a data-id="'. str_replace('category-node-', '', $child->getId()) .'">' . $this->escapeHtml($child->getName()) . '</a>';
            if ($child->hasChildren()) {
                $html .= '<ul>';
                $html .= $this->_getHtml($child);
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @param Varien_Data_Tree_Node $item
     * @return string
     */
    protected function _getRenderedMenuItemAttributes(Varien_Data_Tree_Node $item)
    {
        $html = '';
        $attributes = $this->_getMenuItemAttributes($item);
        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= $attributeValue ? ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"' : '';
        }

        return $html;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(Varien_Data_Tree_Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        $attributes = array(
            'class' => implode(' ', $menuItemClasses)
        );

        return $attributes;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Varien_Data_Tree_Node $item)
    {
        $classes = array();
		/* $classes[] = 'item';
        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        } */

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }

    /**
     * Retrieve cache key data
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $shortCacheId = array(
            'CDZ_CATEGORY_TREE',
            Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrentEntityKey()
        );
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['entity_key'] = $this->getCurrentEntityKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * Retrieve current entity key
     *
     * @return int|string
     */
    public function getCurrentEntityKey()
    {
        if (null === $this->_currentEntityKey) {
            $this->_currentEntityKey = Mage::registry('current_entity_key')
                ? Mage::registry('current_entity_key') : Mage::app()->getStore()->getRootCategoryId();
        }
        return $this->_currentEntityKey;
    }	
}