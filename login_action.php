<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/logger.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Porrada/index.php');
    exit;
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    header('Location: /Porrada/index.php?e=' . urlencode('Token CSRF inválido.'));
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$senha = $_POST['senha'] ?? null;
if (!$email || !$senha) {
    header('Location: /Porrada/index.php?e=' . urlencode('Preencha e-mail e senha.'));
    exit;
}

$stmt = $pdo->prepare('SELECT id, nome, email, senha, perfil FROM utilizadores WHERE email = :e');
$stmt->execute(['e' => $email]);
$user = $stmt->fetch();
if ($user && password_verify($senha, $user['senha'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => $user['id'], 'nome' => $user['nome'], 'email' => $user['email'], 'perfil' => $user['perfil']];
    log_action($pdo, $user['id'], 'login', 'Login efetuado');
    // Flash welcome including role
    $_SESSION['flash'] = ['type'=>'success','title'=>'Bem-vindo','text'=>'Login efectuado com sucesso. Perfil: '.$user['perfil']];
    header('Location: /Porrada/dashboard.php');
    exit;
}

header('Location: /Porrada/index.php?e=' . urlencode('Credenciais inválidas.'));
exit;