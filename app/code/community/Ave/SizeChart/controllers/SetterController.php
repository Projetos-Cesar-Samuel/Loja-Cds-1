<?php

class Ave_SizeChart_SetterController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function indexAction()
    {
        return $this->_redirect('sizechart/member_manage');
    }

    public function activeAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirect('sizechart/member_manage');
        }

        $memberId = (int)$this->getRequest()->getParam('member_id');
        $data["status"] = 'not save';
        if ($memberId) {
            $member = Mage::getModel('ave_sizechart/member')->load($memberId);
            if ($member->getCustomerId() != $this->getSession()->getCustomerId()) {
                $data["status"] = 'fail';
            } else {
                try {
                    $collection = Mage::getResourceModel('ave_sizechart/member_collection')
                        ->addFieldToSelect('*')->addFieldToFilter('customer_id', $this->getSession()->getCustomerId());
                    $collection->massUpdate(array('active' => 0));
                    $member->setData('active', 1);
                    $member->save();
                    $data["status"] = 'ok';
                } catch (Exception $e){
                    $data["status"] = 'fail';
                }
            }
        }

        return $this->getResponse()->setBody(json_encode($data));
    }

    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirect('sizechart/member_manage');
        }

        $data["status"] = 'not save';
        $dimensionId = (int) $this->getRequest()->getParam('dimension_id');
        $dimensionValue = (float) $this->getRequest()->getParam('value');
        $memberId = (int) $this->getRequest()->getParam('member_id');
        $customer = $this->getSession()->getCustomer();
        if ($memberId && $dimensionId && $dimensionValue) {
            try {
                /** @var $measure Ave_SizeChart_Model_MemberMeasure */
                $measure = Mage::getModel('ave_sizechart/memberMeasure');
                $bindData = array(
                    'customer_id'  => $customer->getId(),
                    'member_id'    => $memberId,
                    'dimension_id' => $dimensionId
                );
                $measure->loadByFields($bindData);
                if (!$measure->getEntityId()) {
                    $measure->addData($bindData);
                }

                $measure->setData('value', $dimensionValue);
                $measure->save();
                $data["status"] = 'ok';
            } catch (Exception $e) {
                $data["status"] = 'fail';
            }
        }

        return $this->getResponse()->setBody(json_encode($data));
    }

    /**
     * Retrieve customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}
