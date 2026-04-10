<?php
require('../tcpdf/tcpdf.php');

// Fetch the ID of the order from the URL parameter
$id = $_GET['id'];

// Retrieve other necessary data from URL parameters
$name = urldecode($_GET['name']);
$address = urldecode($_GET['address']);
$number = urldecode($_GET['number']);
$totalProducts = urldecode($_GET['total_products']);
$totalPrice = urldecode($_GET['total_price']);
$method = urldecode($_GET['method']);

$pdf = new TCPDF('P', 'mm', array(100, 150), true, 'UTF-8', false);

$pdf->SetTitle('Invoice');

$pdf->AddPage();

$pdf->SetFont('helvetica', '', 12);

$html = '
    <h1>Invoice</h1>
    <p><strong>Customer Name:</strong> ' . $name . '</p>
    <p><strong>Address:</strong> ' . $address . '</p>
    <p><strong>Contact Number:</strong> ' . $number . '</p>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Product 1</td>
                <td>' . $totalProducts . '</td>
                <td>' . $totalPrice . '</td>
            </tr>
            <!-- Add more rows if there are multiple products -->
        </tbody>
    </table>
    <p><strong>Total Price:</strong> ' . $totalPrice . '</p>
    <p><strong>Payment Method:</strong> ' . $method . '</p>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdfFileName = 'invoice_' . str_replace(' ', '_', $name) . '.pdf';
$pdf->Output($pdfFileName, 'D');
?>
