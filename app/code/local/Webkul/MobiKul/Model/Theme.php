<?php

    class Webkul_Mobikul_Model_Theme    {

        public function toOptionArray()    {
            $data = array();
            $data[] = array("value" => 1, "label" => "red-green");
            $data[] = array("value" => 2, "label" => "light green");
            $data[] = array("value" => 3, "label" => "deep purple-pink");
            $data[] = array("value" => 4, "label" => "blue-orange");
            $data[] = array("value" => 5, "label" => "light blue-red");
            return  $data;
        }

    }