<?php

class Ave_SizeChart_Model_Source_Dimension
{
    const DIMENSION_CM      = 'cm';
    const DIMENSION_INCH    = 'inch';
    const DIMENSION_DEFAULT = self::DIMENSION_CM;

    public function toOptionArray($withEmpty = false)
    {
        $options = array();
        if ($withEmpty) {
            $options[] = array(
                'value' => '',
                'label' => Mage::helper('ave_sizechart')->__('Select a default dimension')
            );
        }

        $options[] = array('value' => self::DIMENSION_CM, 'label' => self::DIMENSION_CM);
        $options[] = array('value' => self::DIMENSION_INCH, 'label' => self::DIMENSION_INCH);
        return $options;
    }

    public function getAllOptions($withEmpty = true)
    {
        $options = array();
        foreach ($this->toOptionArray($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }

    public function toArray($withEmpty = true)
    {
        return $this->getAllOptions($withEmpty);
    }
}
