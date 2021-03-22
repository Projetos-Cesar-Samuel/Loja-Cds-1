<?php

/**
 * Postcode Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_Postcode
 * @author      Magestore Developer
 */
class LA_Postcode_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
        $data = Mage::helper('postcode')->getDataPostcode();
        $this->loadLayout();
        $this->renderLayout();
    }
}