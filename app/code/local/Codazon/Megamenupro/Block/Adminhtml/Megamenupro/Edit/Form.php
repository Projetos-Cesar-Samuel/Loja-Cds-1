<?php
class Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
        protected function _prepareLayout()
        {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            return parent::_prepareLayout();
        }
        
		protected function _prepareForm()
		{
				$form = new Varien_Data_Form(array(
					"id" => "edit_form",
					"action" => $this->getUrl("*/*/save", array("id" => $this->getRequest()->getParam("id"))),
					"method" => "post",
					"enctype" =>"multipart/form-data",
					)
				);
				$form->setUseContainer(true);
				$this->setForm($form);
				$fieldset = $form->addFieldset("megamenupro_form", array("legend"=>Mage::helper("megamenupro")->__("Item information")));

				
						$fieldset->addField("menu_id", "label", array(
							"label" => Mage::helper("megamenupro")->__("ID"),
							"name" => "menu_id",
						));
					
						$fieldset->addField("identifier", "text", array(
							"label" => Mage::helper("megamenupro")->__("Identifier"),
							"name" => "identifier",
							"required" => true
						));
					
						$fieldset->addField("title", "text", array(
							"label" => Mage::helper("megamenupro")->__("Title"),
							"name" => "title",
							"required" => true
						));
									
						$fieldset->addField('type', 'select', array(
							'label'     => Mage::helper('megamenupro')->__('Type'),
							'values'   => Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid::getValueArray3(),
							'name' => 'type',
						));
						$fieldset->addField('css_class','text',array(
							'label' => $this->__('Wrapper CSS Class'),
							'title' => $this->__('Wrapper CSS Class'),
							'name' => 'css_class',
							'required' => false
						));
						$fieldset->addField('dropdown_style','select',array(
							'label' => __('Dropdown Style'),
							'title' => __('Dropdown Animation'),
							'name' => 'dropdown_style',
							'options' => array('auto_width' => 'Auto Width', 'full_width' => __('Full Width')),
							'required' => false
						));
						$fieldset->addField('dropdown_animation','select',array(
							'label' => $this->__('Dropdown Animation'),
							'title' => $this->__('Dropdown Animation'),
							'name' => 'dropdown_animation',
							'options' => array('normal' => $this->__('Normal'), 'fade' => $this->__('Fade'),'slide' => $this->__('Slide'), 'translate' => $this->__('Translate')),
							'required' => false
						));
						$fieldset->addField('is_active', 'select', array(
							'label'     => Mage::helper('megamenupro')->__('Status'),
							'values'   => Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid::getValueArray4(),
							'name' => 'is_active'
						));
						$fieldset->addField('content', 'hidden', array(
							'name' => 'content'
						));
				if (Mage::getSingleton("adminhtml/session")->getMegamenuproData()) {
					$form->setValues(Mage::getSingleton("adminhtml/session")->getMegamenuproData());
					Mage::getSingleton("adminhtml/session")->setMegamenuproData(null);
				} elseif($model = Mage::registry("megamenupro_data")) {
					$stylesVars = array('css_class','dropdown_animation','dropdown_style');
					if($style = $model->getData('style')){
						$style = json_decode($style);
						foreach($stylesVars as $stylesVar){
							if( isset($style->{$stylesVar}) ){
								$model->setData($stylesVar,$style->{$stylesVar});
							}
						}
					}
				    $form->setValues($model->getData());
				}
				return parent::_prepareForm();
		}
		
}
