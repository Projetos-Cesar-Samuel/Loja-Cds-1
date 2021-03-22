<?php

class Ave_SizeChart_Block_Customer_Member extends Mage_Core_Block_Template
{

    protected $_membersCollection;

    public function getMembersCollection()
    {
        return $this->_membersCollection;
    }

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ave/sizechart/member/grid.phtml');
        $this->_membersCollection = Mage::getModel('ave_sizechart/member')->getCustomerMembers();
    }

    public function getEditUrl($member)
    {
        return $this->getUrl('*/*/edit', array('id' => $member->getId()));
    }

    public function getDeleteUrl($member)
    {
        return $this->getUrl('*/*/delete', array('id' => $member->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }

    public function getNewUrl()
    {
        return $this->getUrl('*/*/new');
    }

}
