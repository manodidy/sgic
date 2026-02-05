<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';
require_login();
require_role('Admin');

// Fetch users
$users = $pdo->query('SELECT id, nome, email, perfil, created_at FROM utilizadores ORDER BY created_at DESC')->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="bg-white p-6 rounded shadow">
  <h2 class="text-lg font-semibold mb-4">Gestão de Utilizadores</h2>

  <div class="grid grid-cols-2 gap-6">
    <div>
      <form method="post" action="/Porrada/salvar_utilizador.php" class="space-y-3">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'] ?? '')?>">
        <input name="nome" placeholder="Nome completo" class="w-full p-2 border rounded" required>
        <input name="email" placeholder="E-mail" type="email" class="w-full p-2 border rounded" required>
        <select name="perfil" class="w-full p-2 border rounded">
          <option value="Recepcionista">Recepcionista</option>
          <option value="Medico">Medico</option>
          <option value="Admin">Admin</option>
        </select>
        <input name="senha" placeholder="Senha temporária" type="password" class="w-full p-2 border rounded" required>
        <button class="bg-blue-600 text-white px-3 py-2 rounded">Criar utilizador</button>
      </form>
    </div>

    <div>
      <h3 class="font-semibold mb-2">Lista de utilizadores</h3>
      <table class="w-full text-sm">
        <thead><tr class="text-left"><th>Nome</th><th>E-mail</th><th>Perfil</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr class="border-t">
              <td><?=htmlspecialchars($u['nome'])?></td>
              <td><?=htmlspecialchars($u['email'])?></td>
              <td><?=htmlspecialchars($u['perfil'])?></td>
              <td>
                <?php if ($u['id'] !== ($_SESSION['user']['id'] ?? 0)): ?>
                  <form method="post" action="/Porrada/admin_delete_user.php" onsubmit="return confirm('Tem certeza que deseja eliminar este utilizador?');">
                    <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'] ?? '')?>">
                    <input type="hidden" name="user_id" value="<?=htmlspecialchars($u['id'])?>">
                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                  </form>
                <?php else: ?>
                  <span class="text-slate-400">(Você)</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php';