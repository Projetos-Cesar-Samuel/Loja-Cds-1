<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Model_Import extends Mage_Catalog_Model_Abstract
{
    protected function _construct()
    {
        $this->fixtureDir = Mage::getModuleDir('', 'Codazon_Flexibletheme') . DS . 'fixtures' . DS;
        $this->version = '0.0.1';
        $this->io = new Varien_Io_File();
        $this->csvReader = new Varien_File_Csv();
        $this->rootDir = Mage::getBaseDir('base');
    }
    
    public function importAll()
    {
        try {
            $this->importMainContents();
        } catch(Exception $e) {
            echo "Import Main Content: " . $e->getMessage();
            die();
        }
        
        try {
            $this->importHeaders();
        } catch(Exception $e) {
            echo "Import Header: " . $e->getMessage();
            die();
        }
        
        try {
            $this->importFooters();
        } catch(Exception $e) {
            echo "Import Footer: " . $e->getMessage();
            die();
        }
        
        try {
            $this->importThemes();
        } catch(Exception $e) {
            echo "Import Themes: " . $e->getMessage();
            die();
        }
        
        try {
            $this->importCmsPages();
        } catch(Exception $e) {
            echo "Import CMS Page: " . $e->getMessage();
            die();
        }
        
        try {
            $this->importCmsBlocks();
        } catch(Exception $e) {
            echo "Import CMS Block: " . $e->getMessage();
            die();
        }
        
        try {
            $this->importBlogCategories();
        } catch(Exception $e) {
            echo "Import Blog Categories: " . $e->getMessage();
            die();
        }
        
        try {
            $this->importBlogTags();
        } catch(Exception $e) {
            echo "Import Blog Tags: " . $e->getMessage();
            die();
        }
        
        
        try {
            $this->importBlogPosts();
        } catch(Exception $e) {
            echo "Import Blog Posts: " . $e->getMessage();
            die();
        }
        
        try {
            $this->importMenus();
        } catch(Exception $e) {
            echo "Import Menus: " . $e->getMessage();
            die();
        }
        try {
            $this->importPermissionBlocks();
        } catch(Exception $e) {
            //echo "Import Permission Blocks: " . $e->getMessage();
            //die();
        }
        
    }
    
    public function getFixture($file = '')
    {
        return $this->fixtureDir . $file;
    }
    
    public function importMainContents()
    {
        $file = $this->getFixture('flexibletheme_content_entity.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('flexibletheme/content');
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $model->setStoreId(0);
            if (Mage::getModel('flexibletheme/content')->getCollection()->addFieldToFilter('identifier', $data['identifier'])->count()) {
                continue;
            }
            
            $model->addData($data)->save();
            $model->unsetData();
        }
    }
    
    public function importHeaders()
    {
        $file = $this->getFixture('flexibletheme_header_entity.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('flexibletheme/header');
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $model->setStoreId(0);
            if (Mage::getModel('flexibletheme/header')->getCollection()->addFieldToFilter('identifier', $data['identifier'])->count()) {
                continue;
            }
            
            $model->addData($data)->save();
            $model->unsetData();
        }
    }
    
    public function importFooters()
    {
        $file = $this->getFixture('flexibletheme_footer_entity.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('flexibletheme/footer');
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $model->setStoreId(0);
            if (Mage::getModel('flexibletheme/footer')->getCollection()->addFieldToFilter('identifier', $data['identifier'])->count()) {
                continue;
            }
            
            $model->addData($data)->save();
            $model->unsetData();
        }
    }
    
    public function importThemes()
    {
        $file = $this->getFixture('flexibletheme_theme.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('flexibletheme/theme');
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $model->setStoreId(0);
            if (Mage::getModel('flexibletheme/theme')->load($data['theme_title'], 'theme_title')->getId()) {
                continue;
            }
            
            $model->addData($data)->save();
            $model->unsetData();
        }
    }
    
    public function importCmsPages()
    {
        $file = $this->getFixture('cms_page.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('cms/page');
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $model->setStoreId(0);
            if (Mage::getModel('cms/page')->load($data['identifier'], 'identifier')->getId()) {
                continue;
            }
            
            $model->addData($data)->save();
            $model->unsetData();
        }
    }
    
    public function importCmsBlocks()
    {
        $file = $this->getFixture('cms_block.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('cms/block');
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $model->setStoreId(0);
            if (Mage::getModel('cms/block')->load($data['identifier'], 'identifier')->getId()) {
                continue;
            }
            
            $model->addData($data)->save();
            $model->unsetData();
        }
    }
    
    public function importBlogCategories()
    {
        $file = $this->getFixture('aw_blog_cat.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('blog/cat');
        $stores = Mage::getSingleton('adminhtml/system_store')->getStoreCollection();
        foreach ($stores as $store) {
            $storesArray[] = $store->getId();
        }

        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            if (Mage::getModel('blog/cat')->load($data['identifier'], 'identifier')->getId()) {
                continue;
            }
            
            $data['stores'] = $storesArray;
            
            $model->addData($data);
			$model->save();
            $model->unsetData();
        }
    }
    
    public function importBlogTags()
    {
        $file = $this->getFixture('aw_blog_tags.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('blog/tag');
        
        foreach ($rows as $row) {
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            if (Mage::getModel('blog/tag')->load($data['tag'], 'tag')->getId()) {
                continue;
            }
            
            $data['store_id'] = 0;
            
            $model->addData($data);
			$model->save();
            $model->unsetData();
        }
    }
    
    public function importBlogPosts()
    {
        $file = $this->getFixture('aw_blog.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        $model = Mage::getModel('blog/post');
		$date = Mage::getModel('core/date')->gmtDate();
				
        foreach ($rows as $row) {
            $data = array();
            $data['stores'] = array(0);
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            if (Mage::getModel('blog/post')->load($data['identifier'], 'identifier')->getId()) {
                continue;
            }
						
            if ($data['categories']) {
                $data['cats'] = array();
                $categories = explode(',', $data['categories']);
                if (count($categories)) {
                    foreach ($categories as $catIdentifier) {
                        $cat = Mage::getModel('blog/cat')->load($catIdentifier, 'identifier');
                        if ($cat->getId()) {
                            $data['cats'][] = $cat->getId();
                        }
                    }
                }
            }
			$model->setCreatedTime($date);
            $model->addData($data)->save();
            $model->unsetData();
        }
    }
    
    public function importMenus()
    {
        $file = $this->getFixture('codazon_megamenu.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        
        foreach ($rows as $row) {
			$model = Mage::getModel('megamenupro/megamenupro');
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            if ($model->load($data['identifier'], 'identifier')->getId()) {
                continue;
            }
			unset($data['menu_id']);
            $model->addData($data);
			$model->save();
            $model->unsetData();
        }
    }
    
    public function importPermissionBlocks()
    {
        $file = $this->getFixture('permission_block.csv');
        if (!file_exists($file)) {
            return false;
        }
        $rows = $this->csvReader->getData($file);
        $header = array_shift($rows);
        
        foreach ($rows as $row) {
			$model = Mage::getModel('admin/block');
            $data = array();
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            if ($model->load($data['block_name'], 'block_name')->getId()) {
                continue;
            }
            $model->addData($data);
			$model->save();
            $model->unsetData();
        }
    }
}