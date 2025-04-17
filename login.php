<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$message = '';

// Проверяем сообщение из сессии (например, после регистрации)
if (isset($_SESSION['registration_success'])) {
    $message = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Валидация
    if (empty($login) || empty($password)) {
        $error = 'Введите логин и пароль';
    } else {
        // Ищем пользователя в базе данных
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE login = ? OR email = ?");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Проверяем пароль
            if (password_verify($password, $user['password'])) {
                // Успешный вход
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Неверный логин или пароль';
            }
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white py-4 px-6">
                <h2 class="text-2xl font-bold">Вход в систему</h2>
            </div>
            
            <?php if ($message): ?>
                <div class="bg-green-100 text-green-700 px-6 py-4">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="py-6 px-8">
                <?php if ($error): ?>
                    <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label for="login" class="block text-gray-700 font-medium mb-2">
                        Логин или Email
                    </label>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                        required
                    />
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-medium mb-2">
                        Пароль
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                        required
                    />
                </div>
                
                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                >
                    Войти
                </button>
                
                <div class="mt-4 text-center">
                    <a href="register.php" class="text-blue-600 hover:underline">
                        Нет аккаунта? Зарегистрируйтесь
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>