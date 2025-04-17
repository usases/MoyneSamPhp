<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$formData = [
    'fullName' => '',
    'phone' => '',
    'email' => '',
    'login' => '',
    'password' => '',
    'confirmPassword' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные формы
    $formData = [
        'fullName' => trim($_POST['fullName'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'login' => trim($_POST['login'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirmPassword' => $_POST['confirmPassword'] ?? ''
    ];

    // Валидация
    if (empty($formData['fullName'])) {
        $errors['fullName'] = 'Введите ФИО';
    } elseif (!preg_match('/^[А-ЯЁ][а-яё]+\s[А-ЯЁ][а-яё]+\s[А-ЯЁ][а-яё]+$/u', $formData['fullName'])) {
        $errors['fullName'] = 'Введите ФИО полностью (Фамилия Имя Отчество) на кириллице';
    }

    if (empty($formData['phone'])) {
        $errors['phone'] = 'Введите номер телефона';
    } elseif (!preg_match('/^\+7 \(\d{3}\) \d{3} \d{2} \d{2}$/', $formData['phone'])) {
        $errors['phone'] = 'Телефон должен быть в формате +7 (XXX) XXX XX XX';
    }

    if (empty($formData['email'])) {
        $errors['email'] = 'Введите email';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email';
    }

    if (empty($formData['login'])) {
        $errors['login'] = 'Введите логин';
    }

    if (empty($formData['password'])) {
        $errors['password'] = 'Введите пароль';
    } elseif (strlen($formData['password']) < 6) {
        $errors['password'] = 'Пароль должен содержать минимум 6 символов';
    }

    if ($formData['password'] !== $formData['confirmPassword']) {
        $errors['confirmPassword'] = 'Пароли не совпадают';
    }

    // Если ошибок нет, регистрируем пользователя
    if (empty($errors)) {
        // Проверяем, не занят ли логин
        $stmt = $conn->prepare("SELECT id FROM users WHERE login = ? OR email = ?");
        $stmt->bind_param("ss", $formData['login'], $formData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors['login'] = 'Пользователь с таким логином или email уже существует';
        } else {
            // Хешируем пароль
            $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);
            
            // Регистрируем пользователя
            $stmt = $conn->prepare("INSERT INTO users (full_name, phone, email, login, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $formData['fullName'], $formData['phone'], $formData['email'], $formData['login'], $hashedPassword);
            
            if ($stmt->execute()) {
                $_SESSION['registration_success'] = 'Регистрация успешна. Войдите в систему.';
                header('Location: login.php');
                exit;
            } else {
                $errors['general'] = 'Ошибка при регистрации. Пожалуйста, попробуйте позже.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- InputMask для телефона -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white py-4 px-6">
                <h2 class="text-2xl font-bold">Регистрация</h2>
            </div>

            <form method="POST" class="py-6 px-8">
                <?php if (isset($errors['general'])): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label for="fullName" class="block text-gray-700 font-medium mb-2">
                        ФИО
                    </label>
                    <input
                        type="text"
                        id="fullName"
                        name="fullName"
                        value="<?= htmlspecialchars($formData['fullName']) ?>"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['fullName']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    />
                    <?php if (isset($errors['fullName'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['fullName']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 font-medium mb-2">
                        Телефон
                    </label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?= htmlspecialchars($formData['phone']) ?>"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['phone']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    />
                    <?php if (isset($errors['phone'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['phone']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-2">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($formData['email']) ?>"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['email']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    />
                    <?php if (isset($errors['email'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="login" class="block text-gray-700 font-medium mb-2">
                        Логин
                    </label>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="<?= htmlspecialchars($formData['login']) ?>"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['login']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    />
                    <?php if (isset($errors['login'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['login']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium mb-2">
                        Пароль
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['password']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    />
                    <?php if (isset($errors['password'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="mb-6">
                    <label for="confirmPassword" class="block text-gray-700 font-medium mb-2">
                        Подтверждение пароля
                    </label>
                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['confirmPassword']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    />
                    <?php if (isset($errors['confirmPassword'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= htmlspecialchars($errors['confirmPassword']) ?></p>
                    <?php endif; ?>
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                >
                    Зарегистрироваться
                </button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#phone').inputmask('+7 (999) 999 99 99');
        });
    </script>
</body>
</html>