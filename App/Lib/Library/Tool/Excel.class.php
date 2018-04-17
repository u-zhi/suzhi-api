<?php

class Tool_Excel{
    public function __construct(){
        $dir=__DIR__ ."/phpExcel/";
        include $dir.'PHPExcel.php';
        include $dir.'PHPExcel/Writer/Excel2007.php';
        include $dir.'PHPExcel/IOFactory.php';
    }
    public function excel(){
        $objPHPExcel = new \PHPExcel();
        return $objPHPExcel;
    }
}