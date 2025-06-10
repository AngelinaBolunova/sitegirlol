<?php
session_start();
require_once 'db_connected.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Пожалуйста, заполните все поля.';
    } else {
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header('Location: beautiful_shop.html');
            exit;
        } else {
            $error = 'Неверное имя пользователя или пароль.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Вход в аккаунт - Девичья феерия</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff0f6;
            color: #660033;
            border: 1px solid #d81b60;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
            color: #d81b60;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            font-weight: 700;
        }
        input[type="text"],
        input[type="password"] {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #d81b60;
            border-radius: 5px;
        }
        button {
            margin-top: 20px;
            background-color: #d81b60;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
        }
        button:hover {
            background-color: #ad1457;
        }
        .error {
            color: #d81b60;
            font-weight: 700;
            margin-top: 10px;
            text-align: center;
        }
        .register-link {
            margin-top: 15px;
            text-align: center;
        }
        .register-link a {
            color: #d81b60;
            text-decoration: none;
            font-weight: 700;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Вход в аккаунт</h1>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label for="username">Имя пользователя</label>
        <input type="text" id="username" name="username" required />
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required />
        <button type="submit">Войти</button>
    </form>
    <div class="register-link">
        <a href="register.php">Регистрация</a>
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <form method="get" action="beautiful_shop.html">
            <button type="submit" style="background-color: #d81b60; color: white; border: none; padding: 10px 20px; border-radius: 30px; font-weight: 700; cursor: pointer;">
                Продолжить без авторизации
            </button>
        </form>
    </div>
</body>
</html>
