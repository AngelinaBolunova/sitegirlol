<?php
session_start();

require_once 'db_connected.php';

$colorFilter = $_GET['color'] ?? '';

if ($colorFilter) {
    $stmt = $conn->prepare("SELECT id, name, price, image_path, description, quantity FROM futbolki WHERE color = ?");
    $stmt->bind_param("s", $colorFilter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT id, name, price, image_path, description, quantity FROM futbolki";
    $result = $conn->query($query);
}

if (!$result) {
    die("Ошибка при получении данных: " . $conn->error);
}

$products = [];
$totalQuantity = 0;
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
    $totalQuantity += intval($row['quantity']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Отдел Футболки - Девичья феерия</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .product {
            border: 1px solid #d81b60;
            border-radius: 10px;
            padding: 10px;
            margin: 10px;
            text-align: center;
            background: #fff0f6;
            box-shadow: 0 0 10px rgba(216, 27, 96, 0.3);
            width: 200px;
            display: inline-block;
            vertical-align: top;
            cursor: pointer;
            position: relative;
        }
        .product img {
            max-width: 100%;
            border-radius: 10px;
        }
        .product-name {
            font-weight: 700;
            color: #d81b60;
            margin: 10px 0 5px 0;
        }
        .product-price {
            color: #880e4f;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .add-to-cart-btn {
            background-color: #d81b60;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s ease;
        }
        .add-to-cart-btn:hover {
            background-color: #ad1457;
        }
        /* Модальное окно */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff0f6;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #d81b60;
            border-radius: 15px;
            width: 80%;
            max-width: 500px;
            color: #660033;
            font-family: 'Brush Script MT', cursive;
            font-size: 18px;
            box-shadow: 0 0 20px rgba(255, 51, 153, 0.5);
            position: relative;
        }
        .close-btn {
            color: #d81b60;
            font-weight: 700;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="container">
        <div class="logo">Девичья феерия</div>
        <nav>
            <ul>
                <li><a href="beautiful_shop.html">Главная</a></li>
                <li><a href="about_us.html">О нас</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <button class="btn" onclick="window.location.href='store_reviews.php'">Отзывы о магазине</button>
            <button class="btn" onclick="window.location.href='cart.php'">Корзина</button>
            <button class="btn" onclick="window.location.href='login.php'">Вход в аккаунт</button>
        </div>
    </header>
    <div class="search-bar">
        <form method="get" action="futbolki.php" id="filterForm" style="display: inline-block;">
            <label for="colorFilter">Фильтр по цвету:</label>
            <select name="color" id="colorFilter" onchange="document.getElementById('filterForm').submit();">
                <option value="">Все цвета</option>
                <option value="белый" <?php if (isset($_GET['color']) && $_GET['color'] === 'белый') echo 'selected'; ?>>Белый</option>
                <option value="розовый" <?php if (isset($_GET['color']) && $_GET['color'] === 'розовый') echo 'selected'; ?>>Розовый</option>
                <option value="черный" <?php if (isset($_GET['color']) && $_GET['color'] === 'черный') echo 'selected'; ?>>Черный</option>
                <option value="бежевый" <?php if (isset($_GET['color']) && $_GET['color'] === 'бежевый') echo 'selected'; ?>>Бежевый</option>
            </select>
        </form>
    </div>
    <main>
        <nav style="margin-bottom: 20px; text-align: center;">
            <a href="futbolki.php" style="margin: 0 10px; color: #d81b60; font-weight: bold; text-decoration: underline;">Футболки</a> |
            <a href="kofty.php" style="margin: 0 10px; color: #d81b60; font-weight: bold; text-decoration: none;">Кофты</a> |
            <a href="bryuki.php" style="margin: 0 10px; color: #d81b60; font-weight: bold; text-decoration: none;">Брюки</a>
        </nav>
        <h1 class="category-header">Отдел Футболки</h1>
        <div class="section-quantity" style="text-align:center; font-weight:bold; color:#d81b60; margin-bottom: 15px;">
            В наличии: <?php echo $totalQuantity; ?>
        </div>
        <!-- Removed redundant color filter form here -->
        <div class="products-container">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-price"><?php echo number_format($product['price'], 2, ',', ' '); ?> ₽</div>
                    <div class="product-quantity" style="font-weight: 600; color: #880e4f; margin-bottom: 10px;">
                        В наличии: <?php echo intval($product['quantity']); ?>
                    </div>
                    <form method="post" action="cart.php">
                        <input type="hidden" name="action" value="add" />
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
                        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" />
                        <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>" />
                        <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image_path']); ?>" />
                        <input type="hidden" name="category" value="futbolki" />
                        <input type="number" name="quantity" value="1" min="1" style="width: 50px;" />
                        <button type="submit" class="add-to-cart-btn">Добавить в корзину</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <footer>
        © 2025 Девичья феерия. Все права защищены.
    </footer>

    <!-- Модальное окно для описания товара -->
    <!-- Удалено модальное окно и скрипт показа описания -->
</body>
</html>
