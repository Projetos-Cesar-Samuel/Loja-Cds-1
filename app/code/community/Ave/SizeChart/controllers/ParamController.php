<?php

class Ave_SizeChart_ParamController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        return $this->_redirect('*/*/');
    }

    public function getDataAction()
    {
        $productId = $this->getRequest()->getParam('productId');
        $data = Mage::helper('ave_sizechart/frontend_data')->getData($productId);
        return $this->getResponse()->setBody(json_encode($data ));
    }
}
