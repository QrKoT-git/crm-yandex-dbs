<?php
session_start();

const PASSWORD = 'пароль_входа';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputPassword = $_POST['password'] ?? '';

    if ($inputPassword === PASSWORD) {
        $_SESSION['logged_in'] = true;
        header('Location: market.php');
        exit;
    } else {
        $error = 'Неверный пароль';
    }
}

$loggedIn = $_SESSION['logged_in'] ?? false;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Вход в кабинет</title>
    <style>
        /* Сброс стилей */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Yandex Sans", "Arial", sans-serif;
            background: #f5f6f8;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f1f1f;
        }

        .container {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px 50px;
            box-shadow:
                0 2px 6px rgba(0,0,0,0.07),
                0 10px 20px rgba(0,0,0,0.05);
            width: 360px;
            text-align: center;
        }

        h2 {
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 30px;
            color: #2e2e33;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        input[type="password"] {
            font-size: 16px;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1.5px solid #d2d3db;
            transition: border-color 0.3s ease;
            outline-offset: 2px;
        }

        input[type="password"]:focus {
            border-color: #00b4ff;
            outline: none;
            box-shadow: 0 0 8px rgba(0,180,255,0.3);
        }

        input[type="submit"] {
            background-color: #00b4ff;
            color: white;
            font-weight: 600;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            padding: 14px 0;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow:
                inset 0 -3px 0 rgba(0,180,255,0.7);
        }

        input[type="submit"]:hover {
            background-color: #0097d6;
            box-shadow:
                inset 0 -4px 6px rgba(0,150,214,0.9),
                0 4px 12px rgba(0,150,214,0.3);
        }

        .error {
            color: #ff4d4f;
            font-size: 14px;
            margin-bottom: -10px;
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .btn {
            display: block;
            background-color: #00b4ff;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            padding: 14px 20px;
            border-radius: 10px;
            box-shadow:
                inset 0 -3px 0 rgba(0,180,255,0.7);
            transition:
                background-color 0.3s ease,
                box-shadow 0.3s ease,
                transform 0.15s ease;
        }

        .btn:hover,
        .btn:focus {
            background-color: #0097d6;
            box-shadow:
                inset 0 -4px 6px rgba(0,150,214,0.9),
                0 6px 16px rgba(0,150,214,0.35);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(1px);
            box-shadow:
                inset 0 -2px 4px rgba(0,130,184,0.9);
        }

        /* Дополнительный стиль для кнопки выхода */
        .logout-btn {
            margin-top: 30px;
            background-color: #ff4d4f;
            box-shadow:inset 0 -3px 0 rgba(255,77,79,0.7);
        }
        .logout-btn:hover,
        .logout-btn:focus {
            background-color: #d9363e;
            box-shadow:
                inset 0 -4px 6px rgba(217,54,62,0.9),
                0 6px 16px rgba(217,54,62,0.35);
        }
    </style>
</head>
<body>

<div class="container">
<?php if (!$loggedIn): ?>
    <h2>Вход в маркет</h2>
    <?php if ($error): ?>
        <div class="error"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <form method="post" action="market.php" autocomplete="off" novalidate>
        <input type="password" name="password" placeholder="Введите пароль" required autofocus />
        <input type="submit" value="Войти" />
    </form>
<?php else: ?>
    <h2>Выберите что отправляем!</h2>
    <div class="buttons" role="navigation" aria-label="Основные действия">
        <a href="order_pull_send.php" class="btn">Отправка WG</a>
        <a href="proxy_send.php" class="btn">Отправка Прокси</a>
        <a href="vless_send.php" class="btn">Отправка VLESS</a>
    </div>
    <form method="post" action="market.php" style="margin-top:30px;">
        <input type="hidden" name="logout" value="1" />
        <button type="submit" class="btn logout-btn" style="width:100%; cursor:pointer;">Выйти</button>
    </form>
<?php endif; ?>

</div>

<?php
// Обработка выхода
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header('Location: market.php');
    exit;
}
?>
</body>
</html>
