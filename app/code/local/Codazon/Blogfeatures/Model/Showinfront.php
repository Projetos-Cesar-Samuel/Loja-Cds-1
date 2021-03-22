<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Blogfeatures_Model_Showinfront
{
    public function toOptionArray()
	{
		return array(
            array('value' => 'post_image',      'label' => __('Thumbnail')),
            array('value' => 'title',           'label' => __('Title') ),
            array('value' => 'short_content',   'label' => __('Short Content')),
            array('value' => 'publish_time',    'label' => __('Create Date')),
            array('value' => 'author',          'label'  => __('Author') ),
            array('value' => 'category',        'label' => __('Categories'))
         );
	}
    
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
?>