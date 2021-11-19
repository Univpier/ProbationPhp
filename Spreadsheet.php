<?php

require 'vendor/autoload.php';
require_once 'test.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Exel{
    function spreadsheet($arrTree,$fileName){
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
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
            $writer->save('php://output');
        } catch (PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            echo $e->getMessage();
        }

    }
}

$database = new Database('localhost',  'probation', 'root','');
$data = $database->setupPlainTree();
$excel = new Exel;
$excel->spreadsheet($data,'data.xlsx');