<?php
class Codazon_Megamenupro_Block_Widget_Contactform extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
	protected function _toHtml()
    {
        $this->setTemplate('codazon_megamenupro/contact-form.phtml');
		return parent::_toHtml();
    }
	public function getGeocodeByAddress($address){
		return json_decode(file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false'));
	}
	public function getGoogleMapJavascriptUrl(){
		return "//maps.googleapis.com/maps/api/js?v=3.25&signed_in=true&key=AIzaSyByF5Th99QzkJtIhod9awRaDK2CGSNB43o";
	}
	public function getFormAction(){
		return $this->getUrl('contacts/index/post');	
	}
	public function getFormTitle(){
		if($title = $this->getData('form_title')){
			return $title;
		}else{
			return $this->__('Contact Information');
		}
	}
}