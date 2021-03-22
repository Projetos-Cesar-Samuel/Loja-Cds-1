<?php

/**
 * Postcode Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_Postcode
 * @author      Magestore Developer
 */
class LA_Postcode_Block_Adminhtml_Postcode_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     *
     * @return LA_Postcode_Block_Adminhtml_Postcode_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        if (Mage::getSingleton('adminhtml/session')->getPostcodeData()) {
            $data = Mage::getSingleton('adminhtml/session')->getPostcodeData();
            Mage::getSingleton('adminhtml/session')->setPostcodeData(null);
        } elseif (Mage::registry('postcode_data')) {
            $data = Mage::registry('postcode_data')->getData();
        }
        $fieldset = $form->addFieldset('postcode_form', array(
            'legend'=>Mage::helper('postcode')->__('Item information')
        ));

        $fieldset->addField('title', 'text', array(
            'label'        => Mage::helper('postcode')->__('Title'),
            'class'        => 'required-entry',
            'required'    => true,
            'name'        => 'title',
        ));

        $fieldset->addField('postcode_from', 'text', array(
            'label'        => Mage::helper('postcode')->__('Postcode From'),
            'class'        => 'required-entry',
            'required'    => true,
            'name'        => 'postcode_from',
        ));

        $fieldset->addField('postcode_to', 'text', array(
            'label'        => Mage::helper('postcode')->__('Postcode To'),
            'class'        => 'required-entry',
            'required'    => true,
            'name'        => 'postcode_to',
        ));

       /* $fieldset->addField('filename', 'file', array(
            'label'        => Mage::helper('postcode')->__('File'),
            'required'    => false,
            'name'        => 'filename',
        ));*/

        $fieldset->addField('status', 'select', array(
            'label'        => Mage::helper('postcode')->__('Status'),
            'name'        => 'status',
            'values'    => Mage::getSingleton('postcode/status')->getOptionHash(),
        ));

        /*$fieldset->addField('content', 'editor', array(
            'name'        => 'content',
            'label'        => Mage::helper('postcode')->__('Content'),
            'title'        => Mage::helper('postcode')->__('Content'),
            'style'        => 'width:700px; height:500px;',
            'wysiwyg'    => false,
            'required'    => true,
        ));*/

        $form->setValues($data);
        return parent::_prepareForm();
    }
}