<?php
session_start();
require_once 'db_connected.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Ошибка: пользователь не авторизован.");
}
$userId = $_SESSION['user_id'];

if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['new_status'];

    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND user_id = ?");
    $updateStmt->bind_param("sis", $newStatus, $orderId, $userId);
    $updateStmt->execute();
    header("Location: my_orders.php");
    exit;
}

try {
    // Получаем заказы из таблицы orders по user_id
    $ordersStmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $ordersStmt->bind_param("i", $userId);
    $ordersStmt->execute();
    $ordersResult = $ordersStmt->get_result();
    $orders = [];
    while ($order = $ordersResult->fetch_assoc()) {
        // Для каждого заказа получаем товары из order_items
        $itemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->bind_param("i", $order['id']);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = $item;
        }
        $order['items'] = $items;
        $orders[] = $order;
    }
} catch (Exception $e) {
    die("Ошибка при получении заказов: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Мои заказы - Девичья феерия</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 20px auto;
            padding: 0 20px;
            background-color: #fff0f6;
            color: #660033;
        }
        h1 {
            text-align: center;
            color: #d81b60;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(216, 27, 96, 0.3);
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #fce4ec;
            color: #d81b60;
        }
        img {
            max-width: 80px;
            border-radius: 8px;
        }
        .status {
            font-weight: 700;
            color: #d81b60;
        }
        .order {
            margin-bottom: 40px;
            border: 1px solid #d81b60;
            border-radius: 10px;
            padding: 15px;
        }
        .order-header {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #880e4f;
        }
        .no-orders {
            text-align: center;
            margin-top: 40px;
            font-size: 18px;
            color: #880e4f;
        }
        .back-link {
            display: block;
            margin: 20px auto;
            text-align: center;
            font-weight: 700;
            color: #d81b60;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .status-form select {
            padding: 5px;
            font-weight: 700;
            border-radius: 5px;
            border: 1px solid #d81b60;
            color: #d81b60;
        }
        .status-form button {
            background-color: #d81b60;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            margin-left: 10px;
        }
        .status-form button:hover {
            background-color: #ad1457;
        }
    </style>
</head>
<body>
    <h1>Мои заказы</h1>
    <a href="beautiful_shop.html" class="back-link">Вернуться к покупкам</a>

    <?php if (empty($orders)): ?>
        <p class="no-orders">У вас пока нет заказов.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order">
                <div class="order-header">
                    Заказ #<?php echo $order['id']; ?> от <?php echo $order['order_date']; ?> — Статус: <span class="status"><?php echo htmlspecialchars($order['status']); ?></span>
                </div>
        <table>
            <thead>
                <tr>
                    <th>Изображение</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?php if (!empty($item['image_path'])): ?><img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="max-width: 80px; border-radius: 8px;" /><?php else: ?>Нет изображения<?php endif; ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</td>
                    <td><?php echo $item['quantity']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

                <!-- Статус заказа можно изменить, если нужно -->
                <form method="post" class="status-form">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>" />
                    <select name="new_status">
                        <option value="Новый" <?php if ($order['status'] === 'Новый') echo 'selected'; ?>>Новый</option>
                        <option value="В обработке" <?php if ($order['status'] === 'В обработке') echo 'selected'; ?>>В обработке</option>
                        <option value="Отправлен" <?php if ($order['status'] === 'Отправлен') echo 'selected'; ?>>Отправлен</option>
                        <option value="Доставлен" <?php if ($order['status'] === 'Доставлен') echo 'selected'; ?>>Доставлен</option>
                        <option value="Отменен" <?php if ($order['status'] === 'Отменен') echo 'selected'; ?>>Отменен</option>
                    </select>
                    <button type="submit" name="update_status">Обновить статус</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
