<?php

/**
 * Chart admin block
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Chart extends Mage_Adminhtml_Block_Widget_Grid_Container
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
        $this->_controller         = 'adminhtml_chart';
        $this->_blockGroup         = 'ave_sizechart';

        parent::__construct();
        $this->_headerText         = Mage::helper('ave_sizechart')->__('Chart');
        $this->_updateButton('add', 'label', Mage::helper('ave_sizechart')->__('Add Chart'));

        $this->setTemplate('ave_sizechart/grid.phtml');
    }
}
