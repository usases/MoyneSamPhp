<?php
session_start();
require_once __DIR__ . '/components/header.php';

require_once __DIR__ . '/includes/config.php'; // Подключаем конфиг с соединением

// Функции должны подключаться после конфига
require_once __DIR__ . '/includes/functions.php';
// Получаем типы услуг из базы данных
$serviceTypes = [];
try {
    $stmt = $conn->prepare("SELECT * FROM service_types");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $serviceTypes[] = $row;
    }
    
    $stmt->close();
} catch (Exception $e) {
    // Обработка ошибки (можно залогировать)
    $error_message = "Ошибка при загрузке услуг. Пожалуйста, попробуйте позже.";
}

// Проверяем авторизацию пользователя
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = getUserById($_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профессиональная уборка для вашего дома и офиса</title>
    <!-- Подключаем Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Подключаем Lucide icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen">
    <!-- Hero Section -->
    <section class="bg-blue-600 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                Профессиональная уборка для вашего дома и офиса
            </h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">
                Доверьте уборку профессионалам и наслаждайтесь чистотой без забот
            </p>
            <?php if ($currentUser): ?>
                <a 
                    href="CreateRequestPage.php" 
                    class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold text-lg hover:bg-blue-100 transition-colors"
                >
                    Заказать уборку
                </a>
            <?php else: ?>
                <a 
                    href="register.php" 
                    class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold text-lg hover:bg-blue-100 transition-colors"
                >
                    Зарегистрироваться
                </a>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Services Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Наши услуги</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($serviceTypes as $service): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($service['name']) ?></h3>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($service['description']) ?></p>
                            <p class="text-2xl font-bold text-blue-600">от <?= $service['price'] ?> ₽</p>
                        </div>
                        <div class="bg-gray-100 px-6 py-4">
                            <?php if ($currentUser): ?>
                                <a 
                                    href="CreateRequestPage.php" 
                                    class="block w-full text-center bg-blue-600 text-white py-2 rounded font-medium hover:bg-blue-700 transition-colors"
                                >
                                    Заказать
                                </a>
                            <?php else: ?>
                                <a 
                                    href="login.php" 
                                    class="block w-full text-center bg-blue-600 text-white py-2 rounded font-medium hover:bg-blue-700 transition-colors"
                                >
                                    Войти для заказа
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Как это работает</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Выберите услугу</h3>
                    <p class="text-gray-600">Выберите подходящую услугу из нашего каталога</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="clock" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Выберите время</h3>
                    <p class="text-gray-600">Укажите удобную для вас дату и время</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="map-pin" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Укажите адрес</h3>
                    <p class="text-gray-600">Укажите адрес, где нужно провести уборку</p>
                </div>
                
                <div class="text-center">
                    <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="credit-card" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Оплатите услугу</h3>
                    <p class="text-gray-600">Выберите удобный способ оплаты</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Инициализация иконок Lucide -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php
require_once __DIR__ . '/components/Footer.php';
?>