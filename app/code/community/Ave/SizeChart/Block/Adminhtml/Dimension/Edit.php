<?php

/**
 * Dimension admin edit form
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Dimension_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        parent::__construct();
        $this->_blockGroup = 'ave_sizechart';
        $this->_controller = 'adminhtml_dimension';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('ave_sizechart')->__('Save Dimension')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('ave_sizechart')->__('Delete Dimension')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('ave_sizechart')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author averun <dev@averun.com>
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_dimension') && Mage::registry('current_dimension')->getId()) {
            return Mage::helper('ave_sizechart')->__(
                "Edit Dimension '%s'",
                $this->escapeHtml(Mage::registry('current_dimension')->getName())
            );
        } else {
            return Mage::helper('ave_sizechart')->__('Add Dimension');
        }
    }
}
