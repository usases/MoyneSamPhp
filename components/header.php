<?php
// components/Header.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Проверяем авторизацию пользователя
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = getUserById($_SESSION['user_id']);
    $currentUser['is_admin'] = ($currentUser['role'] ?? '') === 'admin';
}
?>

<header class="bg-blue-600 text-white shadow-md">
  <div class="container mx-auto px-4 py-3 flex justify-between items-center">
    <a href="\MoyneSamPhp\index.php" class="flex items-center space-x-2 text-xl font-bold">
      <i data-lucide="chrome" class="w-6 h-6"></i>
      <span>Moyne Sam</span>
    </a>
    
    <nav>
      <ul class="flex space-x-6">
        <?php if ($currentUser): ?>
          <li>
            <a href="\MoyneSamPhp\requestsPage.php" class="hover:text-blue-200 transition-colors">
              Мои заявки
            </a>
          </li>
          <?php if (!empty($currentUser['is_admin'])): ?>
            <li>
              <a href="\MoyneSamPhp\components\admin.php" class="hover:text-blue-200 transition-colors">
                Админ панель
              </a>
            </li>
          <?php endif; ?>
          <li class="flex items-center space-x-2">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span><?= htmlspecialchars($currentUser['full_name'] ?? '') ?></span>
          </li>
          <li>
            <a 
              href="\MoyneSamPhp\components\logout.php" 
              class="flex items-center space-x-1 hover:text-blue-200 transition-colors"
            >
              <i data-lucide="log-out" class="w-5 h-5"></i>
              <span>Выйти</span>
            </a>
          </li>
        <?php else: ?>
          <li>
            <a href="login.php" class="hover:text-blue-200 transition-colors">
              Вход
            </a>
          </li>
          <li>
            <a href="register.php" class="hover:text-blue-200 transition-colors">
              Регистрация
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<script>
  lucide.createIcons();
</script>