<?php
/**
 * Class Ave_SizeChart_Model_Member
 * @method int getActive()
 * @method string getName()
 */

class Ave_SizeChart_Model_Member extends Mage_Core_Model_Abstract
{
    protected $_membersHolder = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('ave_sizechart/member');
    }

    public function getCustomerMembers($customerId = null)
    {
        if (empty($customerId)) {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        }

        if (key_exists($customerId, $this->_membersHolder)) {
            return $this->_membersHolder[$customerId];
        }

        $members = Mage::getResourceModel('ave_sizechart/member_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('name');
        $members->load();
        $this->_membersHolder[$customerId] = $members;
        return $members;
    }

    public function loadByFields($bindFields)
    {
        $this->getResource()->loadByFields($this, $bindFields);
        return $this;
    }
}
