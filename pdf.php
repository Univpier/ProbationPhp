<?php
require 'vendor/autoload.php';
require_once 'vendor\tecnickcom\tcpdf\tcpdf.php';
require_once 'test.php';

class PDF
{
    function newPDFTable($data)
    {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->pdf->SetAuthor('Кирилл Лапин');
        $this->pdf->SetTitle('Вывод в pdf');
        $this->pdf->SetFont('courier', '', 14, '', true);
        $this->pdf->AddPage();
        $this->pdf->cell(50,5,'responsible name',1);
        $this->pdf->cell(50,5,'name division',1,1);

        foreach ($data as $key=>$value) {
            $html = $value['responsible_name'];
            $html2 = $value['name'];
            $this->pdf->SetFont('times','',14);
            $this->pdf->cell(50,5,$html,1);
            $this->pdf->cell(50,5,$html2,1,1);
        }
        try {
            $this->pdf->Output('document.pdf', 'D');
        } catch (PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            echo $e->getMessage();
        }
    }
}

$database = new Database('localhost', 'probation', 'root', '');
$data = $database->setupPlainTree();
$pdf = new PDF;
$pdf->newPDFTable($data);




