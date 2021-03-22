<?php

/**
 * Size admin block
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Size extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author averun <dev@averun.com>
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_size';
        $this->_blockGroup         = 'ave_sizechart';
        parent::__construct();
        $this->_headerText         = Mage::helper('ave_sizechart')->__('Size');
        $this->_updateButton('add', 'label', Mage::helper('ave_sizechart')->__('Add Size'));

    }
}
