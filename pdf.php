<?php
require 'vendor/autoload.php';
require_once 'vendor\tecnickcom\tcpdf\tcpdf.php';
require_once 'test.php';
// Создаем новый PDF документ
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
// Устанавливаем информацию о документе
$pdf->SetAuthor('Кирилл Лапин');
$pdf->SetTitle('Вывод в pdf');

// Устанавливаем автоматические разрывы страниц
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// Устанавливаем шрифт
$pdf->SetFont('dejavusans', '', 14, '', true);
// Добавляем страницу
$pdf->AddPage();
// Устанавливаем текст

// Выводим текст с помощью writeHTMLCell()

// Закрываем и выводим PDF документ
$pdf->Output('document.pdf', 'I');
?>