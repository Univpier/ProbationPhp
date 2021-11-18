<?php

require 'vendor/autoload.php';
require_once 'test.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use test as ex;

/**
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */


echo ex\Database::$arrTree;

class Exel{
    function spreadsheet($arrTree){

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'name');
        $sheet->setCellValue('C1', 'parentId');
        foreach($arrTree as $key => $value){
            $sheet->setCellValue('A'.$key,  $value['id']);
            $sheet->setCellValue('B'.$key, $value['name']);
            $sheet->setCellValue('C'.$key, $value['parentId']);
        }

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save('hello.xlsx');

        } catch (PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            echo $e->getMessage();
        }

    }
}




$outputExel = new Exel();
//$outputExel->spreadsheet();