<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';
require_login();
require_role('Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Porrada/admin_users.php');
    exit;
}
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    header('Location: /Porrada/admin_users.php?e=' . urlencode('Token CSRF inválido.'));
    exit;
}
$nome = trim($_POST['nome'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$perfil = $_POST['perfil'] ?? 'Recepcionista';
$senha = $_POST['senha'] ?? '';

if (!$nome || !$email || !$senha) {
    header('Location: /Porrada/admin_users.php?e=' . urlencode('Preencha todos os campos.'));
    exit;
}
$stmt = $pdo->prepare('SELECT id FROM utilizadores WHERE email = :e');
$stmt->execute(['e'=>$email]);
if ($stmt->fetch()) {
    header('Location: /Porrada/admin_users.php?e=' . urlencode('E-mail já registado.'));
    exit;
}
$hash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO utilizadores (nome, email, senha, perfil, created_at) VALUES (:n, :e, :s, :p, NOW())');
$stmt->execute(['n'=>$nome,'e'=>$email,'s'=>$hash,'p'=>$perfil]);
$uid = $pdo->lastInsertId();
log_action($pdo, $_SESSION['user']['id'], 'create_user', 'Criou utilizador '.$email);

// Flash success and redirect
$_SESSION['flash'] = ['type'=>'success','title'=>'Utilizador','text'=>'Utilizador criado com sucesso.'];
header('Location: /Porrada/admin_users.php');
exit;