<?php
class Codazon_Megamenupro_Model_Menulist extends Mage_Core_Model_Abstract{
	public function toOptionArray(){
		$menus = Mage::getModel("megamenupro/megamenupro")->getCollection();
		$menuList = array();
		foreach($menus as $menu){
			$menuList[] = array('value' => $menu->getIdentifier(), 'label' => $menu->getTitle());
		}
		return $menuList;
	}
}