<?php
class Codazon_Megamenupro_IndexController extends Mage_Core_Controller_Front_Action
{
	public function previewAction(){
		$data = $this->getRequest()->getParams();
		$menuBlock = $this->getLayout()->createBlock('megamenupro/widget_megamenu');
		$style = array();
		$stylesVars = array('css_class','dropdown_animation','dropdown_style');
		foreach($stylesVars as $stylesVar){
			if(isset($data[$stylesVar])){
				$style[$stylesVar] = $data[$stylesVar];
			}
		}
		$data['menu_id'] = 0;
		$data['style'] = json_encode($style);
		$megamenu = Mage::getModel("megamenupro/megamenupro");
		$megamenu->addData($data);
		$menuBlock->_construct();
		$menuBlock->setMenuObject($megamenu);
		$menuBlock->setTemplate('codazon_megamenupro/megamenu.phtml');
		$menuBlock->setData('need_filter',true);
		$html = '<div class="row menu-container" style="overflow:hidden; width:100%; height:100vh; max-height:670px;">';
		if($megamenu->getType() == 0)
		{
			$html .= '<div class="col-sm-24">';
			$html .= $menuBlock->toHtml();
			$html .= '</div>';
			$html .= '<div class="horizontal-preview col-sm-24">';
			$html .= '<img class="img-responsive demo-banner" src="'.$menuBlock->getSkinUrl('codazon/megamenupro/images/demo_banner.jpg',array('_area'=>'adminhtml','_package' => 'default')).'" />';
			$html .= '</div>';
		}else{
			$html .= '<div class="vertical-preview col-sm-6">';
			$html .= $menuBlock->toHtml();
			$html .= '</div>';
			$html .= '<div class="col-sm-18">';
			$html .= '<img class="img-responsive demo-banner" src="'.$menuBlock->getSkinUrl('codazon/megamenupro/images/demo_banner.jpg',array('_area'=>'adminhtml','_package' => 'default')).'" />';
			$html .= '</div>';
		}
		$html .= '</div>';
		echo $html;
	}
}
?>