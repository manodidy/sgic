<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';
require_login();

function classify_triage($v) {
    // Server-side fallback classification; keep in sync with triagem.js
    $spo2 = $v['spo2'];
    $hr = $v['hr'];
    $sys = $v['systolic'];
    $temp = $v['temp'];

    if ($spo2 < 90 || $sys < 90 || $hr > 140 || $temp >= 40) return 'Emergência';
    if ($spo2 < 94 || $sys < 100 || $hr > 120 || $temp >= 39) return 'Urgente';
    return 'Normal';
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $nuit = trim($_POST['nuit'] ?? '');
    $idade = (int)($_POST['idade'] ?? 0);
    $temp = (float)($_POST['temp'] ?? 0);
    $hr = (int)($_POST['hr'] ?? 0);
    $spo2 = (int)($_POST['spo2'] ?? 0);
    $systolic = (int)($_POST['systolic'] ?? 0);

    if (!$nome) $errors[] = 'Nome é obrigatório.';

    if (empty($errors)) {
        $triagem = classify_triage(['spo2'=>$spo2,'hr'=>$hr,'systolic'=>$systolic,'temp'=>$temp]);
        $stmt = $pdo->prepare('INSERT INTO pacientes (nome, nuit, idade, temp, hr, spo2, systolic, triagem, status, created_at) VALUES (:n, :nu, :i, :t, :hr, :sp, :sy, :tri, :st, NOW())');
        $stmt->execute([
            'n'=>$nome,'nu'=>$nuit,'i'=>$idade,'t'=>$temp,'hr'=>$hr,'sp'=>$spo2,'sy'=>$systolic,'tri'=>$triagem,'st'=>'nafila'
        ]);
        $pid = $pdo->lastInsertId();
        log_action($pdo, $_SESSION['user']['id'], 'criar_paciente', 'Paciente criado: '.$nome);
        // Flash
        $_SESSION['flash'] = ['type'=>'success','title'=>'Paciente','text'=>'Paciente registado com sucesso.'];
        header('Location: pacientes.php');
        exit;
    }
}

// Update status (simple approach)
if (isset($_GET['action']) && $_GET['action'] === 'update' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'] ?? 'nafila';
    $stmt = $pdo->prepare('UPDATE pacientes SET status = :s WHERE id = :id');
    $stmt->execute(['s'=>$status,'id'=>$id]);
    log_action($pdo, $_SESSION['user']['id'], 'atualizar_paciente', "Status {$status} para paciente {$id}");
    header('Location: pacientes.php');
    exit;
}

// Fetch patients ordered by triage priority
$order = "FIELD(triagem, 'Emergência','Urgente','Normal'), created_at ASC";
$patients = $pdo->query("SELECT * FROM pacientes ORDER BY {$order}")->fetchAll();

include __DIR__ . '/includes/header.php';
$active = (isset($_GET['action']) && $_GET['action'] === 'novo') ? 'novo-paciente' : 'lista';
?>
<div class="grid grid-cols-6 gap-6">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="col-span-5">
    <div class="bg-white p-6 rounded shadow">
      <h2 class="text-lg font-semibold mb-4">Gerir Pacientes</h2>

  <?php if ($errors): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4">
      <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
    </div>
  <?php endif; ?>

  <form method="post" class="grid grid-cols-2 gap-3 mb-6">
    <div>
      <label class="block text-sm">Nome</label>
      <input name="nome" required class="w-full border p-2 rounded">
    </div>
    <div>
      <label class="block text-sm">NUIT</label>
      <input name="nuit" class="w-full border p-2 rounded" id="nuit">
    </div>
    <div>
      <label class="block text-sm">Idade</label>
      <input name="idade" type="number" class="w-full border p-2 rounded">
    </div>
    <div>
      <label class="block text-sm">Temperatura (°C)</label>
      <input name="temp" id="temp" class="w-full border p-2 rounded" type="number" step="0.1">
    </div>
    <div>
      <label class="block text-sm">Pulso (bpm)</label>
      <input name="hr" id="hr" class="w-full border p-2 rounded" type="number">
    </div>
    <div>
      <label class="block text-sm">SpO2 (%)</label>
      <input name="spo2" id="spo2" class="w-full border p-2 rounded" type="number">
    </div>
    <div>
      <label class="block text-sm">Pressão Sistólica</label>
      <input name="systolic" id="systolic" class="w-full border p-2 rounded" type="number">
    </div>

    <div class="col-span-2">
      <label class="block text-sm">Classificação (triagem)</label>
      <input name="triagem" id="triagem" class="w-full border p-2 rounded bg-gray-50" readonly>
    </div>

    <div class="col-span-2">
      <button type="button" class="bg-gray-200 text-gray-800 px-3 py-2 rounded" id="calcTri">Calcular Triagem</button>
      <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded">Adicionar Paciente</button>
    </div>
  </form>

  <h3 class="font-semibold">Lista de Espera</h3>
  <table class="w-full mt-2 border-collapse">
    <thead><tr class="text-left text-sm text-gray-600"><th>Nome</th><th>Triagem</th><th>Status</th><th>Chegada</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($patients as $p): ?>
        <tr class="border-t">
          <td><?=htmlspecialchars($p['nome'])?></td>
          <td><?=htmlspecialchars($p['triagem'])?></td>
          <td><?=htmlspecialchars($p['status'])?></td>
          <td><?=htmlspecialchars($p['created_at'])?></td>
          <td>
            <?php if (in_array($_SESSION['user']['perfil'], ['Medico','Recepcionista'])): ?>
              <a class="text-xs text-green-600" href="?action=update&id=<?=$p['id']?>&status=atendido">Marcar Atendido</a> |
            <?php endif; ?>
            <?php if (in_array($_SESSION['user']['perfil'], ['Medico','Recepcionista'])): ?>
              <a class="text-xs text-red-600" href="?action=update&id=<?=$p['id']?>&status=nafila">Voltar à Fila</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['perfil'] === 'Medico'): ?>
              | <a class="text-xs text-blue-600" target="_blank" href="/Porrada/generate_transfer.php?id=<?=$p['id']?>">Gerar Guia</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
  </div>
</div>

<script src="/Porrada/assets/js/triagem.js"></script>
<script>
  document.getElementById('calcTri').addEventListener('click', function(){
    const temp = parseFloat(document.getElementById('temp').value) || 0;
    const hr = parseInt(document.getElementById('hr').value) || 0;
    const spo2 = parseInt(document.getElementById('spo2').value) || 0;
    const systolic = parseInt(document.getElementById('systolic').value) || 0;
    const tri = classifyTriage({temp, hr, spo2, systolic});
    document.getElementById('triagem').value = tri;
  });
</script>

<?php include __DIR__ . '/includes/footer.php';
