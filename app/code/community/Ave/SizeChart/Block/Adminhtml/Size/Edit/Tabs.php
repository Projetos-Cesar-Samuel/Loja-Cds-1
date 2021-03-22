<?php

/**
 * Size admin edit tabs
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Size_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author averun <dev@averun.com>
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('size_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ave_sizechart')->__('Size'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return Ave_SizeChart_Block_Adminhtml_Size_Edit_Tabs
     * @author averun <dev@averun.com>
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_size',
            array(
                'label'   => Mage::helper('ave_sizechart')->__('Size'),
                'title'   => Mage::helper('ave_sizechart')->__('Size'),
                'content' => $this->getLayout()->createBlock(
                    'ave_sizechart/adminhtml_size_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve size entity
     *
     * @access public
     * @return Ave_SizeChart_Model_Size
     * @author averun <dev@averun.com>
     */
    public function getSize()
    {
        return Mage::registry('current_size');
    }
}
