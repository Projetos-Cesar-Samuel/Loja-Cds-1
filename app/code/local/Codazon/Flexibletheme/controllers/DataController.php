<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_DataController extends Mage_Core_Controller_Front_Action
{
    protected $model;
    
    public function getModel()
    {
        if ($this->model === null) {
            $this->model = Mage::getSingleton('flexibletheme/export');
            if ($version = $this->getRequest()->getParam('version')) {
                $this->model->setVersion($version);
            }
        }
        return $this->model;
    }
    
    public function exportAction()
    {
        set_time_limit(3600);
        $request = $this->getRequest();
        $model = $this->getModel();
        if ($request->getParam('export_product_images')) {
            $this->exportProductImages();
        } elseif ($request->getParam('export_full')) {
            $this->exportFull();
        } else {
            if (!$request->getParam('only_pack_theme')) {
                $this->exportData();
                $this->packTheme();
            } else {
                $this->packTheme();
                echo $this->__('Successfully');
            }
        }
    }
    
    public function importAction()
    {
        //$model = Mage::getSingleton('flexibletheme/import');
        //$model->importAll();
    }
    
    
    public function exportProductImages()
    {
        $result = $this->getModel()->packProductImages();
    }
    
    public function exportFull()
    {
        
        if ($this->getRequest()->getParam('skip_export_database')) {
            $this->getModel()->setData('skip_export_database', 1);
        }
        $result = $this->getModel()->packFull();
    }
    
    public function packTheme()
    {
        $this->getModel()->packTheme();
    }
    
    
    public function exportData()
    {
        
        echo "<style>
        .section {
            width: 20%;
            box-sizing: border-box;
            padding:10px 10px
        }
        .wrap{
            display: flex;
            display: -ms-flex;
            display: -webkit-flex;
            flex-wrap: wrap;
            -webkit-flex-wrap: wrap;
            -ms-flex-align: stretch;
            -webkit-align-items: stretch;
            -moz-align-items: stretch;
            -ms-align-items: stretch;
            -o-align-items: stretch;
            align-items: stretch;
        }
        p.item {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 5px 0;
            font-family: Arial;
            font-size: 12px;
        }
        </style>
        ";
        
        $model = $this->getModel();

        echo '<div class="wrap">';
        $model->printResult('Export Main Contents', $model->exportMainContents());
        $model->printResult('Export Headers', $model->exportHeaders());
        $model->printResult('Export Footers', $model->exportFooters());
        $model->printResult('Export Themes', $model->exportThemes());
        $model->printResult('Export CMS Pages', $model->exportCmsPages());
        $model->printResult('Export CMS Blocks', $model->exportCmsBlocks());
        $model->printResult('Export Blog Categories', $model->exportBlogCategories());
        $model->printResult('Export Blog Tags', $model->exportBlogTags());
        $model->printResult('Export Blog Posts', $model->exportBlogPosts());
        $model->printResult('Export Menus', $model->exportMenus());
        $model->printResult('Export Permission Blocks', $model->exportPermissionBlocks());
        echo "</div>";

    }
}