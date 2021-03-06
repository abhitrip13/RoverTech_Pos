<?php
//call the pdf library
require('fpdf182/fpdf.php');

//A4 width : 219mm
//default margin : 10mm each side
//writeable horizontal : 219-(10*2)=199mm

//Create pdf object

$pdf = new FPDF('P','mm','A4');

//String orentation (P or L)=Potrait or landscape
//String unit (pt,mm,cm,in)-measurement
// Mixed format (A3,A4,A5,Letter and Legal)-format of pages

//Add new pages
//to see how this cell() method works open the image in fpdf folder
$pdf->AddPage();
// $pdf->SetFillColor(123,255,234); for backgorund color
$pdf->SetFont('Arial','B',16);//2nd aggument can be BIU B for bold I for italics and U for underline
$pdf->Cell(80,10,'Cybarg Inc',0,0,'');

$pdf->SetFont('Arial','B',13);
$pdf->Cell(112,10,"Invoice",0,1,'C');

$pdf->SetFont('Arial','',8);
$pdf->Cell(80,5,"Address : Kamta , faizabad road , Lucknow",0,0,'');

$pdf->SetFont('Arial','',10);
$pdf->Cell(112,5,"Invoice: #12345",0,1,'C');

$pdf->SetFont('Arial','',8);
$pdf->Cell(80,5,"Phone Number : +91-6393122939",0,0,'');

$pdf->SetFont('Arial','',10);
$pdf->Cell(112,5,"Date : 22/2/2021",0,1,'C');

$pdf->SetFont('Arial','',8);
$pdf->Cell(80,5,"Email-Address : abhishekt.1181@gmail.com",0,1,'');
$pdf->Cell(80,5,"Website : www.cybarg.com ",0,1,'');

//Line(x1,y1,x2,y2)
$pdf->Line(5,10,205,10);
$pdf->Line(5,45,205,45);
$pdf->Line(5,46,205,46);

$pdf->Ln(10);//line break

$pdf->SetFont('Arial','B',12);
$pdf->Cell(20,10,'Bill To : ',0,0,'C');

$pdf->SetFont('Courier','BI',14);
$pdf->Cell(50,10,'Abhishek',0,1,'');

$pdf->Cell(50,5,'',0,1,'');//empty cell


$pdf->SetFillColor(208,208,208);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'Product',1,0,'C',true);
$pdf->Cell(20,8,'Qty',1,0,'C',true);
$pdf->Cell(30,8,'Price',1,0,'C',true);
$pdf->Cell(40,8,'Total',1,1,'C',true);


$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'iPhone',1,0,'C');
$pdf->Cell(20,8,'1',1,0,'C');
$pdf->Cell(30,8,'800',1,0,'C');
$pdf->Cell(40,8,'800',1,1,'C');

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'HardDisk',1,0,'C');
$pdf->Cell(20,8,'1',1,0,'C');
$pdf->Cell(30,8,'600',1,0,'C');
$pdf->Cell(40,8,'600',1,1,'C');

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'Samsung Galaxy S21',1,0,'C');
$pdf->Cell(20,8,'1',1,0,'C');
$pdf->Cell(30,8,'1000',1,0,'C');
$pdf->Cell(40,8,'1000',1,1,'C');


$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'',0,0,'C');
$pdf->Cell(20,8,'',0,0,'C');
$pdf->Cell(30,8,'Subtotal',1,0,'C',true);
$pdf->Cell(40,8,'2400',1,1,'C');

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'',0,0,'C');
$pdf->Cell(20,8,'',0,0,'C');
$pdf->Cell(30,8,'Tax',1,0,'C',true);
$pdf->Cell(40,8,'60',1,1,'C');

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'',0,0,'C');
$pdf->Cell(20,8,'',0,0,'C');
$pdf->Cell(30,8,'Discount',1,0,'C',true);
$pdf->Cell(40,8,'30',1,1,'C');

$pdf->SetFont('Arial','B',14);
$pdf->Cell(100,8,'',0,0,'C');
$pdf->Cell(20,8,'',0,0,'C');
$pdf->Cell(30,8,'Total',1,0,'C',true);
$pdf->Cell(40,8,'$'.'2400',1,1,'C');

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'',0,0,'C');
$pdf->Cell(20,8,'',0,0,'C');
$pdf->Cell(30,8,'Paid',1,0,'C',true);
$pdf->Cell(40,8,'2400',1,1,'C');

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,8,'',0,0,'C');
$pdf->Cell(20,8,'',0,0,'C');
$pdf->Cell(30,8,'Due',1,0,'C',true);
$pdf->Cell(40,8,'2400',1,1,'C');

$pdf->SetFont('Arial','B',10);
$pdf->Cell(100,8,'',0,0,'C');
$pdf->Cell(20,8,'',0,0,'C');
$pdf->Cell(30,8,'Payment Type',1,0,'C',true);
$pdf->Cell(40,8,'Cash',1,1,'C');

$pdf->Cell(50,10,'',0,1,'');//empty cell

$pdf->SetFont('Arial','B',10);
$pdf->Cell(32,8,'Important Notice:',0,0,'C',true);

$pdf->SetFont('Arial','',8);
$pdf->Cell(148,8,'No item will be refunded or replaced if you don"t have the invoice with you.You can refund within 2 days of purchase.',0,0,'');


//Output the result

$pdf->Output();


?>