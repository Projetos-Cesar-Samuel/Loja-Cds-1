<?php
class Codazon_Megamenupro_Adminhtml_MegamenuprobackendController extends Mage_Adminhtml_Controller_Action
{

	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('megamenupro/megamenuprobackend');
		return true;
	}

	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Edit Mega Menu"));
	   $this->renderLayout();
    }
}