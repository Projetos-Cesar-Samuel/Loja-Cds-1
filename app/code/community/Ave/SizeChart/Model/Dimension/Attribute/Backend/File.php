<?php

/**
 * Admin backend source model for files
 *
 * @category    Ave
 * @package     Ave_SizeChart
 * @author      averun <dev@averun.com>
 */
class Ave_SizeChart_Model_Dimension_Attribute_Backend_File extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

    /**
     * @param Varien_Object $object
     * @return $this
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());
        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()
                ->saveAttribute($object, $this->getAttribute()->getName());
        } else {
            $path = Mage::helper('ave_sizechart/dimension')->getFileBaseDir();
            try {
                $uploader = new Varien_File_Uploader($this->getAttribute()->getName());
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $result = $uploader->save($path);
                $object->setData($this->getAttribute()->getName(), $result['file']);
                $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $this;
    }
}
