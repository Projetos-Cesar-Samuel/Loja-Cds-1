<?php
class Codazon_Megamenupro_Block_Widget_Megamenu extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
	protected $_templateProcessor = false;
	protected $_menuTree = null;
	public function _construct(){
		parent::_construct();
		$this->_templateProcessor = Mage::helper('cms')->getBlockTemplateProcessor();
		$this->setData('need_filter',true);
		if($this->getData('custom_template')){
			$this->setTemplate($this->getData('custom_template'));
		}else{
			$this->setTemplate('codazon_megamenupro/megamenu.phtml');
		}
	}
	
	public function getMenuTree(){
		if($this->_menuTree === null){
			$menu = $this->getMenuObject();
			if(count($menu->getContent()) == 0){
				$this->_menuTree = false;
				return $this->_menuTree;
			}
			if($menu){
				$items = json_decode($menu->getContent());
				$tree = array();
				$i = 0;
				$buff = array();
				if(count($items)){
						foreach($items as $item){
							$current = &$buff[$i];
							$current = $item;
							$k = $i - 1;
							while(isset($items[$k]) && ($k > 0)){
								if(($items[$k]->depth < $items[$i]->depth)){
									break;
								}else{
									$k--;
								}
							}
							if($item->depth == 0){
								$tree[$i] = &$current;
							}else{
								$buff[$k]->children[$i] = &$current;
							}
							$i++;
						}
				}
				$this->_menuTree = $tree;
			}
		}
		return $this->_menuTree;
	}

	public function _toHtml(){
		if($this->getData('need_filter')){
			return $this->_templateProcessor->filter(parent::_toHtml());
		}else{
			return parent::_toHtml();
		}
	}
	public function getMenuHtml($items){
		$html = '';
		foreach($items as $item){
			$html .= $this->setData('current_item',$item)->toHtml();
		}
		return $html;
	}
	
	public function getBackgroundStyle($content){
		switch ($content->bg_position){
			case 'left_top':
				return "left:{$content->bg_position_x}px; top:{$content->bg_position_y}px"; break;
			case 'left_bottom':
				return "left:{$content->bg_position_x}px; bottom:{$content->bg_position_y}px"; break;
			case 'right_top':
				return "right:{$content->bg_position_x}px; top:{$content->bg_position_y}px"; break;
			case 'right_bottom':
			default:
				return "right:{$content->bg_position_x}px; bottom:{$content->bg_position_y}px"; break;
		}
	}
	
	public function getMenuObject(){
		if(!$this->_menuObject){
			$identifier = trim($this->getMenu());
			$megamenu = Mage::getModel("megamenupro/megamenupro");
			$col = $megamenu->getCollection()
				->addFieldToFilter('is_active',1)
				->addFieldToFilter('identifier',$identifier);
			$this->_menuObject = $col->getFirstItem();
		}
		return $this->_menuObject;
	}
	public function setMenuObject($menuObject){
		$this->_menuObject = $menuObject;
	}
	public function getParentType($items,$i){
		$k = $i - 1;
		while( isset($items[$k]) && ($k > 0)){
			if(($items[$k]->depth < $items[$i]->depth)){
				break;
			}else{
				$k = $k - 1;
			}
		}
		if(isset($items[$k])){
			return $items[$k]->item_type;
		}else{
			return false;	
		}
	}
	public function getIcon($content){
		if(isset($content->icon_type) && $content->icon_type == 0){
			return ($content->icon_font)?'<i class="menu-icon fa fa-'.$content->icon_font.'"></i>':'';	
		}else{
			return ($content->icon_img)?'<i class="menu-icon img-icon"><img src="'.$content->icon_img.'"></i>':'';	
		}
	}
	public function getItemCSSClass($item)
	{
		$depth = (int)$item->depth;
		$content = $item->content;

		$class[] = "item level{$depth} {$content->class}";
		if($depth == 0){
			$class[] = 'level-top';
		}
		switch ($item->item_type){
			case 'category':
				$class[] = 'parent cat-tree';
				if($content->display_type == 1){
					$class[] = 'no-dropdown';
				}
				break;
			case 'link':
				if(isset($item->children)){
					$class[] = 'parent';
				}
				break;
			case 'text':
				$class[] = 'text-content'; break;
			case 'row':
				$class[] = 'row no-dropdown'; break;
			case 'col':
				$class[] = 'col need-unwrap'; break;
			case 'tab_container':
				break;
			case 'tab_item':
				$class[] = 'tab-item'; break;
			default:
		}
		return implode(' ',$class);
	}
	public function getCategoryTree(){
		// if(!$this->_categoryTree){
			// $this->_categoryTree = $this->getLayout()->createBlock('megamenupro/widget_categorytree');
		// }
		// return $this->_categoryTree;
        return $this->getLayout()->createBlock('megamenupro/widget_categorytree');
	}
}