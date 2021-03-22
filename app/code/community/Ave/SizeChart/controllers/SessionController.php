<?php

class Ave_SizeChart_SessionController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        return $this->_redirect('*/*/');
    }

    public function setAction()
    {
        if (!$this->getRequest()->isPost()) {
            return 'fail';
        }

        $data["status"] = 'not save';
        $name = $this->getRequest()->getParam('name');
        $value = $this->getRequest()->getParam('value');
        if ($name && $value && strpos($name, 'ave_sizechart') === 0) {
            try {
                $this->getSession()->setData($name, $value);
                $data["name"] = $name;
                $data["status"] = 'ok';
            } catch (Exception $e) {
                $data["status"] = 'fail';
            }
        }

        return $this->getResponse()->setBody(json_encode($data));
    }

    /**
     * @return Zend_Controller_Response_Abstract
     */
    public function getMembersAction()
    {
        $members = Mage::helper('ave_sizechart/frontend_data')->getCustomerMembers();
        return $this->getResponse()->setBody(json_encode($members));
    }

    /**
     * @return Zend_Controller_Response_Abstract
     */
    public function getSessionAction()
    {
        $data = Mage::helper('ave_sizechart/frontend_data')->getSessionData();
        return $this->getResponse()->setBody(json_encode($data));
    }

    /**
     * Retrieve customer session object
     *
     * @return Mage_Core_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('core/session');
    }
}
