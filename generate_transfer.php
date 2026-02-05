<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
// Only médicos can authorize transfer guides
require_role('Medico');

// Composer autoload (run `composer install` first)
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Endroid\QrCode\Builder\Builder;

$id = (int)($_GET['id'] ?? 0);
if (!$id) exit('ID inválido');

$stmt = $pdo->prepare('SELECT * FROM pacientes WHERE id = :id');
$stmt->execute(['id' => $id]);
$p = $stmt->fetch();
if (!$p) exit('Paciente não encontrado.');

$transferId = 'TR-'.date('Ymd').'-'.$p['id'];

$qrData = json_encode([
    'transfer_id' => $transferId,
    'origem' => 'Unidade Sanitaria X',
    'paciente_nuit' => $p['nuit'],
    'nome' => $p['nome'],
    'data' => date('c')
]);

// Gera QR (endroid/qr-code)
$qr = Builder::create()->data($qrData)->size(240)->margin(10)->build();
$qrDataUri = $qr->getDataUri();

$html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:Arial,Helvetica,sans-serif;color:#111} .header{background:#f7fafc;padding:12px;border-bottom:1px solid #e2e8f0} .content{padding:18px} .meta{margin-bottom:10px}</style></head><body>';
$html .= '<div class="header"><h2>Guia de Transferência - SGCI</h2></div>';
$html .= '<div class="content">';
$html .= '<p class="meta"><strong>ID Transferência:</strong> '.htmlspecialchars($transferId).'</p>';
$html .= '<p><strong>Paciente:</strong> '.htmlspecialchars($p['nome']).'</p>';
$html .= '<p><strong>NUIT:</strong> '.htmlspecialchars($p['nuit']).'</p>';
$html .= '<p><strong>Triagem:</strong> '.htmlspecialchars($p['triagem']).'</p>';
$html .= '<p><strong>Data:</strong> '.date('Y-m-d H:i').'</p>';
$html .= '<div style="margin-top:18px"><img src="'.$qrDataUri.'" alt="QR" /></div>';
$html .= '</div></body></html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("guia_transferencia_{$p['id']}.pdf", ['Attachment' => 1]);
exit;
