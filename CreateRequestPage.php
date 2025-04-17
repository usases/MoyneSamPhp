<?php
// create-request.php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUser = getUserById($_SESSION['user_id']);
$errors = [];
$formData = [
    'address' => '',
    'contact_info' => '',
    'date_time' => '',
    'payment_type' => 'cash',
    'service_type_id' => ''
];

// Получаем типы услуг
$serviceTypes = [];
$stmt = $conn->prepare("SELECT * FROM service_types");
$stmt->execute();
$serviceTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'address' => trim($_POST['address'] ?? ''),
        'contact_info' => trim($_POST['contact_info'] ?? ''),
        'date_time' => $_POST['date_time'] ?? '',
        'payment_type' => $_POST['payment_type'] ?? 'cash',
        'service_type_id' => $_POST['service_type_id'] ?? ''
    ];

    // Валидация
    if (empty($formData['address'])) {
        $errors['address'] = 'Введите адрес';
    }
    
    if (empty($formData['contact_info'])) {
        $errors['contact_info'] = 'Введите контактную информацию';
    }
    
    if (empty($formData['date_time'])) {
        $errors['date_time'] = 'Выберите дату и время';
    } else {
        $selectedDate = new DateTime($formData['date_time']);
        $now = new DateTime();
        
        if ($selectedDate <= $now) {
            $errors['date_time'] = 'Дата и время должны быть в будущем';
        }
    }
    
    if (empty($formData['service_type_id'])) {
        $errors['service_type_id'] = 'Выберите тип услуги';
    }

    // Если ошибок нет, сохраняем заявку
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO service_requests 
            (user_id, service_type_id, address, contact_info, date_time, payment_type, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'new')");
        $stmt->bind_param("isssss", 
            $currentUser['id'],
            $formData['service_type_id'],
            $formData['address'],
            $formData['contact_info'],
            $formData['date_time'],
            $formData['payment_type']
        );
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Заявка успешно создана';
            header("Location: requestsPage.php");
            exit;
        } else {
            $errors['general'] = 'Ошибка при создании заявки: ' . $conn->error;
        }
    }
}

// Получаем текущую дату и время для минимального значения
$now = new DateTime();
$minDateTime = $now->format('Y-m-d\TH:i');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание заявки на уборку</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .header-container {
            margin-bottom: 2rem;
        }
        .footer-container {
            margin-top: 2rem;
            padding-top: 2rem;
            padding-bottom: 0; 
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="header-container">
        <?php require_once __DIR__ . '/components/Header.php'; ?>
    </div>
    
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white py-4 px-6">
                <h2 class="text-2xl font-bold">Создание заявки на уборку</h2>
            </div>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="bg-red-100 text-red-700 px-6 py-4">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="py-6 px-8">
                <div class="mb-4">
                    <label for="service_type_id" class="block text-gray-700 font-medium mb-2">
                        Тип услуги
                    </label>
                    <select
                        id="service_type_id"
                        name="service_type_id"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['service_type_id']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    >
                        <option value="">Выберите тип услуги</option>
                        <?php foreach ($serviceTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= $formData['service_type_id'] == $type['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['name']) ?> - <?= $type['price'] ?> ₽
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['service_type_id'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['service_type_id'] ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label for="address" class="block text-gray-700 font-medium mb-2">
                        Адрес
                    </label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['address']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    ><?= htmlspecialchars($formData['address']) ?></textarea>
                    <?php if (isset($errors['address'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['address'] ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label for="contact_info" class="block text-gray-700 font-medium mb-2">
                        Контактная информация
                    </label>
                    <textarea
                        id="contact_info"
                        name="contact_info"
                        rows="2"
                        placeholder="Дополнительный телефон, имя контактного лица и т.д."
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['contact_info']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    ><?= htmlspecialchars($formData['contact_info']) ?></textarea>
                    <?php if (isset($errors['contact_info'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['contact_info'] ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label for="date_time" class="block text-gray-700 font-medium mb-2">
                        Дата и время
                    </label>
                    <input
                        type="datetime-local"
                        id="date_time"
                        name="date_time"
                        value="<?= htmlspecialchars($formData['date_time']) ?>"
                        min="<?= $minDateTime ?>"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 <?= isset($errors['date_time']) ? 'border-red-500 focus:ring-red-200' : 'border-gray-300 focus:ring-blue-200' ?>"
                    />
                    <?php if (isset($errors['date_time'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?= $errors['date_time'] ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">
                        Способ оплаты
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                name="payment_type"
                                value="cash"
                                <?= $formData['payment_type'] === 'cash' ? 'checked' : '' ?>
                                class="mr-2"
                            />
                            Наличными
                        </label>
                        <label class="flex items-center">
                            <input
                                type="radio"
                                name="payment_type"
                                value="card"
                                <?= $formData['payment_type'] === 'card' ? 'checked' : '' ?>
                                class="mr-2"
                            />
                            Картой при выполнении работ
                        </label>
                        <label class="flex items-center">
                            <input
                                type="radio"
                                name="payment_type"
                                value="online"
                                <?= $formData['payment_type'] === 'online' ? 'checked' : '' ?>
                                class="mr-2"
                            />
                            Онлайн оплата
                        </label>
                    </div>
                </div>
                
                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                >
                    Создать заявку
                </button>
            </form>
        </div>
    </div>
    
    <!-- Footer с уменьшенным отступом сверху -->
    <div class="footer-container bg-gray-100">
        <?php require_once __DIR__ . '/components/Footer.php'; ?>
    </div>
</body>
</html>