<?php
session_start();
require_once 'db.php';

$mode = $_GET['mode'] ?? 'login'; // login или register

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'login') {
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
    } elseif ($mode === 'register') {
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
                    $mode = 'login';
                } else {
                    $error = 'Ошибка при регистрации. Попробуйте позже.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title><?php echo $mode === 'login' ? 'Вход в аккаунт' : 'Регистрация'; ?> - Девичья феерия</title>
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
        .switch-link {
            margin-top: 15px;
            text-align: center;
        }
        .switch-link a {
            color: #d81b60;
            text-decoration: none;
            font-weight: 700;
        }
        .switch-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1><?php echo $mode === 'login' ? 'Вход в аккаунт' : 'Регистрация'; ?></h1>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="post" action="auth.php?mode=<?php echo $mode; ?>">
        <label for="username">Имя пользователя</label>
        <input type="text" id="username" name="username" required />
        <?php if ($mode === 'register'): ?>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required />
        <?php endif; ?>
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password" required />
        <?php if ($mode === 'register'): ?>
            <label for="password_confirm">Подтверждение пароля</label>
            <input type="password" id="password_confirm" name="password_confirm" required />
        <?php endif; ?>
        <button type="submit"><?php echo $mode === 'login' ? 'Войти' : 'Зарегистрироваться'; ?></button>
    </form>
    <div class="switch-link">
        <?php if ($mode === 'login'): ?>
            <a href="auth.php?mode=register">Регистрация</a>
        <?php else: ?>
            <a href="auth.php?mode=login">Вход в аккаунт</a>
        <?php endif; ?>
    </div>
</body>
</html>
