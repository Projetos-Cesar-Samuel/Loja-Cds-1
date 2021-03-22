<?php

/**
 * Postcode Edit Tabs Block
 * 
 * @category    Magestore
 * @package     Magestore_Postcode
 * @author      Magestore Developer
 */
class LA_Postcode_Block_Adminhtml_Postcode_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('postcode_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('postcode')->__('Item Information'));
    }
    
    /**
     * prepare before render block to html
     *
     * @return LA_Postcode_Block_Adminhtml_Postcode_Edit_Tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('postcode')->__('Item Information'),
            'title'     => Mage::helper('postcode')->__('Item Information'),
            'content'   => $this->getLayout()
                                ->createBlock('postcode/adminhtml_postcode_edit_tab_form')
                                ->toHtml(),
        ));

        $this->addTab('form_conditions', array(
            'label'     => Mage::helper('postcode')->__('Conditions'),
            'title'     => Mage::helper('postcode')->__('Conditions'),
            'content'   => $this->getLayout()->createBlock('postcode/adminhtml_postcode_edit_tab_conditions')->toHtml(),
            //'active'    =>true,
        ));

        return parent::_beforeToHtml();
    }
}