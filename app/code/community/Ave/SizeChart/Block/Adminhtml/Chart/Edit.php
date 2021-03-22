<?php

/**
 * Chart admin edit form
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Block_Adminhtml_Chart_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @access public
     * @author averun <dev@averun.com>
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'ave_sizechart';
        $this->_controller = 'adminhtml_chart';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('ave_sizechart')->__('Save Chart')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('ave_sizechart')->__('Delete Chart')
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
        if (Mage::registry('current_chart') && Mage::registry('current_chart')->getId()) {
            return Mage::helper('ave_sizechart')->__(
                "Edit Chart '%s'",
                $this->escapeHtml(Mage::registry('current_chart')->getName())
            );
        } else {
            return Mage::helper('ave_sizechart')->__('Add Chart');
        }
    }
}
