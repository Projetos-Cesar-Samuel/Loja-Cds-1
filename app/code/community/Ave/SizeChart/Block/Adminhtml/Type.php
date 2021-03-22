<?php

/**
 * Type admin block
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Type extends Mage_Adminhtml_Block_Widget_Grid_Container
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
        $this->_controller         = 'adminhtml_type';
        $this->_blockGroup         = 'ave_sizechart';
        parent::__construct();
        $this->_headerText         = Mage::helper('ave_sizechart')->__('Type');
        $this->_updateButton('add', 'label', Mage::helper('ave_sizechart')->__('Add Type'));

        $this->setTemplate('ave_sizechart/grid.phtml');
    }
}
