<?php
ini_set("memory_limit","256M");
set_time_limit(300);
require_once "processors/classes/uFunc.php";
require_once "lib/phpexcel/PHPExcel.php";

class import_class {
    private $uCore, $uFunc;


    public function getXLS($xls, $separat=null) {
        if($separat !== null) {
            $objReader = new PHPExcel_Reader_CSV();
            $objReader->setInputEncoding('UTF-8');
            $objReader->setDelimiter($separat);
            $objReader->setSheetIndex(0);
            $objPHPExcel = $objReader->load($xls);
        }
        else {
            $objPHPExcel = PHPExcel_IOFactory::load($xls);
        }

        $lists = array();
        foreach($objPHPExcel ->getWorksheetIterator() as $worksheet) {
            $lists[] = $worksheet->toArray();
        }

        return $lists;
    }

    public function text($str) {
        return $this->uCore->text(array('processors','uBc'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uFunc = new \processors\uFunc($this->uCore);
    }
}
