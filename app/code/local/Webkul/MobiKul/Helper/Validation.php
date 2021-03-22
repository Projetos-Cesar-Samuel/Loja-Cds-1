<?php

class Webkul_MobiKul_Helper_Validation extends Mage_Core_Helper_Data  {

    public function validMine($data, $mimeData, $arrayName) {
        if (is_array($mimeData)) {
            $type = $data[$arrayName]['type'];
            $mimeType = $mimeData['mime'];
            if ($mimeType == $type) {            
                return true;
            }
        }
        return false;
    }
}