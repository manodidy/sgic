<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

$sth = $pdo->query('SELECT * FROM pacientes ORDER BY created_at DESC');
$rows = $sth->fetchAll();

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Pacientes></Pacientes>');
foreach ($rows as $r) {
    $p = $xml->addChild('Paciente');
    $p->addChild('ID', $r['id']);
    $p->addChild('Nome', $r['nome']);
    $p->addChild('NUIT', $r['nuit']);
    $p->addChild('Idade', $r['idade']);
    $p->addChild('Triagem', $r['triagem']);
    $p->addChild('Status', $r['status']);
    $p->addChild('Chegada', $r['created_at']);
}

header('Content-Type: application/xml; charset=utf-8');
header('Content-Disposition: attachment; filename="pacientes-'.date('Y-m-d').'.xml"');
echo $xml->asXML();
exit;
