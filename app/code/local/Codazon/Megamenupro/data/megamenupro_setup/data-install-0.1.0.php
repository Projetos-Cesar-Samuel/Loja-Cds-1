<?php
$csvObject = new Varien_File_Csv();
$csvfile = Mage::getModuleDir('','Codazon_Megamenupro').DS.'fixtures'.DS.'megamenupro.csv';
if (file_exists($csvfile)) {
    $rows = $csvObject->getData($csvfile);
    $header = array_shift($rows);
    $model = Mage::getModel("megamenupro/megamenupro");
    foreach($rows as $row) {
        $data = array();
        foreach ($row as $key => $value) {
            if ($header[$key] == 'content' || $header[$key] == 'style') {
                $value = str_replace('""','"',$value);
                $value = str_replace('\\\\','\\',$value);
                $value = html_entity_decode($value,ENT_COMPAT);
            }
            $data[$header[$key]] = $value;
        }
        $model->addData($data)->save();
        $model->unsetData();
    }
}
?>