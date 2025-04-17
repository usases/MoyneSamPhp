<?php
// admin.php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentUser = getUserById($_SESSION['user_id']);
$currentUser['is_admin'] = ($currentUser['role'] ?? '') === 'admin';

if (!$currentUser['is_admin']) {
    header("Location: index.php");
    exit;
}

// Получаем данные
$serviceRequests = [];
$stmt = $conn->prepare("SELECT * FROM service_requests ORDER BY created_at DESC");
$stmt->execute();
$serviceRequests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$serviceTypes = [];
$stmt = $conn->prepare("SELECT * FROM service_types");
$stmt->execute();
$serviceTypes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$users = [];
$stmt = $conn->prepare("SELECT id, full_name FROM users");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Обработка форм
$activeTab = $_GET['tab'] ?? 'requests';
$editingServiceId = null;
$newService = ['name' => '', 'description' => '', 'price' => 0];
$statusUpdateData = ['request_id' => '', 'status' => 'new', 'cancellation_reason' => ''];

// Обработка добавления/редактирования услуги
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_action'])) {
    if ($_POST['service_action'] === 'save') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);

        if (!empty($_POST['service_id'])) {
            // Редактирование существующей услуги
            $stmt = $conn->prepare("UPDATE service_types SET name = ?, description = ?, price = ? WHERE id = ?");
            $stmt->bind_param("ssdi", $name, $description, $price, $_POST['service_id']);
        } else {
            // Добавление новой услуги
            $stmt = $conn->prepare("INSERT INTO service_types (name, description, price) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $name, $description, $price);
        }

        $stmt->execute();
        header("Location: admin.php?tab=services");
        exit;
    } elseif ($_POST['service_action'] === 'delete' && !empty($_POST['service_id'])) {
        // Удаление услуги
        $stmt = $conn->prepare("DELETE FROM service_types WHERE id = ?");
        $stmt->bind_param("i", $_POST['service_id']);
        $stmt->execute();
        header("Location: admin.php?tab=services");
        exit;
    }
}

// Обработка изменения статуса заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_action'])) {
    $requestId = $_POST['request_id'];
    $status = $_POST['status'];
    $cancellationReason = ($status === 'cancelled') ? trim($_POST['cancellation_reason']) : null;

    // Используем подготовленное выражение для безопасности
    $stmt = $conn->prepare("UPDATE service_requests SET status = ?, cancellation_reason = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $cancellationReason, $requestId);

    if ($stmt->execute()) {
        header("Location: admin.php?tab=requests");
        exit;
    } else {
        // Обработка ошибки
        die("Ошибка при обновлении статуса: " . $conn->error);
    }
}

