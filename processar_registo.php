<?php
// processar_registo.php - desativado para público. Registo apenas por Admin.
require_once __DIR__ . '/includes/auth.php';
// If not logged in or not admin, show message and redirect
if (empty($_SESSION['user']) || ($_SESSION['user']['perfil'] ?? '') !== 'Admin') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type'=>'error','title'=>'Registo desativado','text'=>'Registo público desativado. Apenas o Administrador pode criar contas.'];
    header('Location: /Porrada/index.php');
    exit;
}

// If admin reached here, redirect to admin users UI (use salvar_utilizador.php form there)
header('Location: /Porrada/admin_users.php');
exit;