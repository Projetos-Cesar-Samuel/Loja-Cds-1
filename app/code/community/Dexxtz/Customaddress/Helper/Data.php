<?php

/**
 * Copyright [2014] [Dexxtz]
 *
 * @package   Dexxtz_Customaddress
 * @author    Dexxtz
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

class Dexxtz_Customaddress_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getAutoComplete($class, $zip = false, $end = false)
	{
		$value = Mage::getStoreConfig('dexxtz_customaddress/general/address_autocomplete');
		
		if ($zip && $value == 1){
			if ($end) {
				return ' onblur="getEnderecoShipping()" ';
			} else {
				return' onblur="getEndereco()" ';	
			}
		} else {
			if ($value == 1) {
				return $class;
			}
		}
	}
	
	public function addMask($maxLength, $storeCode)
	{
		$mask = Mage::getStoreConfig('dexxtz_customaddress/general/telephone_mask');
		
		if ($mask == 1 && $storeCode == 'pt_BR') {
			return ' onkeypress="maskDexxtz(this,maskTelephone)" maxlength="' . $maxLength . '"';
		}		
	}
	
	public function addZipMask($storeCode)
	{
		$mask = Mage::getStoreConfig('dexxtz_customaddress/general/zip_mask');
		
		if ($mask == 1 && $storeCode == 'pt_BR') {
			return ' onkeypress="maskDexxtz(this,maskZipBrazil)" maxlength="9"';
		}
	}
	
	public function getZipLink()
	{
		$value = Mage::getStoreConfig('dexxtz_customaddress/show_fields/zip_link');
		return $value;		
	}
	
	public function getZipText()
	{
		$value = Mage::getStoreConfig('dexxtz_customaddress/show_fields/zip_text');
		
		if (empty($value)) {
			$value = $this->__('Do not know the zip');
		}
		
		return $value;
	}
	
	public function getCompany()
	{
		$value = Mage::getStoreConfig('dexxtz_customaddress/show_fields/company');
		return $value;		
	}
	
	public function getCountry()
	{
		$value = Mage::getStoreConfig('dexxtz_customaddress/show_fields/country');
		return $value;		
	}
	
	public function checkStates()
	{
		if(!$_SESSION['states']){
			$collection = Mage::getModel('directory/region')->getResourceCollection();
			$states = $collection->addCountryFilter('BR');
			foreach ($states as $info){
				$state[] = array('id' => $info['region_id'], 'code' => $info['code'], 'name' => $info['name']);
			}
			$_SESSION['states'] = $state;
		}
	}
}	