<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';

if (session_status() === PHP_SESSION_NONE) session_start();
require_role('Admin');

// CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
    http_response_code(400);
    exit('Requisição inválida (CSRF).');
}

$id = (int)($_POST['user_id'] ?? 0);
if (!$id) {
    http_response_code(400);
    exit('ID inválido.');
}

// Prevent self-deletion
if ($id === ($_SESSION['user']['id'] ?? 0)) {
    $_SESSION['flash'] = ['type' => 'error', 'title' => 'Erro', 'text' => 'Não pode eliminar a sua própria conta enquanto autenticado.'];
    header('Location: /Porrada/admin_users.php');
    exit;
}

// Check if user exists
$stmt = $pdo->prepare('SELECT id, email, perfil FROM utilizadores WHERE id = :id');
$stmt->execute(['id' => $id]);
$user = $stmt->fetch();
if (!$user) {
    $_SESSION['flash'] = ['type' => 'error', 'title' => 'Erro', 'text' => 'Utilizador não encontrado.'];
    header('Location: /Porrada/admin_users.php');
    exit;
}

// Prevent deleting last admin
if ($user['perfil'] === 'Admin') {
    $count = (int)$pdo->query("SELECT COUNT(*) FROM utilizadores WHERE perfil = 'Admin'")->fetchColumn();
    if ($count <= 1) {
        $_SESSION['flash'] = ['type' => 'error', 'title' => 'Erro', 'text' => 'Não é possível eliminar o último utilizador Admin.'];
        header('Location: /Porrada/admin_users.php');
        exit;
    }
}

// Perform deletion
$del = $pdo->prepare('DELETE FROM utilizadores WHERE id = :id');
$del->execute(['id' => $id]);
if ($del->rowCount() > 0) {
    log_action($pdo, $_SESSION['user']['id'] ?? null, 'delete_user', 'Eliminou utilizador '.$user['email'], $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);
    $_SESSION['flash'] = ['type' => 'success', 'title' => 'Feito', 'text' => 'Utilizador eliminado com sucesso.'];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'title' => 'Erro', 'text' => 'Falha ao eliminar utilizador.'];
}
header('Location: /Porrada/admin_users.php');
exit;
