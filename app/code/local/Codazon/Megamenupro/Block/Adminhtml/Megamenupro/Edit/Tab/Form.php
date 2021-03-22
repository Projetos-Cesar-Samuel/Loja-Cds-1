<?php
class Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("megamenupro_form", array("legend"=>Mage::helper("megamenupro")->__("Item information")));

				
						$fieldset->addField("menu_id", "text", array(
						"label" => Mage::helper("megamenupro")->__("ID"),
						"name" => "menu_id",
						));
					
						$fieldset->addField("identifier", "text", array(
						"label" => Mage::helper("megamenupro")->__("Identifier"),
						"name" => "identifier",
						));
					
						$fieldset->addField("title", "text", array(
						"label" => Mage::helper("megamenupro")->__("Title"),
						"name" => "title",
						));
									
						 $fieldset->addField('type', 'select', array(
						'label'     => Mage::helper('megamenupro')->__('Type'),
						'values'   => Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid::getValueArray3(),
						'name' => 'type',
						));				
						 $fieldset->addField('is_active', 'select', array(
						'label'     => Mage::helper('megamenupro')->__('Status'),
						'values'   => Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid::getValueArray4(),
						'name' => 'is_active',
						));

				if (Mage::getSingleton("adminhtml/session")->getMegamenuproData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getMegamenuproData());
					Mage::getSingleton("adminhtml/session")->setMegamenuproData(null);
				} 
				elseif(Mage::registry("megamenupro_data")) {
				    $form->setValues(Mage::registry("megamenupro_data")->getData());
				}
				return parent::_prepareForm();
		}
}
