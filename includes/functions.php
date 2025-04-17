<?php
// includes/functions.php

function getUserById($userId) {
    global $conn; // Используем соединение из config.php
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $stmt->close();
        
        return $user;
    } catch (Exception $e) {
        // Логирование ошибки
        error_log("Error getting user: " . $e->getMessage());
        return null;
    }
}
/**
 * Получает название типа услуги по ID
 */
function getServiceTypeName($serviceTypeId, $serviceTypes) {
    foreach ($serviceTypes as $type) {
        if ($type['id'] == $serviceTypeId) {
            return $type['name'];
        }
    }
    return 'Неизвестная услуга';
}

/**
 * Форматирует дату для отображения
 */
function formatDate($dateString) {
    if (empty($dateString)) return '';
    $date = new DateTime($dateString);
    return $date->format('d.m.Y H:i');
}

/**
 * Форматирует тип оплаты для отображения
 */
function formatPaymentType($paymentType) {
    $types = [
        'cash' => 'Наличные',
        'card' => 'Картой',
        'online' => 'Онлайн'
    ];
    return $types[$paymentType] ?? $paymentType;
}

/**
 * Форматирует статус для отображения
 */
function formatStatus($status) {
    $statuses = [
        'new' => 'Новая',
        'in_progress' => 'В работе',
        'completed' => 'Завершена', 
        'cancelled' => 'Отменена'
    ];
    return $statuses[$status] ?? $status;
}

/**
 * Возвращает класс цвета для статуса
 */
function getStatusColorClass($status) {
    $colors = [
        'new' => 'bg-blue-100 text-blue-800',
        'in_progress' => 'bg-yellow-100 text-yellow-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800'
    ];
    return $colors[$status] ?? 'bg-gray-100 text-gray-800';
}
?>