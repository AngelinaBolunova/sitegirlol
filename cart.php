<?php
session_start();
require_once 'db_connected.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Ошибка: пользователь не авторизован.");
}
$userId = $_SESSION['user_id'];

// Инициализация корзины в сессии, если не существует
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Добавление товара в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = floatval($_POST['product_price']);
    $productImage = $_POST['product_image'];
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $category = $_POST['category'] ?? '';

    // Проверка, есть ли товар уже в корзине
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'name' => $productName,
            'price' => $productPrice,
            'image' => $productImage,
            'quantity' => $quantity,
            'category' => $category
        ];
    }

    header('Location: cart.php');
    exit;
}

// Удаление товара из корзины
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    header('Location: cart.php');
    exit;
}

// Очистка корзины
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'order_selected') {
    $selectedItems = $_POST['selected_items'] ?? [];
    if (!empty($selectedItems)) {
        try {
            $conn->begin_transaction();

            // Создаем заказ с user_id
            $totalAmount = 0;
    foreach ($selectedItems as $productId) {
        if (!isset($_SESSION['cart'][$productId])) {
            throw new Exception("Товар с ID $productId не найден в корзине.");
        }
        $item = $_SESSION['cart'][$productId];
        // Отладочный вывод категории товара
        error_log("Категория товара ID $productId: '" . ($item['category'] ?? '') . "'");
        $totalAmount += $item['price'] * $item['quantity'];
    }

            $insertOrderStmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
            $insertOrderStmt->bind_param("id", $userId, $totalAmount);
            $insertOrderStmt->execute();
            $orderId = $conn->insert_id;

            // Вставляем товары в order_items
$insertItemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, category, name, image_path, quantity, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
$updateQuantityStmt = null;
foreach ($selectedItems as $productId) {
    $item = $_SESSION['cart'][$productId];
    $category = $item['category'] ?? '';
    $imagePath = $item['image'] ?? '';
    $insertItemStmt->bind_param("iisssid", $orderId, $productId, $category, $item['name'], $imagePath, $item['quantity'], $item['price']);
    if (!$insertItemStmt->execute()) {
        throw new Exception("Ошибка вставки товара в order_items: " . $insertItemStmt->error);
    }
    // Уменьшаем количество товара в соответствующей таблице
    // Удаляем этот блок, так как подготовка с "UPDATE ?" невозможна
    // Вместо этого формируем запрос динамически ниже

    // Параметры для обновления: таблица - $category, количество - $item['quantity'], id - $productId
    // Проверяем допустимость категории
    $allowedCategories = ['kofty', 'bryuki', 'futbolki'];
    if (!in_array($category, $allowedCategories, true)) {
        throw new Exception("Недопустимая категория товара: " . htmlspecialchars($category));
    }
    // Формируем запрос с жестко заданным именем таблицы
    $updateQuery = "UPDATE `$category` SET quantity = quantity - ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    if ($stmtUpdate === false) {
        throw new Exception("Ошибка подготовки запроса на обновление количества товара.");
    }
    $stmtUpdate->bind_param("ii", $item['quantity'], $productId);
    if (!$stmtUpdate->execute()) {
        throw new Exception("Ошибка обновления количества товара: " . $stmtUpdate->error);
    }
    $stmtUpdate->close();
}

            // Удаляем заказанные товары из корзины
            foreach ($selectedItems as $productId) {
                unset($_SESSION['cart'][$productId]);
            }

            $conn->commit();

            header('Location: my_orders.php');
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            die("Ошибка при оформлении заказа: " . $e->getMessage());
        }
    }
}

// Подсчет общей стоимости
function calculateTotal($cart) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

// Подсчет общей стоимости с учетом скидки 20% в июне
function calculateDiscountedTotal($cart) {
    $total = calculateTotal($cart);
    $currentMonth = date('n'); // 1-12
    if ($currentMonth == 6) { // Июнь
        $total = $total * 0.8; // Скидка 20%
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
        .clear-cart, .back-home, .order-selected {
            display: inline-block;
            margin: 10px 10px 20px 0;
            background-color: #d81b60;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-align: center;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        .clear-cart:hover, .back-home:hover, .order-selected:hover {
            background-color: #ad1457;
        }
    </style>
</head>
<body>
    <h1>Ваша корзина</h1>
    <?php if (empty($_SESSION['cart'])): ?>
        <p class="empty">Ваша корзина пуста.</p>
        <a href="beautiful_shop.html" class="back-home">← Вернуться на главную</a>
    <?php else: ?>
        <form method="post" action="cart.php">
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
                <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
                <tr>
                    <td><input type="checkbox" name="selected_items[]" value="<?php echo htmlspecialchars($productId); ?>" /></td>
                    <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" /></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo number_format($item['price'], 2, ',', ' '); ?> ₽</td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td class="actions">
                        <a href="cart.php?action=remove&product_id=<?php echo urlencode($productId); ?>">Удалить</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="total">
            <?php
            $currentMonth = date('n');
            $total = calculateTotal($_SESSION['cart']);
            if ($currentMonth == 6) {
                $discountedTotal = calculateDiscountedTotal($_SESSION['cart']);
                echo "Итого: <del>" . number_format($total, 2, ',', ' ') . " ₽</del> <strong>" . number_format($discountedTotal, 2, ',', ' ') . " ₽</strong>";
                echo "<br><small style='color: #d81b60;'>Скидка 20% на все товары в июне!</small>";
            } else {
                echo "Итого: " . number_format($total, 2, ',', ' ') . " ₽";
            }
            ?>
        </p>
        <button type="submit" name="action" value="order_selected" class="order-selected">Заказать выбранные</button>
        <a href="beautiful_shop.html" class="back-home">← Вернуться на главную</a>
        <a href="cart.php?action=clear" class="clear-cart">Очистить корзину</a>
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
