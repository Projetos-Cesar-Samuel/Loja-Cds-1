<?php

/**
 * Type admin attribute block
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Type_Attribute extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @author averun <dev@averun.com>
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_type_attribute';
        $this->_blockGroup = 'ave_sizechart';
        $this->_headerText = Mage::helper('ave_sizechart')->__('Manage Type Attributes');
        parent::__construct();
        $this->_updateButton(
            'add',
            'label',
            Mage::helper('ave_sizechart')->__('Add New Type Attribute')
        );
    }
}
