<?php
// registo.php - público desativado. Registo apenas por Administrador via `admin_users.php`.
require_once __DIR__ . '/includes/auth.php';

// If user is admin, redirect to admin users page
if (!empty($_SESSION['user']) && ($_SESSION['user']['perfil'] ?? '') === 'Admin') {
    header('Location: /Porrada/admin_users.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow mt-8">
  <h2 class="text-lg font-semibold mb-4">Registo desativado</h2>
  <p class="mb-3">O registo público foi desativado por razões de segurança. Por favor, contacte o <strong>Administrador</strong> da unidade para criar uma conta.</p>
  <p class="text-sm text-gray-600">Se já tem uma conta, faça o <a href="/Porrada/index.php" class="text-blue-600">login</a>.</p>
</div>

<?php include __DIR__ . '/includes/footer.php';
