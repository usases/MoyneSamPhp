<?php
// requests.php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получаем данные пользователя
$currentUser = getUserById($_SESSION['user_id']);
$currentUser['is_admin'] = ($currentUser['role'] ?? '') === 'admin';

// Получаем заявки из базы данных
$requests = [];
if ($currentUser['is_admin']) {
    // Для администратора показываем только его заявки
    $stmt = $conn->prepare("SELECT * FROM service_requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $currentUser['id']);
} else {
    // Для обычных пользователей - только их заявки
    $stmt = $conn->prepare("SELECT * FROM service_requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $currentUser['id']);
}
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);

// Получаем типы услуг
$serviceTypes = [];
$stmt = $conn->prepare("SELECT * FROM service_types");
$stmt->execute();
$serviceTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Сообщение (например, после создания заявки)
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $currentUser['is_admin'] ? 'Мои заявки (Админ)' : 'Мои заявки' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        .reason-tooltip {
            position: relative;
            display: inline-block;
        }
        .reason-tooltip .tooltip-text {
            visibility: hidden;
            width: 200px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .reason-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <?php require_once BASE_PATH . '/components/Header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white py-4 px-6 flex justify-between items-center">
                <h2 class="text-2xl font-bold">
                    <?= $currentUser['is_admin'] ? 'Мои заявки (Админ)' : 'Мои заявки' ?>
                </h2>
                <a href="CreateRequestPage.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span>Создать заявку</span>
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="bg-green-100 text-green-700 px-6 py-4 border-b border-green-200">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($requests)): ?>
                <div class="py-12 px-6 text-center">
                    <div class="mx-auto max-w-md">
                        <i data-lucide="inbox" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Заявок не найдено</h3>
                        <p class="text-gray-500 mb-6">У вас пока нет созданных заявок на услуги</p>
                        <a href="CreateRequestPage.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                            Создать первую заявку
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    №
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Услуга
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Дата
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Адрес
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Оплата
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Статус
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Причина отмены
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($requests as $request): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= $request['id'] ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars(getServiceTypeName($request['service_type_id'], $serviceTypes)) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatDate($request['date_time']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 truncate" title="<?= htmlspecialchars($request['address']) ?>">
                                    <?= htmlspecialchars($request['address']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatPaymentType($request['payment_type']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="<?= getStatusColorClass($request['status']) ?> px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        <?= formatStatus($request['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($request['status'] === 'cancelled' && !empty($request['cancellation_reason'])): ?>
                                        <div class="reason-tooltip">
                                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                                            <span class="tooltip-text"><?= htmlspecialchars($request['cancellation_reason']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once BASE_PATH . '/components/Footer.php'; ?>
    
    <script>
        // Инициализация иконок Lucide
        lucide.createIcons();
    </script>
</body>
</html>