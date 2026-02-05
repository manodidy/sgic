<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';

require_login();
$user = $_SESSION['user'];

// Quick stats
$counts = [];
$counts['users'] = (int)$pdo->query('SELECT COUNT(*) FROM utilizadores')->fetchColumn();
$counts['pacientes'] = (int)$pdo->query('SELECT COUNT(*) FROM pacientes')->fetchColumn();

include __DIR__ . '/includes/header.php';
$active = 'dashboard';
?>
<div class="grid grid-cols-6 gap-6">
  <!-- Sidebar (include reutilizável) -->
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <!-- Content -->
  <div class="col-span-5">
    <div class="flex justify-between items-center mb-6">
      <div class="text-lg font-semibold">Painel</div>
      <?php
        $perfil = $_SESSION['user']['perfil'] ?? ($_SESSION['user']['role'] ?? 'Recepcionista');
        $perfilClass = $perfil === 'Admin' ? 'bg-red-500' : ($perfil === 'Medico' ? 'bg-blue-500' : 'bg-green-600');
      ?>
      <div class="text-sm flex items-center gap-3">
        <div>Bem-vindo, <strong><?=htmlspecialchars($user['nome'] ?? $user['username'] ?? '')?></strong></div>
        <span class="text-xs px-2 py-1 rounded text-white <?= $perfilClass ?>"><?=htmlspecialchars($perfil)?></span>
        <a href="/Porrada/includes/logout.php" class="text-red-600">Sair</a>
      </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
      <div class="p-4 bg-white rounded shadow">
        <div class="text-sm text-gray-600">Total Pacientes</div>
        <div class="text-2xl font-bold" id="countTotal">0</div>
      </div>
      <div class="p-4 bg-white rounded shadow">
        <div class="text-sm text-gray-600">Urgentes</div>
        <div class="text-2xl font-bold text-red-600" id="countUrgente">0</div>
      </div>
      <div class="p-4 bg-white rounded shadow">
        <div class="text-sm text-gray-600">Normais</div>
        <div class="text-2xl font-bold text-green-600" id="countNormal">0</div>
      </div>
    </div>

    <div class="mt-6 bg-white p-4 rounded shadow">
      <h3 class="font-semibold mb-3">Últimos pacientes</h3>
      <div id="recentPatients">
        <table class="w-full border-collapse">
          <thead><tr class="text-left text-sm text-gray-600"><th>Nome</th><th>Triagem</th><th>Chegada</th></tr></thead>
          <tbody>
            <?php
            $stmt = $pdo->query('SELECT nome, triagem, created_at FROM pacientes ORDER BY created_at DESC LIMIT 8');
            foreach ($stmt->fetchAll() as $row): ?>
              <tr class="border-t"><td><?=htmlspecialchars($row['nome'])?></td><td><?=htmlspecialchars($row['triagem'])?></td><td><?=htmlspecialchars($row['created_at'])?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="/Porrada/assets/js/polling.js"></script>

<?php include __DIR__ . '/includes/footer.php';