// Функции для отображения
function getUserName($id, $users)
{
    foreach ($users as $user) {
        if ($user['id'] == $id) {
            return $user['full_name'];
        }
    }
    return 'Неизвестный пользователь';
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-screen bg-gray-50">
    <?php require_once __DIR__ . '/../components/Header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white py-4 px-6">
                <h2 class="text-2xl font-bold">Панель администратора</h2>
            </div>

            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <a href="admin.php?tab=requests"
                        class="<?= $activeTab === 'requests' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> py-4 px-6 font-medium text-sm">
                        Заявки
                    </a>
                    <a href="admin.php?tab=services"
                        class="<?= $activeTab === 'services' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> py-4 px-6 font-medium text-sm">
                        Услуги
                    </a>
                </nav>
            </div>

            <?php if ($activeTab === 'requests'): ?>
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Управление заявками</h3>

                    <?php if (empty($serviceRequests)): ?>
                        <p class="text-gray-500">Нет заявок</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            № заявки
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Клиент
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Услуга
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Дата и время
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Статус
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Создана
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Действия
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($serviceRequests as $request): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?= substr($request['id'], 0, 8) ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars(getUserName($request['user_id'], $users)) ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars(getServiceTypeName($request['service_type_id'], $serviceTypes)) ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <?= formatDate($request['date_time']) ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span
                                                    class="<?= getStatusColorClass($request['status']) ?> px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                    <?= formatStatus($request['status']) ?>
                                                </span>
                                                <?php if ($request['status'] === 'cancelled' && !empty($request['cancellation_reason'])): ?>
                                                    <p class="text-xs text-red-500 mt-1">
                                                        <?= htmlspecialchars($request['cancellation_reason']) ?></p>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                <?= formatDate($request['created_at']) ?>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                <button
                                                    onclick="document.getElementById('statusModal<?= $request['id'] ?>').classList.remove('hidden')"
                                                    class="text-blue-600 hover:text-blue-800">
                                                    Изменить статус
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Status Update Modal -->
                                        <div id="statusModal<?= $request['id'] ?>"
                                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                                            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                                                <h3 class="text-lg font-medium mb-4">Изменение статуса заявки</h3>

                                                <form method="POST" action="admin.php">
                                                    <input type="hidden" name="status_action" value="update">
                                                    <input type="hidden" name="request_id" value="<?= $request['id'] ?>">

                                                    <div class="mb-4">
                                                        <label for="status" class="block text-gray-700 font-medium mb-2">
                                                            Статус
                                                        </label>
                                                        <select id="status" name="status"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200">
                                                            <option value="new" <?= $request['status'] === 'new' ? 'selected' : '' ?>>
                                                                Новая</option>
                                                            <option value="in_progress" <?= $request['status'] === 'in_progress' ? 'selected' : '' ?>>В процессе</option>
                                                            <option value="completed" <?= $request['status'] === 'completed' ? 'selected' : '' ?>>Завершена</option>
                                                            <option value="cancelled" <?= $request['status'] === 'cancelled' ? 'selected' : '' ?>>Отменена</option>
                                                        </select>
                                                    </div>

                                                    <div id="reasonContainer" class="mb-4 hidden">
                                                        <label for="cancellation_reason"
                                                            class="block text-gray-700 font-medium mb-2">
                                                            Причина отмены
                                                        </label>
                                                        <textarea id="cancellation_reason" name="cancellation_reason" rows="3"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"><?= htmlspecialchars($request['cancellation_reason'] ?? '') ?></textarea>
                                                    </div>

                                                    <div class="flex justify-end space-x-2">
                                                        <button type="button"
                                                            onclick="document.getElementById('statusModal<?= $request['id'] ?>').classList.add('hidden')"
                                                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                            Отмена
                                                        </button>
                                                        <button type="submit"
                                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                                            Сохранить
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <script>
                                            // Показываем/скрываем поле причины отмены
                                            document.querySelector('#statusModal<?= $request['id'] ?> select[name="status"]').addEventListener('change', function () {
                                                const reasonContainer = document.querySelector('#statusModal<?= $request['id'] ?> #reasonContainer');
                                                if (this.value === 'cancelled') {
                                                    reasonContainer.classList.remove('hidden');
                                                } else {
                                                    reasonContainer.classList.add('hidden');
                                                }
                                            });
                                        </script>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($activeTab === 'services'): ?>
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Управление услугами</h3>

                    <form method="POST" action="admin.php" class="mb-8 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium mb-4">
                            <?= isset($_GET['edit']) ? 'Редактирование услуги' : 'Добавление новой услуги' ?>
                        </h4>

                        <?php if (isset($_GET['edit'])): ?>
                            <input type="hidden" name="service_id" value="<?= $_GET['edit'] ?>">
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM service_types WHERE id = ?");
                            $stmt->bind_param("i", $_GET['edit']);
                            $stmt->execute();
                            $service = $stmt->get_result()->fetch_assoc();
                            ?>
                        <?php endif; ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="name" class="block text-gray-700 font-medium mb-2">
                                    Название
                                </label>
                                <input type="text" id="name" name="name"
                                    value="<?= isset($service) ? htmlspecialchars($service['name']) : '' ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    required />
                            </div>

                            <div>
                                <label for="price" class="block text-gray-700 font-medium mb-2">
                                    Цена (₽)
                                </label>
                                <input type="number" id="price" name="price"
                                    value="<?= isset($service) ? $service['price'] : '' ?>" min="0" step="100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    required />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 font-medium mb-2">
                                Описание
                            </label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                                required><?= isset($service) ? htmlspecialchars($service['description']) : '' ?></textarea>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <?php if (isset($_GET['edit'])): ?>
                                <a href="admin.php?tab=services"
                                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                    Отмена
                                </a>
                            <?php endif; ?>
                            <button type="submit" name="service_action" value="save"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <?= isset($_GET['edit']) ? 'Сохранить' : 'Добавить' ?>
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Название
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Описание
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Цена
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Действия
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($serviceTypes as $service): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($service['name']) ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">
                                            <?= htmlspecialchars($service['description']) ?>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <?= $service['price'] ?> ₽
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <a href="admin.php?tab=services&edit=<?= $service['id'] ?>"
                                                class="text-blue-600 hover:text-blue-800 mr-3">
                                                Редактировать
                                            </a>
                                            <form method="POST" action="admin.php" class="inline">
                                                <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                                <button type="submit" name="service_action" value="delete"
                                                    class="text-red-600 hover:text-red-800"
                                                    onclick="return confirm('Вы уверены, что хотите удалить эту услугу?')">
                                                    Удалить
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/../components/Footer.php'; ?>

    <script>
        // Инициализация иконок Lucide
        lucide.createIcons();
    </script>
</body>

</html>