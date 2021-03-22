<?php
class Codazon_Megamenupro_Block_Adminhtml_Megamenupro_Edit_Fields_MenuItems_Types extends Mage_Adminhtml_Block_Template
{
	protected function _getWidthClass(){
		$widthClass[] = array('label' => '-', 'value' => '');
		for($i = 1; $i <= 24; $i++){
			$widthClass[] = array('label' => $this->__('Width %s %s: %s px',$i,str_repeat('&nbsp;',ceil(4/(floor($i/10)+1))),round(($i/24)*1200)), 'value' => $i);
		}
		return $widthClass;
	}
	public function _construct(){
		parent::_construct();
		
		$types = array(
			array(
				'title' => $this->__('Item Link'),
				'name' => 'link',
				'placeholder' => $this->__('<i class="fa fa-link"></i>'),
				'content' =>array(
					array('title' => $this->__('Menu Item Title'), 'name' => 'label', 'type' => 'text'),
					array('title' => $this->__('Menu Item URL'), 'name' => 'url', 'type' => 'text'),
					array('title' => $this->__('Custom CSS Class'), 'name' => 'class', 'type' => 'text'),
					array('title' => $this->__('Menu Icon Item'), 'type' => 'label', 'value' => 'For example <i class="fa fa-diamond"></i> Diamond' ),
					array('title' => $this->__('Icon Item use'), 'name' => 'icon_type', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Get icon from Awesome font Library'), 'value' => '0'),
							array('label' => $this->__('Get icon from Image library or other URL'), 'value' => '1'),
						),
						'action' => 'cdzmenu.switchIconChooser(this);'
					),
					array('title' => $this->__(''), 'name' => 'icon_font', 'type' => 'icon'),
					array('title' => $this->__(''), 'name' => 'icon_img', 'type' => 'image', 'style' => 'display:none', 'button_text' => $this->__('Image Icons Library'), 'description' => 'Recommended size: at least 32px &times 32px')
				)
			),
			array(
				'title' => $this->__('Dropdown Content'),
				'name' => 'text',
				'placeholder' => $this->__('<i class="fa fa-file-code-o"></i>'),
				'content' => array(
					array('title' => $this->__('Custom CSS Class'), 'name' => 'class', 'type' => 'text'),
					array('title' => $this->__('Custom CSS Inline Style'), 'name' => 'style', 'type' => 'text',  'placeholder' => 'padding:20px; margin:30px;'),
					array('title' => $this->__('Dropdown Width'), 'name' => 'width_class', 'type' => 'dropdown',
						'values' => $this->_getWidthClass()
					),
					array('title' => $this->__('Dropdown Content'), 'type' => 'heading'),
					array('title' => $this->__('Layout'), 'name' => 'layout', 'type' => 'layout',
						'layouts' => array(array(1),array(1,1),array(1,1,1),array(1,1,1,1),array(1,1,1,1,1,1),array(1,2),array(2,1),array(1,1,2),array(2,1,1),array(1,2,1),array(1,1,1,1,2),array(2,1,1,1,1))),
					array('title' => $this->__(''), 'name' => 'content', 'type' => 'editor', 'columns' => 1, 'value' => ''),
					array('title' => $this->__('<span style="margin-top: 12.5px;display: inline-block;">Background for content</span>'), 'type' => 'label',
						'value' => 'For example <a class="full-view-img" data-href="'.$this->getImageUrl('menu/background.jpg').'" onclick="cdzmenu.viewfull(this)" href="javascript:void(0)"><img src="'.$this->getImageUrl('menu/background_small.jpg').'" /></a> <a class="full-view-link" onclick="cdzmenu.viewfull(this)" data-href="'.$this->getImageUrl('menu/background.jpg').'" href="javascript:void(0)">'.$this->__('Click to view example').'</a>' ),
					array('title' => $this->__('Image'), 'name' => 'background', 'type' => 'image' ),
					array('title' => $this->__('Position'), 'name' => 'bg_position', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Left - Top'), 'value' => 'left_top'),
							array('label' => $this->__('Left - Bottom'), 'value' => 'left_bottom'),
							array('label' => $this->__('Right - Top'), 'value' => 'right_top'),
							array('label' => $this->__('Right - Bottom'), 'value' => 'right_bottom'),
						)
					),
					array('title' => $this->__('X (px)'), 'name' => 'bg_position_x', 'type' => 'text', 'value' => '0'),
					array('title' => $this->__('Y (px)'), 'name' => 'bg_position_y', 'type' => 'text', 'value' => '0')
				)
			),
			array(
				'title' => $this->__('Tabs Container'),
				'name' => 'tab_container',
				'placeholder' => $this->__('<i class="fa fa-folder-o"></i>'),
				'content' => array(
					array('title' => $this->__('Custom CSS Class'), 'name' => 'class', 'type' => 'text'),
					array('title' => $this->__('Custom CSS Inline Style'), 'name' => 'style', 'type' => 'text',  'placeholder' => 'padding:20px; margin:30px;'),
					array('title' => $this->__('Tabs Width'), 'name' => 'width_class', 'type' => 'dropdown',
						'values' => $this->_getWidthClass()
					),
					array('title' => $this->__('Tab type'), 'name' => 'tab_type', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Horizontal'), 'value' => '0'),
							array('label' => $this->__('Vertical'), 'value' => '1'),
						)
					),
					array('title' => $this->__('<span style="margin-top: 12.5px;display: inline-block;">Background for content</span>'), 'type' => 'label',
						'value' => 'For example <a class="full-view-img" data-href="'.$this->getImageUrl('menu/background.jpg').'" onclick="cdzmenu.viewfull(this)" href="javascript:void(0)"><img src="'.$this->getImageUrl('menu/background_small.jpg').'" /></a> <a class="full-view-link" onclick="cdzmenu.viewfull(this)" data-href="'.$this->getImageUrl('menu/background.jpg').'" href="javascript:void(0)">'.$this->__('Click to view example').'</a>' ),
					array('title' => $this->__('Image'), 'name' => 'background', 'type' => 'image' ),
					array('title' => $this->__('Position'), 'name' => 'bg_position', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Left - Top'), 'value' => 'left_top'),
							array('label' => $this->__('Left - Bottom'), 'value' => 'left_bottom'),
							array('label' => $this->__('Right - Top'), 'value' => 'right_top'),
							array('label' => $this->__('Right - Bottom'), 'value' => 'right_bottom'),
						)
					),
					array('title' => $this->__('X (px)'), 'name' => 'bg_position_x', 'type' => 'text', 'value' => '0'),
					array('title' => $this->__('Y (px)'), 'name' => 'bg_position_y', 'type' => 'text', 'value' => '0')
				)
			),
			array(
				'title' => $this->__('Tab Item'),
				'name' => 'tab_item',
				'placeholder' => $this->__('<i class="fa fa-folder-o"></i>'),
				'content' => array(
					array('title' => $this->__('Tab Title'), 'name' => 'label', 'type' => 'text'),
					array('title' => $this->__('Tab URL'), 'name' => 'url', 'type' => 'text'),
					array('title' => $this->__('Custom CSS Class'), 'name' => 'class', 'type' => 'text'),
					array('title' => $this->__('Tab Content'), 'type' => 'heading'),
					array('title' => $this->__('Layout'), 'name' => 'layout', 'type' => 'layout',
						'layouts' => array(array(1),array(1,1),array(1,1,1),array(1,1,1,1),array(1,1,1,1,1,1),array(1,2),array(2,1),array(1,1,2),array(2,1,1),array(1,2,1),array(1,1,1,1,2),array(2,1,1,1,1))),
					array('title' => $this->__(''), 'name' => 'content', 'type' => 'editor', 'columns' => 1, 'value' => ''),
					array('title' => $this->__('<span style="margin-top: 12.5px;display: inline-block;">Background for content</span>'), 'type' => 'label',
						'value' => 'For example <a class="full-view-img" data-href="'.$this->getImageUrl('menu/background.jpg').'" onclick="cdzmenu.viewfull(this)" href="javascript:void(0)"><img src="'.$this->getImageUrl('menu/background_small.jpg').'" /></a> <a class="full-view-link" onclick="cdzmenu.viewfull(this)" data-href="'.$this->getImageUrl('menu/background.jpg').'" href="javascript:void(0)">'.$this->__('Click to view example').'</a>' ),
					array('title' => $this->__('Image'), 'name' => 'background', 'type' => 'image' ),
					array('title' => $this->__('Position'), 'name' => 'bg_position', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Left - Top'), 'value' => 'left_top'),
							array('label' => $this->__('Left - Bottom'), 'value' => 'left_bottom'),
							array('label' => $this->__('Right - Top'), 'value' => 'right_top'),
							array('label' => $this->__('Right - Bottom'), 'value' => 'right_bottom'),
						)
					),
					array('title' => $this->__('X (px)'), 'name' => 'bg_position_x', 'type' => 'text', 'value' => '0'),
					array('title' => $this->__('Y (px)'), 'name' => 'bg_position_y', 'type' => 'text', 'value' => '0'),
					array('title' => $this->__('Menu Icon Item'), 'type' => 'label', 'value' => 'For example <i class="fa fa-diamond"></i> Diamond' ),
					array('title' => $this->__('Icon Item use'), 'name' => 'icon_type', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Get icon from Awesome font Library'), 'value' => '0'),
							array('label' => $this->__('Get icon from Image library or other URL'), 'value' => '1'),
						),
						'action' => 'cdzmenu.switchIconChooser(this);'
					),
					array('title' => $this->__(''), 'name' => 'icon_font', 'type' => 'icon'),
					array('title' => $this->__(''), 'name' => 'icon_img', 'type' => 'image', 'style' => 'display:none', 'button_text' => $this->__('Image Icons Library'), 'description' => 'Recommended size: at least 32px &times 32px')
				)
			),
			array(
				'title' => $this->__('Categories List'),
				'name' => 'category',
				'placeholder' => $this->__('<i class="fa fa-th-list"></i>'),
				'content' => array(
					array('title' => $this->__('Menu Item Title'), 'name' => 'label', 'type' => 'text'),
					array('title' => $this->__('URL'), 'name' => 'url', 'type' => 'text'),
					array('title' => $this->__('Parent Cat ID'), 'name' => 'category', 'type' => 'category'),
					array('title' => $this->__('Maximum Depth'), 'name' => 'max_depth', 'type' => 'text', 'description' => $this->__('Leave empty or set value "0" to get all category depths')),
					array('title' => $this->__('Custom CSS Class'), 'name' => 'class', 'type' => 'text'),
					array('title' => $this->__('Display Type'), 'name' => 'display_type', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Show categories list as a drop down menu of item title'), 'value' => 0),
							array('label' => $this->__('Show categories list just below item title'), 'value' => 1)
						),
						'description' => '<p>For example:</p><p><a class="full-view-link" style="text-decoration: none;" onclick="cdzmenu.viewfull(this)" data-href="'.$this->getImageUrl('menu/category_list_type_1.jpg').'" href="javascript:void(0)"><i class="fa fa-hand-pointer-o"></i> '.$this->__('Show categories list as a drop down menu of item title').'</a></p><p><a style="text-decoration: none;" class="full-view-link" onclick="cdzmenu.viewfull(this)" data-href="'.$this->getImageUrl('menu/category_list_type_2.jpg').'" href="javascript:void(0)"><i class="fa fa-hand-pointer-o"></i> '.$this->__('Show categories list just below item title').'</a></p>'
					),
					array('title' => $this->__('Menu Icon Item'), 'type' => 'label', 'value' => 'For example <i class="fa fa-diamond"></i> Diamond' ),
					array('title' => $this->__(''), 'name' => 'icon_type', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Get icon from Awesome font Library'), 'value' => '0'),
							array('label' => $this->__('Get icon from Image library or other URL'), 'value' => '1'),
						),
						'action' => 'cdzmenu.switchIconChooser(this);'
					),
					array('title' => $this->__(''), 'name' => 'icon_font', 'type' => 'icon'),
					array('title' => $this->__(''), 'name' => 'icon_img', 'type' => 'image', 'style' => 'display:none', 'button_text' => $this->__('Image Icons Library'), 'description' => 'Recommended size: at least 32px &times 32px'),
				)
			),
			array(
				'title' => $this->__('Bootstrap Row'),
				'name' => 'row',
				'placeholder' => $this->__('<i class="fa fa-bars"></i>'),
				'content' =>array(
					array('title' => $this->__('Custom CSS Class'), 'name' => 'class', 'type' => 'text'),
					array('title' => $this->__('Custom CSS Inline Style'), 'name' => 'style', 'type' => 'text', 'placeholder' => 'padding:20px; margin:30px;'),
					array('title' => $this->__('<span style="margin-top: 12.5px;display: inline-block;">Background for content</span>'), 'type' => 'label',
						'value' => 'For example <a class="full-view-img" data-href="'.$this->getImageUrl('menu/background.jpg').'" onclick="cdzmenu.viewfull(this)" href="javascript:void(0)"><img src="'.$this->getImageUrl('menu/background_small.jpg').'" /></a> <a class="full-view-link" onclick="cdzmenu.viewfull(this)" data-href="'.$this->getImageUrl('menu/background.jpg').'" href="javascript:void(0)">'.$this->__('Click to view example').'</a>' ),
					array('title' => $this->__('Image'), 'name' => 'background', 'type' => 'image' ),
					array('title' => $this->__('Position'), 'name' => 'bg_position', 'type' => 'dropdown',
						'values' => array(
							array('label' => $this->__('Left - Top'), 'value' => 'left_top'),
							array('label' => $this->__('Left - Bottom'), 'value' => 'left_bottom'),
							array('label' => $this->__('Right - Top'), 'value' => 'right_top'),
							array('label' => $this->__('Right - Bottom'), 'value' => 'right_bottom'),
						)
					),
					array('title' => $this->__('X (px)'), 'name' => 'bg_position_x', 'type' => 'text', 'value' => '0'),
					array('title' => $this->__('Y (px)'), 'name' => 'bg_position_y', 'type' => 'text', 'value' => '0')
				)
			),
			array(
				'title' => $this->__('Bootstrap Column'),
				'placeholder' => $this->__('<i class="fa fa-columns"></i>'),
				'name' => 'col',
				'content' =>array(
					array('title' => $this->__('Custom CSS Class'), 'name' => 'class', 'type' => 'text'),
					array('title' => $this->__('Custom CSS Inline Style'), 'name' => 'style', 'type' => 'text',  'placeholder' => 'padding:20px; margin:30px;'),
				)
			),
		);
		$this->setItemTypes($types);
	}
	public function getMediaUrl(){
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
	}
	public function addNewType($type){
		array_push($this->_itemTypes,$type);
		return $this;
	}
	public function setItemTypes($types){
		$this->_itemTypes = $types;
		return $this;	
	}
	public function getItemTypes(){
		return $this->_itemTypes;	
	}
	public function getItemTypesJson(){
		return json_encode($this->_itemTypes);
	}
	
	public function getColumnTemplates(){
		return
		array(
			array(
				'title' => $this->__('Aplly for All'),
				'type' => 'heading'	
			),
			array(
				'title' => $this->__('Image - Title - Paragraph'),
				'type' => 'layout01',
				'image' => $this->getImageUrl('column_layout/layout_01.png'),
				'col' => 1
			),
			array(
				'title' => $this->__('Image - Title - List'),
				'type' => 'layout02',
				'image' => $this->getImageUrl('column_layout/layout_02.png'),
				'col' => 1
			),
			array(
				'title' => $this->__('Title - Paragraph'),
				'type' => 'layout03',
				'image' => $this->getImageUrl('column_layout/layout_03.png'),
				'col' => 1
			),
			array(
				'title' => $this->__('Title - List'),
				'type' => 'layout04',
				'image' => $this->getImageUrl('column_layout/layout_04.png'),
				'col' => 1
			),
			array(
				'title' => $this->__('Title - Paragraph - Image'),
				'type' => 'layout05',
				'image' => $this->getImageUrl('column_layout/layout_05.png'),
				'col' => 1
			),
			array(
				'title' => $this->__('Title - List - Image'),
				'type' => 'layout06',
				'image' => $this->getImageUrl('column_layout/layout_06.png'),
				'col' => 1
			),
			/*array(
				'title' => $this->__('Apply for single column layout'),
				'type' => 'heading'	
			),
			array(
				'title' => $this->__('Paragraph - Image'),
				'type' => 'layout07',
				'image' => $this->getImageUrl('column_layout/layout_07.png'),
				'col' => 2
			),
			array(
				'title' => $this->__('List - Image'),
				'type' => 'layout08',
				'image' => $this->getImageUrl('column_layout/layout_08.png'),
				'col' => 2
			),
			array(
				'title' => $this->__('Image - List'),
				'type' => 'layout09',
				'image' => $this->getImageUrl('column_layout/layout_09.png'),
				'col' => 2
			) */
		);
	}
	public function getImageUrl($path){
		return $this->getSkinUrl('codazon/megamenupro/images/'.$path);
	}

}
