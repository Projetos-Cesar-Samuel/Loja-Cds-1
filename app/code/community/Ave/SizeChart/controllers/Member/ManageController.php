<?php

class Ave_SizeChart_Member_ManageController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        if ($block = $this->getLayout()->getBlock('customer_member')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->getLayout()->getBlock('head')->setTitle($this->__('Size Chart Members'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('form');
    }

    public function editAction()
    {
        $this->_forward('form');
    }

    public function formAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sizechart/member_manage');
        }

        $this->renderLayout();
    }

    public function formPostAction()            // Save data
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }

        if ($this->getRequest()->isPost()) {
            $customer = $this->getSession()->getCustomer();
            /** @var $member Ave_SizeChart_Model_Member */
            $member = Mage::getModel('ave_sizechart/member');
            try {
                $customerId = $customer->getId();
                $name = $this->getRequest()->getParam('name');
                if (empty($name)) {
                    throw new Mage_Core_Exception($this->__('Please enter the member name.'));
                }

                if (($memberId = $this->getRequest()->getParam('id')) && !empty($memberId)) {
                    $member->load($memberId);
                } else {
                    $member->setCustomerId($customerId);
                }

                $member->setActive($this->getRequest()->getParam('active', false))->setName($name);
                if ($this->getRequest()->getParam('active', false)) {
                    $collection = Mage::getResourceModel('ave_sizechart/member_collection')
                        ->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerId);
                    $collection->massUpdate(array('active' => 0));
                }

                $member->save();
                $this->saveMeasure($customerId, $member->getId());
                $this->getSession()->addSuccess($this->__('The Member has been saved.'));
                return $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure' => true)));
            } catch (Mage_Core_Exception $e) {
                $this->getSession()->setMemberFormData($this->getRequest()->getPost())->addException(
                    $e,
                    $e->getMessage()
                );
            } catch (Exception $e) {
                $this->getSession()->setMemberFormData($this->getRequest()->getPost())->addException(
                    $e,
                    $this->__('Cannot save member.')
                );
            }
        }

        return $this->_redirectError(Mage::getUrl('*/*/edit', array('id' => $member->getId())));
    }

    public function deleteAction()
    {
        $memberId = $this->getRequest()->getParam('id', false);

        if ($memberId) {
            $member = Mage::getModel('ave_sizechart/member')->load($memberId);
            if ($member->getCustomerId() != $this->getSession()->getCustomerId()) {
                $this->getSession()->addError($this->__('The member does not belong to this customer.'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/index'));
                return;
            }

            try {
                $member->delete();
                $this->getSession()->addSuccess(
                    $this->__('The member %s has been deleted.', htmlspecialchars($member->getName()))
                );
            } catch (Exception $e){
                $this->getSession()->addException($e, $this->__('An error occurred while deleting the member.'));
            }
        }

        $this->getResponse()->setRedirect(Mage::getUrl('*/*/index'));
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

    /**
     * @param $customerId
     * @param $realMemberId
     */
    protected function saveMeasure($customerId, $realMemberId)
    {
        $transaction = Mage::getModel('core/resource_transaction');
        foreach ($this->getRequest()->getParam('dimension') as $dimensionId => $dimension) {
            if (empty($dimension)) {
                continue;
            }

            $bindData = array(
                'customer_id'  => $customerId,
                'member_id'    => $realMemberId,
                'dimension_id' => $dimensionId
            );
            /** @var $measure Ave_SizeChart_Model_MemberMeasure */
            $measure = Mage::getModel('ave_sizechart/memberMeasure');
            $measure->loadByFields($bindData);
            if (!$measure->getEntityId()) {
                $measure->addData($bindData);
            }

            $measure->setData('value', $dimension);
            $transaction->addObject($measure);
        }

        $transaction->save();
    }
}
