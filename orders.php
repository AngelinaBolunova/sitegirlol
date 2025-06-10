<?php
session_start();
require_once 'db.php';

$sessionId = session_id();
$mode = $_GET['mode'] ?? 'cart'; // cart или my_orders

if ($mode === 'cart') {
    // Логика корзины (обработка POST-запросов и отображение корзины)
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $productId = $_POST['product_id'];
        $productName = $_POST['product_name'];
        $productPrice = floatval($_POST['product_price']);
        $productImage = $_POST['product_image'];
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        $category = $_POST['category'] ?? '';

        try {
            $quantityStmt = null;
            if ($category === 'bryuki') {
                $quantityStmt = $conn->prepare("SELECT quantity FROM bryuki WHERE id = ?");
            } elseif ($category === 'futbolki') {
                $quantityStmt = $conn->prepare("SELECT quantity FROM futbolki WHERE id = ?");
            } elseif ($category === 'kofty') {
                $quantityStmt = $conn->prepare("SELECT quantity FROM kofty WHERE id = ?");
            }

            if ($quantityStmt) {
                $quantityStmt->bind_param("i", $productId);
                $quantityStmt->execute();
                $quantityResult = $quantityStmt->get_result();
                $productData = $quantityResult->fetch_assoc();
                $availableQuantity = $productData['quantity'] ?? 0;

                if ($quantity > $availableQuantity) {
                    die("Ошибка: запрошенное количество товара превышает доступное на складе.");
                }
            }

            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND category = ?");
            $stmt->bind_param("sis", $sessionId, $productId, $category);
            $stmt->execute();
            $result = $stmt->get_result();
            $cartItem = $result->fetch_assoc();

            if ($cartItem) {
                $newQuantity = $cartItem['quantity'] + $quantity;
                if ($newQuantity > $availableQuantity) {
                    die("Ошибка: итоговое количество товара в корзине превышает доступное на складе.");
                }
                $updateStmt = $conn->prepare("UPDATE cart SET quantity = ?, status = 'В корзине' WHERE id = ?");
                $updateStmt->bind_param("ii", $newQuantity, $cartItem['id']);
                $updateStmt->execute();
            } else {
                $insertStmt = $conn->prepare("INSERT INTO cart (session_id, product_id, category, name, image_path, quantity, status) VALUES (?, ?, ?, ?, ?, ?, 'В корзине')");
                $insertStmt->bind_param("sisssi", $sessionId, $productId, $category, $productName, $productImage, $quantity);
                $insertStmt->execute();
            }
        } catch (Exception $e) {
            die("Ошибка при добавлении в корзину: " . $e->getMessage());
        }

        header('Location: orders.php?mode=cart');
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'remove_selected') {
        $selectedItems = $_POST['selected_items'] ?? [];
        if (!empty($selectedItems)) {
            try {
                $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
                $deleteQuery = "DELETE FROM cart WHERE session_id = ? AND id IN ($placeholders)";
                $deleteStmt = $conn->prepare($deleteQuery);
                $types = str_repeat('i', count($selectedItems));
                $params = array_merge([$sessionId], $selectedItems);
                $deleteStmt->bind_param("s" . $types, ...$params);
                $deleteStmt->execute();
            } catch (Exception $e) {
                die("Ошибка при удалении из корзины: " . $e->getMessage());
            }
        }
        header('Location: orders.php?mode=cart');
        exit;
    }

    if (isset($_GET['action']) && $_GET['action'] === 'clear') {
        try {
            $clearStmt = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
            $clearStmt->bind_param("s", $sessionId);
            $clearStmt->execute();
        } catch (Exception $e) {
            die("Ошибка при очистке корзины: " . $e->getMessage());
        }
        header('Location: orders.php?mode=cart');
        exit;
    }

    if (isset($_POST['action']) && ($_POST['action'] === 'order' || $_POST['action'] === 'order_selected')) {
        try {
            $conn->begin_transaction();

            $selectedItems = $_POST['selected_items'] ?? [];

            if ($_POST['action'] === 'order_selected' && empty($selectedItems)) {
                throw new Exception("Не выбраны товары для заказа");
            }

            $query = "SELECT product_id, category, c.name as product_name, c.image_path as product_image, c.quantity, 
                COALESCE(b.price, f.price, k.price) as price
                FROM cart c
                LEFT JOIN bryuki b ON c.product_id = b.id AND c.category = 'bryuki'
                LEFT JOIN futbolki f ON c.product_id = f.id AND c.category = 'futbolki'
                LEFT JOIN kofty k ON c.product_id = k.id AND c.category = 'kofty'
                WHERE c.session_id = ? AND c.status = 'В корзине'";

            if ($_POST['action'] === 'order_selected') {
                $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
                $query .= " AND c.id IN ($placeholders)";
            }

            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                throw new Exception("Ошибка подготовки запроса: " . $conn->error);
            }

            if ($_POST['action'] === 'order_selected') {
                $types = str_repeat('i', count($selectedItems));
                $params = array_merge([$sessionId], $selectedItems);
                $stmt->bind_param("s" . $types, ...$params);
            } else {
                $stmt->bind_param("s", $sessionId);
            }

            $stmt->execute();
            $cartItemsResult = $stmt->get_result();
            $cartItems = [];
            $totalAmount = 0;
            while ($row = $cartItemsResult->fetch_assoc()) {
                $cartItems[] = $row;
                $totalAmount += $row['price'] * $row['quantity'];
            }

            if (empty($cartItems)) {
                throw new Exception("Корзина пуста");
            }

            $insertOrderStmt = $conn->prepare("INSERT INTO orders (session_id, total_amount) VALUES (?, ?)");
            $insertOrderStmt->bind_param("sd", $sessionId, $totalAmount);
            $insertOrderStmt->execute();
            $orderId = $conn->insert_id;

            foreach ($cartItems as $item) {
                $quantityStmt = null;
                if ($item['category'] === 'bryuki') {
                    $quantityStmt = $conn->prepare("SELECT quantity FROM bryuki WHERE id = ?");
                } elseif ($item['category'] === 'futbolki') {
                    $quantityStmt = $conn->prepare("SELECT quantity FROM futbolki WHERE id = ?");
                } elseif ($item['category'] === 'kofty') {
                    $quantityStmt = $conn->prepare("SELECT quantity FROM kofty WHERE id = ?");
                }
                if ($quantityStmt) {
                    $quantityStmt->bind_param("i", $item['product_id']);
                    $quantityStmt->execute();
                    $quantityResult = $quantityStmt->get_result();
                    $productData = $quantityResult->fetch_assoc();
                    $availableQuantity = $productData['quantity'] ?? 0;
                    if ($item['quantity'] > $availableQuantity) {
                        $conn->rollback();
                        die("Ошибка: количество товара '{$item['name']}' превышает доступное на складе.");
                    }
                }
            }

            $insertItemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, category, name, image_path, quantity, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $insertItemStmt->bind_param("iisssid", $orderId, $item['product_id'], $item['category'], $item['product_name'], $item['product_image'], $item['quantity'], $item['price']);
                $insertItemStmt->execute();
            }

            if ($_POST['action'] === 'order_selected') {
                $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
                $deleteQuery = "DELETE FROM cart WHERE session_id = ? AND id IN ($placeholders)";
                $deleteStmt = $conn->prepare($deleteQuery);
                $types = str_repeat('i', count($selectedItems));
                $params = array_merge([$sessionId], $selectedItems);
                $deleteStmt->bind_param("s" . $types, ...$params);
                $deleteStmt->execute();
            } else {
                $clearCartStmt = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
                $clearCartStmt->bind_param("s", $sessionId);
                $clearCartStmt->execute();
            }

            $conn->commit();

            header('Location: orders.php?mode=my_orders');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            die("Ошибка при создании заказа: " . $e->getMessage());
        }
    }

    try {
        $query = "
            SELECT c.id as cart_id, c.product_id, c.category, c.quantity,
                COALESCE(b.name, f.name, k.name) as name,
                COALESCE(b.price, f.price, k.price) as price,
                COALESCE(b.image_path, f.image_path, k.image_path) as image_path
            FROM cart c
            LEFT JOIN bryuki b ON c.product_id = b.id AND c.category = 'bryuki'
            LEFT JOIN futbolki f ON c.product_id = f.id AND c.category = 'futbolki'
            LEFT JOIN kofty k ON c.product_id = k.id AND c.category = 'kofty'
            WHERE c.session_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartItems = [];
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
        }
    } catch (Exception $e) {
        die("Ошибка при получении корзины: " . $e->getMessage());
    }

    function calculateTotal($items) {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8" />
        <title>Корзина - Девичья феерия</title>
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
            nav {
                margin-bottom: 20px;
                text-align: center;
            }
            nav a {
                display: inline-block;
                margin: 0 10px;
                padding: 8px 15px;
                background-color: #d81b60;
                color: white;
                text-decoration: none;
                border-radius: 30px;
                font-weight: 700;
                transition: background-color 0.3s ease;
            }
            nav a:hover {
                background-color: #ad1457;
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
            .actions a {
                color: #d81b60;
                text-decoration: none;
                font-weight: bold;
                margin: 0 5px;
            }
            .actions a:hover {
                text-decoration: underline;
            }
            .total {
                text-align: right;
                font-size: 20px;
                font-weight: 700;
                margin-top: 15px;
                color: #880e4f;
            }
            .empty {
                text-align: center;
                margin-top: 40px;
                font-size: 18px;
                color: #880e4f;
            }
            .clear-cart {
                display: block;
                margin: 20px auto;
                background-color: #d81b60;
                color: white;
                padding: 10px 20px;
                border-radius: 30px;
                text-align: center;
                width: 150px;
                font-weight: 700;
                text-decoration: none;
            }
            .clear-cart:hover {
                background-color: #ad1457;
            }
        </style>
    </head>
    <body>
        <nav>
            <a href="beautiful_shop.html">Вернуться к покупкам</a>
            <a href="orders.php?mode=cart&action=clear">Очистить корзину</a>
            <a href="orders.php?mode=my_orders">Мои заказы</a>
        </nav>
        <h1>Ваша корзина</h1>

        <?php if (empty($cartItems)): ?>
            <p class="empty">Ваша корзина пуста.</p>
        <?php else: ?>
            <form method="post" action="orders.php?mode=cart">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll" /></th>
                        <th>Изображение</th>
                        <th>Название</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><input type="checkbox" name="selected_items[]" value="<?php echo $item['cart_id']; ?>" /></td>
                        <td><img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" /></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td class="actions">
                            <a href="orders.php?mode=cart&action=remove&cart_item_id=<?php echo urlencode($item['cart_id']); ?>">Удалить</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="total">Итого: <?php echo number_format(calculateTotal($cartItems), 2, ',', ' '); ?> ₽</p>
            <button type="submit" name="action" value="order_selected" style="background-color:#d81b60; color:white; padding:10px 20px; border:none; border-radius:30px; font-weight:700; cursor:pointer;">Заказать выбранные</button>
            <button type="submit" name="action" value="order" style="background-color:#880e4f; color:white; padding:10px 20px; border:none; border-radius:30px; font-weight:700; cursor:pointer; margin-left: 10px;">Заказать все</button>
            <button type="submit" name="action" value="remove_selected" style="background-color:#ad1457; color:white; padding:10px 20px; border:none; border-radius:30px; font-weight:700; cursor:pointer; margin-left: 10px;">Удалить выбранные</button>
            </form>
        <?php endif; ?>

        <script>
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        </script>
    </body>
    </html>
<?php
} elseif ($mode === 'my_orders') {
    // Логика отображения заказов и обновления статуса
    if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $orderId = intval($_POST['order_id']);
        $newStatus = $_POST['new_status'];

        $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND session_id = ?");
        $updateStmt->bind_param("sis", $newStatus, $orderId, $sessionId);
        $updateStmt->execute();
        header("Location: orders.php?mode=my_orders");
        exit;
    }

    try {
        $ordersStmt = $conn->prepare("SELECT * FROM orders WHERE session_id = ? ORDER BY order_date DESC");
        $ordersStmt->bind_param("s", $sessionId);
        $ordersStmt->execute();
        $ordersResult = $ordersStmt->get_result();
        $orders = [];
        while ($order = $ordersResult->fetch_assoc()) {
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
                                <td><img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" /></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</td>
                                <td><?php echo $item['quantity']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

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
<?php
}
?>
