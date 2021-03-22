<?php
 
/**
 * Chart admin edit tab attributes block
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
*/
class Ave_SizeChart_Block_Adminhtml_Chart_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Widget_Form
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
        $form->setDataObject(Mage::registry('current_chart'));
        $fieldset = $form->addFieldset(
            'info',
            array(
                'legend' => Mage::helper('ave_sizechart')->__('Chart Information'),
                'class' => 'fieldset-wide',
            )
        );
        $attributes = $this->getAttributes();
        foreach ($attributes as $attribute) {
            $attribute->setEntity(Mage::getResourceModel('ave_sizechart/chart'));
        }

        $this->_setFieldset($attributes, $fieldset, array());
        $formValues = Mage::registry('current_chart')->getData();
        if (!Mage::registry('current_chart')->getId()) {
            foreach ($attributes as $attribute) {
                if (!isset($formValues[$attribute->getAttributeCode()])) {
                    $formValues[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                }
            }
        }

        $form->addValues($formValues);
        $form->setFieldNameSuffix('chart');
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
                'ave_sizechart/adminhtml_chart_helper_file'
            ),
            'image'    => Mage::getConfig()->getBlockClassName(
                'ave_sizechart/adminhtml_chart_helper_image'
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
     * @return Ave_SizeChart_Model_Chart
     * @author averun <dev@averun.com>
     */
    public function getChart()
    {
        return Mage::registry('current_chart');
    }

    /**
     * get after element html
     *
     * @access protected
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @author averun <dev@averun.com>
     */
    protected function _getAdditionalElementHtml($element)
    {
        if ($element->getName() == 'category_id') {
            $html = '<a href="{#url}" id="category_id_link" target="_blank"></a>';
            $html .= '<script type="text/javascript">
            function changeCategoryIdLink() {
                if ($(\'category_id\').value == \'\') {
                    $(\'category_id_link\').hide();
                } else {
                    $(\'category_id_link\').show();
                    var url = \''
                            .$this->getUrl('adminhtml/sizechart_category/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'category_id\').value);
                    $(\'category_id_link\').href = realUrl;
                    $(\'category_id_link\').innerHTML = text.replace(\'{#name}\', ' .
                                            ' $(\'category_id\').options[$(\'category_id\').selectedIndex].innerHTML);
                }
            }
            $(\'category_id\').observe(\'change\', changeCategoryIdLink);
            changeCategoryIdLink();
            </script>';
            return $html;
        }

        if ($element->getName() == 'type_id') {
            $html = '<a href="{#url}" id="type_id_link" target="_blank"></a>';
            $html .= '<script type="text/javascript">
            function changeTypeIdLink() {
                if ($(\'type_id\').value == \'\') {
                    $(\'type_id_link\').hide();
                } else {
                    $(\'type_id_link\').show();
                    var url = \''.$this->getUrl('adminhtml/sizechart_type/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'type_id\').value);
                    $(\'type_id_link\').href = realUrl;
                    $(\'type_id_link\').innerHTML = text.replace(\'{#name}\',' .
                                                    ' $(\'type_id\').options[$(\'type_id\').selectedIndex].innerHTML);
                }
            }
            $(\'type_id\').observe(\'change\', changeTypeIdLink);
            changeTypeIdLink();
            </script>';
            return $html;
        }

        return '';
    }
}
