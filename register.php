<?php
session_start();
require_once 'db_connected.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $password_confirm === '') {
        $error = 'Пожалуйста, заполните все поля.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email.';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают.';
    } else {
        // Проверяем, существует ли уже пользователь с таким именем или email
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = 'Пользователь с таким именем или email уже существует.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $username, $email, $password_hash);
            if ($insertStmt->execute()) {
                $success = 'Регистрация прошла успешно. Теперь вы можете войти.';
            } else {
                $error = 'Ошибка при регистрации. Попробуйте позже.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Регистрация - Девичья феерия</title>
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
        input[type="email"],
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
        .success {
            color: #388e3c;
            font-weight: 700;
            margin-top: 10px;
            text-align: center;
        }
        .login-link {
            margin-top: 15px;
            text-align: center;
        }
        .login-link a {
            color: #d81b60;
            text-decoration: none;
            font-weight: 700;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Регистрация</h1>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="post" action="register.php">
        <label for="username">Имя пользователя</label>
        <input type="text" id="username" name="username" required />
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required />
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required />
        <label for="password_confirm">Подтверждение пароля</label>
        <input type="password" id="password_confirm" name="password_confirm" required />
        <button type="submit">Зарегистрироваться</button>
    </form>
    <div class="login-link">
        <a href="login.php">Вход в аккаунт</a>
    </div>
</body>
</html>
