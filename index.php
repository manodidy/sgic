<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/logger.php';
session_start();

// Simple CSRF helper
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

// Optionally show error passed via GET
$errorMsg = $_GET['e'] ?? null;
// Ensure $errors is always defined to avoid PHP notices
$errors = [];

include __DIR__ . '/includes/header.php';
?>

<!-- Login Container -->
<div class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md">
    <!-- Welcome Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
      <!-- Header Gradient -->
      <div class="h-2 bg-gradient-to-r from-blue-600 to-blue-700"></div>
      
      <div class="px-6 py-8">
        <!-- Title -->
        <div class="text-center mb-8">
          <h2 class="text-3xl font-bold text-slate-900 mb-2">Bem-vindo ao SGCI</h2>
          <p class="text-slate-600 text-sm">Sistema de Gestão Clínica e Interoperabilidade</p>
        </div>

        <!-- Error Messages -->
        <?php if ($errors): ?>
          <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-700 text-sm font-medium mb-2">Erros encontrados:</p>
            <?php foreach ($errors as $e): ?>
              <div class="text-red-600 text-xs mb-1">• <?=htmlspecialchars($e)?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
          <div class="mb-6 p-4 bg-orange-50 border border-orange-200 rounded-lg">
            <p class="text-orange-700 text-sm font-medium"><?=htmlspecialchars($errorMsg)?></p>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="post" action="/Porrada/login_action.php" class="space-y-5">
          <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
          
          <!-- Email Field -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Endereço de E-mail</label>
            <input 
              class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
              name="email" 
              type="email" 
              placeholder="seu@email.com"
              required
              autocomplete="email">
          </div>

          <!-- Password Field -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Palavra-passe</label>
            <input 
              class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
              name="senha" 
              type="password" 
              placeholder="••••••••"
              required
              autocomplete="current-password">
          </div>

          <!-- Submit Button -->
          <button 
            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium py-2.5 rounded-lg hover:shadow-lg hover:from-blue-700 hover:to-blue-800 transition duration-200 mt-6"
            type="submit">
            Entrar no Sistema
          </button>
        </form>

        <!-- Footer Info -->
        <div class="mt-6 pt-6 border-t border-slate-200">
          <p class="text-center text-xs text-slate-600">
            Acesso restrito a utilizadores autorizados<br>
            <span class="text-slate-500">v1.0.0 © SGCI 2026</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Security Note -->
    <div class="mt-4 text-center text-xs text-slate-600">
      <p>ℹ️ Utilize as suas credenciais fornecidas pelo administrador</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php';

