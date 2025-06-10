<?php
session_start();
require_once 'db_connected.php';

$query = trim($_GET['query'] ?? '');

$results = [];

if ($query !== '') {
    $likeQuery = '%' . $query . '%';

    // Search in kofty
    $stmt = $conn->prepare("SELECT id, name, price, image_path, quantity, 'kofty' AS category FROM kofty WHERE name LIKE ?");
    $stmt->bind_param("s", $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    // Search in bryuki
    $stmt = $conn->prepare("SELECT id, name, price, image_path, quantity, 'bryuki' AS category FROM bryuki WHERE name LIKE ?");
    $stmt->bind_param("s", $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    // Search in futbolki
    $stmt = $conn->prepare("SELECT id, name, price, image_path, quantity, 'futbolki' AS category FROM futbolki WHERE name LIKE ?");
    $stmt->bind_param("s", $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Результаты поиска - Девичья феерия</title>
    <link rel="stylesheet" href="style.css" />
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
            cursor: default;
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
        .product-quantity {
            font-weight: 600;
            color: #660033;
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
    </style>
</head>
<body>
    <h1>Результаты поиска по запросу: "<?php echo htmlspecialchars($query); ?>"</h1>
    <div style="text-align:center; margin-bottom: 20px;">
        <a href="beautiful_shop.html" class="back-to-home-btn">← На главную</a>
    </div>
    <?php if (empty($results)): ?>
        <p>По вашему запросу ничего не найдено.</p>
    <?php else: ?>
        <?php foreach ($results as $product): ?>
            <div class="product">
                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                <div class="product-price"><?php echo number_format($product['price'], 2, ',', ' '); ?> ₽</div>
                <div class="product-quantity">В наличии: <?php echo intval($product['quantity']); ?></div>
                <form method="post" action="cart.php">
                    <input type="hidden" name="action" value="add" />
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" />
                    <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>" />
                    <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image_path']); ?>" />
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" />
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo intval($product['quantity']); ?>" style="width: 50px;" />
                    <button type="submit" class="add-to-cart-btn">Добавить в корзину</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
