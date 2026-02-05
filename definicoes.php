<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logger.php';

if (session_status() === PHP_SESSION_NONE) session_start();
require_role('Admin', 'Medico', 'Recepcionista');

include __DIR__ . '/includes/header.php';

// Handle password change
$passMsg = '';
$passErr = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_pass'])) {
    $current = $_POST['senha_atual'] ?? '';
    $newPass = $_POST['senha_nova'] ?? '';
    $confirmPass = $_POST['senha_confirma'] ?? '';
    
    if (empty($current) || empty($newPass) || empty($confirmPass)) {
        $passErr = 'Todos os campos são obrigatórios.';
    } elseif ($newPass !== $confirmPass) {
        $passErr = 'As palavras-passe não coincidem.';
    } elseif (strlen($newPass) < 6) {
        $passErr = 'A palavra-passe deve ter pelo menos 6 caracteres.';
    } else {
        // Verify current password
        $stmt = $pdo->prepare('SELECT senha FROM utilizadores WHERE id = :id');
        $stmt->execute(['id' => $_SESSION['user']['id']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($current, $user['senha'])) {
            $passErr = 'A palavra-passe atual está incorreta.';
        } else {
            // Update password
            $hashed = password_hash($newPass, PASSWORD_BCRYPT);
            $upd = $pdo->prepare('UPDATE utilizadores SET senha = :s WHERE id = :id');
            $upd->execute(['s' => $hashed, 'id' => $_SESSION['user']['id']]);
            $passMsg = 'Palavra-passe alterada com sucesso.';
            log_action($pdo, $_SESSION['user']['id'], 'change_password', 'Password alterada', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);
        }
    }
}
?>

<div class="space-y-8">
  <!-- Page Header -->
  <div>
    <h1 class="text-4xl font-bold text-slate-900">Definições</h1>
    <p class="text-slate-600 mt-2">Gerencie as suas preferências e segurança da conta</p>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Sidebar Navigation -->
    <div class="lg:col-span-1">
      <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
        <nav class="space-y-1 p-4">
          <a href="#perfil" class="block px-4 py-3 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg">👤 Perfil</a>
          <a href="#password" class="block px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg transition">🔒 Segurança</a>
          <a href="#notificacoes" class="block px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg transition">🔔 Notificações</a>
          <a href="#privacidade" class="block px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg transition">🛡️ Privacidade</a>
        </nav>
      </div>
    </div>

    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
      
      <!-- Profile Section -->
      <section id="perfil" class="bg-white rounded-lg border border-slate-200 p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Informações do Perfil</h2>
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Nome Completo</label>
              <div class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900">
                <?=htmlspecialchars($_SESSION['user']['nome'])?>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">E-mail</label>
              <div class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900">
                <?=htmlspecialchars($_SESSION['user']['email'])?>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Perfil / Cargo</label>
              <div class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                  <?=htmlspecialchars($_SESSION['user']['perfil'])?>
                </span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Membro desde</label>
              <div class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900">
                <?=date('d/m/Y')?>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Password Section -->
      <section id="password" class="bg-white rounded-lg border border-slate-200 p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Alterar Palavra-passe</h2>
        
        <?php if ($passMsg): ?>
          <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-700 text-sm font-medium">✓ <?=htmlspecialchars($passMsg)?></p>
          </div>
        <?php endif; ?>

        <?php if ($passErr): ?>
          <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700 text-sm font-medium">✗ <?=htmlspecialchars($passErr)?></p>
          </div>
        <?php endif; ?>

        <form method="post" class="space-y-5">
          <input type="hidden" name="change_pass" value="1">
          
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Palavra-passe Atual</label>
            <input 
              class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
              name="senha_atual"
              type="password"
              placeholder="••••••••"
              required>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Nova Palavra-passe</label>
              <input 
                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                name="senha_nova"
                type="password"
                placeholder="••••••••"
                required>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Confirmar Nova Palavra-passe</label>
              <input 
                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                name="senha_confirma"
                type="password"
                placeholder="••••••••"
                required>
            </div>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-700 text-xs">💡 A palavra-passe deve ter pelo menos 6 caracteres. Use uma combinação de letras, números e símbolos para maior segurança.</p>
          </div>

          <button 
            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:shadow-lg hover:from-blue-700 hover:to-blue-800 transition duration-200"
            type="submit">
            Atualizar Palavra-passe
          </button>
        </form>
      </section>

      <!-- Notifications Section -->
      <section id="notificacoes" class="bg-white rounded-lg border border-slate-200 p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Preferências de Notificações</h2>
        <div class="space-y-4">
          <label class="flex items-center p-4 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition">
            <input type="checkbox" class="w-4 h-4 text-blue-600 rounded" checked>
            <div class="ml-3">
              <p class="font-medium text-slate-900">Alertas de Acesso Negado</p>
              <p class="text-sm text-slate-600">Receba alertas quando houver tentativas de acesso bloqueadas</p>
            </div>
          </label>
          <label class="flex items-center p-4 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition">
            <input type="checkbox" class="w-4 h-4 text-blue-600 rounded" checked>
            <div class="ml-3">
              <p class="font-medium text-slate-900">Novas Consultas</p>
              <p class="text-sm text-slate-600">Notifique-me quando for atribuída uma nova consulta</p>
            </div>
          </label>
          <label class="flex items-center p-4 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition">
            <input type="checkbox" class="w-4 h-4 text-blue-600 rounded">
            <div class="ml-3">
              <p class="font-medium text-slate-900">Resumo Semanal</p>
              <p class="text-sm text-slate-600">Receba um resumo semanal das atividades do sistema</p>
            </div>
          </label>
        </div>
      </section>

      <!-- Privacy Section -->
      <section id="privacidade" class="bg-white rounded-lg border border-slate-200 p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Privacidade e Dados</h2>
        <div class="space-y-6">
          <div class="bg-slate-50 border border-slate-200 rounded-lg p-6">
            <h3 class="font-semibold text-slate-900 mb-3">📋 Histórico de Atividades</h3>
            <p class="text-sm text-slate-600 mb-4">Visualize ou exporte o histórico de suas atividades no sistema.</p>
            <a href="/Porrada/historico.php" class="inline-block px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 border border-blue-300 rounded-lg hover:bg-blue-50 transition">
              Ver Histórico
            </a>
          </div>

          <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="font-semibold text-red-900 mb-3">⚠️ Zona de Perigo</h3>
            <p class="text-sm text-red-700 mb-4">Eliminação permanente de dados pessoais. Esta ação é irreversível.</p>
            <button class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition" onclick="if(confirm('Tem certeza? Esta ação é irreversível.')) alert('Funcionalidade em desenvolvimento')">
              Eliminar Conta
            </button>
          </div>
        </div>
      </section>

    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php';
