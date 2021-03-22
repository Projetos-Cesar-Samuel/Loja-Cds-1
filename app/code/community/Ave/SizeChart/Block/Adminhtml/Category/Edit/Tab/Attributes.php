<?php
 
/**
 * Category admin edit tab attributes block
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
*/
class Ave_SizeChart_Block_Adminhtml_Category_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the attributes for the form
     *
     * @access protected
     * @return void
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
     * @author averun <dev@averun.com>
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setDataObject(Mage::registry('current_category'));
        $fieldset = $form->addFieldset(
            'info',
            array(
                'legend' => Mage::helper('ave_sizechart')->__('Category of sizes Information'),
                'class' => 'fieldset-wide',
            )
        );
        $attributes = $this->getAttributes();
        foreach ($attributes as $attribute) {
            $attribute->setEntity(Mage::getResourceModel('ave_sizechart/category'));
        }

        $this->_setFieldset($attributes, $fieldset, array());
        $formValues = Mage::registry('current_category')->getData();
        if (!Mage::registry('current_category')->getId()) {
            foreach ($attributes as $attribute) {
                if (!isset($formValues[$attribute->getAttributeCode()])) {
                    $formValues[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                }
            }
        }

        $form->addValues($formValues);
        $form->setFieldNameSuffix('category');
        $this->setForm($form);
    }

    /**
     * prepare layout
     *
     * @access protected
     * @return void
     * @see Mage_Adminhtml_Block_Widget_Form::_prepareLayout()
     * @author averun <dev@averun.com>
     */
    protected function _prepareLayout()
    {
        Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );
        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('ave_sizechart/adminhtml_sizechart_renderer_fieldset_element')
        );
    }

    /**
     * get the additional element types for form
     *
     * @access protected
     * @return array()
     * @see Mage_Adminhtml_Block_Widget_Form::_getAdditionalElementTypes()
     * @author averun <dev@averun.com>
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'file'     => Mage::getConfig()->getBlockClassName(
                'ave_sizechart/adminhtml_category_helper_file'
            ),
            'image'    => Mage::getConfig()->getBlockClassName(
                'ave_sizechart/adminhtml_category_helper_image'
            ),
            'textarea' => Mage::getConfig()->getBlockClassName(
                'adminhtml/catalog_helper_form_wysiwyg'
            )
        );
    }

    /**
     * get current entity
     *
     * @access protected
     * @return Ave_SizeChart_Model_Category
     * @author averun <dev@averun.com>
     */
    public function getCategory()
    {
        return Mage::registry('current_category');
    }
}
