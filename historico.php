<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();
require_login();
include __DIR__ . '/includes/header.php';

// Filters
$action = $_GET['action'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$params = ['uid' => $_SESSION['user']['id']];
$where = ' WHERE user_id = :uid ';

if ($action !== '') {
    $where .= ' AND action = :action ';
    $params['action'] = $action;
}
if ($start !== '') {
    $where .= ' AND created_at >= :start ';
    $params['start'] = $start . ' 00:00:00';
}
if ($end !== '') {
    $where .= ' AND created_at <= :end ';
    $params['end'] = $end . ' 23:59:59';
}

$sql = "SELECT id, action, details, ip, user_agent, created_at FROM logs " . $where . " ORDER BY created_at DESC LIMIT 500";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

?>
<div class="bg-white rounded-lg border border-slate-200 p-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold">Histórico de Atividades</h2>
    <a href="/Porrada/definicoes.php" class="text-sm text-slate-600 hover:text-blue-600">← Voltar</a>
  </div>

  <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
    <div>
      <label class="text-xs text-slate-600">Ação</label>
      <select name="action" class="mt-1 block w-full rounded-md border-slate-200">
        <option value="">Todas</option>
        <option value="access_denied" <?= $action==='access_denied' ? 'selected' : ''?>>Acessos Negados</option>
        <option value="alert_sent" <?= $action==='alert_sent' ? 'selected' : ''?>>Alertas Enviados</option>
        <option value="create_user" <?= $action==='create_user' ? 'selected' : ''?>>Criar Utilizador</option>
        <option value="criar_paciente" <?= $action==='criar_paciente' ? 'selected' : ''?>>Criar Paciente</option>
      </select>
    </div>

    <div>
      <label class="text-xs text-slate-600">Data Início</label>
      <input name="start" type="date" value="<?=htmlspecialchars($start)?>" class="mt-1 block w-full rounded-md border-slate-200">
    </div>

    <div>
      <label class="text-xs text-slate-600">Data Fim</label>
      <input name="end" type="date" value="<?=htmlspecialchars($end)?>" class="mt-1 block w-full rounded-md border-slate-200">
    </div>

    <div class="flex items-end">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filtrar</button>
    </div>
  </form>

  <div class="overflow-auto">
    <table class="min-w-full text-sm">
      <thead class="text-slate-600">
        <tr>
          <th class="px-3 py-2 text-left">Ação</th>
          <th class="px-3 py-2 text-left">Detalhes</th>
          <th class="px-3 py-2 text-left">IP</th>
          <th class="px-3 py-2 text-left">User Agent</th>
          <th class="px-3 py-2 text-left">Data/Hora</th>
        </tr>
      </thead>
      <tbody class="text-slate-800">
        <?php if (empty($logs)): ?>
          <tr><td colspan="5" class="p-4 text-center text-slate-500">Nenhum registo encontrado.</td></tr>
        <?php else: ?>
          <?php foreach ($logs as $l): ?>
            <tr class="border-t border-slate-100">
              <td class="px-3 py-2 align-top font-medium"><?=htmlspecialchars($l['action'])?></td>
              <td class="px-3 py-2 align-top max-w-xl"><?=nl2br(htmlspecialchars($l['details']))?></td>
              <td class="px-3 py-2 align-top"><?=htmlspecialchars($l['ip'] ?? '-')?></td>
              <td class="px-3 py-2 align-top"><?=htmlspecialchars(substr($l['user_agent'] ?? '-',0,80))?></td>
              <td class="px-3 py-2 align-top"><?=htmlspecialchars($l['created_at'])?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php';
