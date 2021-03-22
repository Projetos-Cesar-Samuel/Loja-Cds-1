<?php

class Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Exportcsv extends Mage_Adminhtml_Block_Widget_Grid
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
		$this->addColumn("identifier", array(
			"header" => Mage::helper("megamenupro")->__("identifier"),
			"index" => "identifier",
		));
		$this->addColumn("title", array(
			"header" => Mage::helper("megamenupro")->__("title"),
			"index" => "title",
		));
		$this->addColumn('type', array(
			'header' => Mage::helper('megamenupro')->__('type'),
			'index' => 'type'			
		));
		
		$this->addColumn('is_active', array(
			'header' => Mage::helper('megamenupro')->__('is_active'),
			'index' => 'is_active'		
		));
		
		$this->addColumn('content', array(
			'header' => Mage::helper('megamenupro')->__('content'),
			'index' => 'content'		
		));
		$this->addColumn('style', array(
			'header' => Mage::helper('megamenupro')->__('style'),
			'index' => 'style'		
		));
				
		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
	}
}