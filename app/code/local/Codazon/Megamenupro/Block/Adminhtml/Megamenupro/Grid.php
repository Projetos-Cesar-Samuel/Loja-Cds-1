<?php

class Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("megamenuproGrid");
				$this->setDefaultSort("menu_id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("megamenupro/megamenupro")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("menu_id", array(
					"header" => Mage::helper("megamenupro")->__("ID"),
					"align" =>"right",
					"width" => "50px",
					"type" => "number",
					"index" => "menu_id",
				));
                
				$this->addColumn("identifier", array(
					"header" => Mage::helper("megamenupro")->__("Identifier"),
					"index" => "identifier",
				));
				$this->addColumn("title", array(
					"header" => Mage::helper("megamenupro")->__("Title"),
					"index" => "title",
				));
				$this->addColumn('type', array(
					'header' => Mage::helper('megamenupro')->__('Type'),
					'index' => 'type',
					'type' => 'options',
					'options'=>Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid::getOptionArray3(),				
				));
				
				$this->addColumn('is_active', array(
					'header' => Mage::helper('megamenupro')->__('Status'),
					'index' => 'is_active',
					'type' => 'options',
					'options'=>Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid::getOptionArray4(),				
				));
						
				$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
				$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));
				$this->addExportType('*/*/exportMenuData', Mage::helper('sales')->__('Menu Data'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('menu_id');
			$this->getMassactionBlock()->setFormFieldName('menu_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_megamenupro', array(
					 'label'=> Mage::helper('megamenupro')->__('Remove Megamenupro'),
					 'url'  => $this->getUrl('*/adminhtml_megamenupro/massRemove'),
					 'confirm' => Mage::helper('megamenupro')->__('Are you sure?')
				));
			return $this;
		}
			
		static public function getOptionArray3()
		{
            $data_array=array(); 
			$data_array[0]='Horizontal';
			$data_array[1]='Vertical';
            $data_array[2]='Toggle';
            return($data_array);
		}
		static public function getValueArray3()
		{
            $data_array=array();
			foreach(Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid::getOptionArray3() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray4()
		{
            $data_array=array(); 
			$data_array[0]='Disable';
			$data_array[1]='Enable';
            return($data_array);
		}
		static public function getValueArray4()
		{
            $data_array=array();
			foreach(Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Grid::getOptionArray4() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}