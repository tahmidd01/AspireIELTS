<?php
require_once('tcpdf/tcpdf.php');

// Create new PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 16);
$pdf->Write(0, 'TCPDF is working correctly!', '', 0, 'L', true, 0, false, false, 0);
$pdf->Output('test_output.pdf', 'I'); // Show in browser
