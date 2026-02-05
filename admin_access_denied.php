<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';

require_login();
require_role('Admin');

// Build filters from GET
$filters = [];
$params = [];
$where = "l.action = 'access_denied'";

if (!empty($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
    $where .= " AND l.created_at >= :start_date";
    $params['start_date'] = $_GET['start_date'] . ' 00:00:00';
}
if (!empty($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
    $where .= " AND l.created_at <= :end_date";
    $params['end_date'] = $_GET['end_date'] . ' 23:59:59';
}
if (!empty($_GET['ip'])) {
    $filters['ip'] = $_GET['ip'];
    $where .= " AND l.ip = :ip";
    $params['ip'] = $_GET['ip'];
}
if (!empty($_GET['user'])) {
    $filters['user'] = $_GET['user'];
    $where .= " AND (u.email LIKE :user OR u.nome LIKE :user)";
    $params['user'] = '%' . $_GET['user'] . '%';
}

$limit = min(2000, max(50, (int)($_GET['limit'] ?? 200)));

$sql = "SELECT l.*, u.nome AS user_nome, u.email AS user_email FROM logs l LEFT JOIN utilizadores u ON u.id = l.user_id WHERE {$where} ORDER BY l.created_at DESC LIMIT :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue(':' . $k, $v);
}
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>
<div class="bg-white p-6 rounded shadow">
  <h2 class="text-lg font-semibold mb-4">Relatório: Acessos Negados</h2>
  <p class="text-sm text-gray-600 mb-4">Registos de tentativas bloqueadas (IP, user agent, utilizador quando aplicável). Use os filtros abaixo para refinar a pesquisa.</p>

  <form method="get" class="grid grid-cols-4 gap-3 mb-4">
    <div>
      <label class="block text-sm">Data Início</label>
      <input type="date" name="start_date" value="<?=htmlspecialchars($filters['start_date'] ?? '')?>" class="w-full border p-2 rounded">
    </div>
    <div>
      <label class="block text-sm">Data Fim</label>
      <input type="date" name="end_date" value="<?=htmlspecialchars($filters['end_date'] ?? '')?>" class="w-full border p-2 rounded">
    </div>
    <div>
      <label class="block text-sm">IP</label>
      <input type="text" name="ip" value="<?=htmlspecialchars($filters['ip'] ?? '')?>" class="w-full border p-2 rounded" placeholder="Ex: 102.0.0.1">
    </div>
    <div>
      <label class="block text-sm">Usuário / E-mail</label>
      <input type="text" name="user" value="<?=htmlspecialchars($filters['user'] ?? '')?>" class="w-full border p-2 rounded" placeholder="nome ou e-mail">
    </div>
    <div class="col-span-2">
      <label class="block text-sm">Limite</label>
      <input type="number" name="limit" min="50" max="2000" value="<?=htmlspecialchars($limit)?>" class="w-full border p-2 rounded">
    </div>
    <div class="col-span-2 flex items-end">
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Aplicar Filtros</button>
      <a href="/Porrada/admin_access_denied.php" class="ml-3 text-sm text-gray-600">Limpar</a>
    </div>
  </form>

  <table class="w-full text-sm border-collapse">
    <thead>
      <tr class="text-left border-b"><th>Data</th><th>Usuário</th><th>E-mail</th><th>IP</th><th>User Agent</th><th>Detalhes</th></tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="border-t"><td><?=htmlspecialchars($r['created_at'])?></td><td><?=htmlspecialchars($r['user_nome'] ?? '-')?> </td><td><?=htmlspecialchars($r['user_email'] ?? '-')?></td><td><?=htmlspecialchars($r['ip'] ?? '-')?></td><td><?=htmlspecialchars(substr($r['user_agent'] ?? '-',0,120))?></td><td><?=htmlspecialchars($r['details'] ?? '-')?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/includes/footer.php';