<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Productfilterpro_Model_Source_Filtertypes
{
	public function toOptionArray(){
		return array(
			'0'	=> 'Only filter by catgories',
			'1'	=> 'New',
			'2' => 'Best Selling products',
			'3' => 'Most View',
			'4'	=> 'Attribute',
		);
	}
}