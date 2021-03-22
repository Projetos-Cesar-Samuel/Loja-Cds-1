<?php

/**
 * Category of sizes admin block
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
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
        $this->_controller         = 'adminhtml_category';
        $this->_blockGroup         = 'ave_sizechart';
        parent::__construct();
        $this->_headerText         = Mage::helper('ave_sizechart')->__('Category of sizes');
        $this->_updateButton('add', 'label', Mage::helper('ave_sizechart')->__('Add Category of sizes'));

        $this->setTemplate('ave_sizechart/grid.phtml');
    }
}
