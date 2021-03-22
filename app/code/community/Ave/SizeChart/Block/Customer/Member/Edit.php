<?php

class Ave_SizeChart_Block_Customer_Member_Edit extends Mage_Directory_Block_Data
{
    protected $_dimensionModel;
    protected $_measurmentData;
    protected $_countryCollection;
    protected $_regionCollection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_dimensionModel = Mage::getModel('ave_sizechart/member');
        if ($id = $this->getRequest()->getParam('id')) {
            $this->_dimensionModel->load($id);
            if ($this->_dimensionModel->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
                $this->_dimensionModel->setData(array());
            }
        }

        if (!$this->_dimensionModel->getId()) {
            $this->_dimensionModel->setCustomerId($this->getCustomer()->getId());
        }

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->getTitle());
        }
    }

    /**
     * @return array
     */
    public function getMeasurementList()
    {
        if (empty($this->_measurmentData)) {
            /** @var $m Ave_SizeChart_Model_Resource_Dimension_Collection */
            $m = Mage::getModel('ave_sizechart/dimension')->getCollection();
            $m->addFieldToFilter('type', 'dimension');
            $m->addFieldToFilter('status', 1);
            $m->addAttributeToSelect('name', true);
            $m->addAttributeToSelect('id');
            $m->addAttributeToSelect('entity_id');
            $m->addAttributeToSelect('description', true);
            $memberId = (int) $this->getRequest()->getParam('id');
            if (!empty($memberId)) {
                $m->joinField(
                    'value',
                    $m->getTable('ave_sizechart/member_measure'),
                    'value',
                    'dimension_id=entity_id',
                    array('member_id' => $memberId),
                    'left'
                );
            }

            $m->addOrder('position', Varien_Data_Collection::SORT_ORDER_ASC);
            $m->load();
            $this->_measurmentData = $m->getData();
        }

        return $this->_measurmentData;
    }

    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }

        if ($this->getDimensionModel()->getId()) {
            $title = Mage::helper('customer')->__('Edit Member');
        } else {
            $title = Mage::helper('customer')->__('Add New Member');
        }

        return $title;
    }

    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        return $this->getUrl('sizechart/member_manage');
    }

    public function getSaveUrl()
    {
        return Mage::getUrl(
            'sizechart/member_manage/formPost',
            array('_secure' => true, 'id' => $this->getDimensionModel()->getId())
        );
    }

    public function getDimensionModel()
    {
        return $this->_dimensionModel;
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}
