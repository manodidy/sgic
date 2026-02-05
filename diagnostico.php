<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';

require_login();
require_role('Medico');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id = (int)($_POST['paciente_id'] ?? 0);
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $prescricao = trim($_POST['prescricao'] ?? '');

    if (!$paciente_id || !$diagnostico) {
        $errors[] = 'Paciente e diagnóstico são obrigatórios.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO consultas (paciente_id, medico_id, diagnostico, prescricao, created_at) VALUES (:p, :m, :d, :pr, NOW())');
        $stmt->execute(['p'=>$paciente_id,'m'=>$_SESSION['user']['id'],'d'=>$diagnostico,'pr'=>$prescricao]);
        $cid = $pdo->lastInsertId();
        log_action($pdo, $_SESSION['user']['id'], 'criar_diagnostico', 'Diagnóstico criado para paciente '.$paciente_id);
        $_SESSION['flash'] = ['type'=>'success','title'=>'Diagnóstico','text'=>'Diagnóstico registado com sucesso.'];
        header('Location: diagnostico.php');
        exit;
    }
}

$pacientes = $pdo->query('SELECT id, nome, nuit FROM pacientes ORDER BY created_at DESC LIMIT 200')->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="bg-white p-6 rounded shadow">
  <h2 class="text-lg font-semibold mb-4">Lançar Diagnóstico</h2>

  <?php if ($errors): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4">
      <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="grid grid-cols-1 gap-3">
    <label class="text-sm">Paciente</label>
    <select name="paciente_id" class="w-full p-2 border rounded">
      <?php foreach ($pacientes as $p): ?>
        <option value="<?=$p['id']?>"><?=htmlspecialchars($p['nome'].' - '.$p['nuit'])?></option>
      <?php endforeach; ?>
    </select>

    <label class="text-sm">Diagnóstico</label>
    <input name="diagnostico" class="w-full p-2 border rounded" required>

    <label class="text-sm">Prescrição (opcional)</label>
    <textarea name="prescricao" class="w-full p-2 border rounded"></textarea>

    <button class="bg-blue-600 text-white px-4 py-2 rounded" type="submit">Registar Diagnóstico</button>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php';