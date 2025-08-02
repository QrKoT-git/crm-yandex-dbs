<?php

$campaignId = 'Ид кампании (не кабинет)';
$apiKey = 'токен';

function sendDigitalKey($campaignId, $orderId, $apiKey) {
    $headers = [
        "Api-Key: $apiKey",
        "Content-Type: application/json",
        "Accept: application/json",
    ];

    // Получаем детали заказа
    $urlGetOrder = "https://api.partner.market.yandex.ru/campaigns/$campaignId/orders/$orderId";

    $ch = curl_init($urlGetOrder);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        return ['error' => "Ошибка при получении заказа: " . curl_error($ch)];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode < 200 || $httpCode >= 300) {
        return ['error' => "Ошибка HTTP при получении заказа: $httpCode. Ответ сервера: $response"];
    }

    $orderData = json_decode($response, true);
    curl_close($ch);

    if (!isset($orderData['order']['items'][0]['id'])) {
        return ['error' => "Не удалось найти id товара в ответе от API."];
    }

    $itemId = $orderData['order']['items'][0]['id'];

    // Отправляем цифровой ключ
    $urlDeliver = "https://api.partner.market.yandex.ru/campaigns/$campaignId/orders/$orderId/deliverDigitalGoods";

    $body = [
        "items" => [
            [
                "id" => $itemId,
                "codes" => [
                    "Пишем тут текстом цифровой ключ"
                ],
                "slip" => "Инструкция по активации ключа",
                "activate_till" => "25-08-2025"
            ]
        ]
    ];

    $ch = curl_init($urlDeliver);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));

    $responseDeliver = curl_exec($ch);
    if ($responseDeliver === false) {
        return ['error' => "Ошибка при передаче цифровых кодов: " . curl_error($ch)];
    }

    $httpCodeDeliver = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCodeDeliver >= 200 && $httpCodeDeliver < 300) {
        $dataDeliver = json_decode($responseDeliver, true);
        return ['success' => $dataDeliver];
    } else {
        return ['error' => "Ошибка HTTP при передаче цифровых кодов: $httpCodeDeliver. Ответ сервера: $responseDeliver"];
    }
}

$message = '';
$messageType = ''; // success или error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = trim($_POST['order_id'] ?? '');

    // Валидация номера заказа (только цифры и не пусто)
    if ($orderId === '') {
        $message = 'Пожалуйста, введите номер заказа.';
        $messageType = 'error';
    } else {
        // Отправляем ключ
        $result = sendDigitalKey($campaignId, $orderId, $apiKey);
        if (isset($result['error'])) {
            $message = $result['error'];
            $messageType = 'error';
        } else {
            // Красиво выводим JSON ответа
            $jsonPretty = json_encode($result['success'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $message = "<pre style='white-space: pre-wrap;'>$jsonPretty</pre>";
            $messageType = 'success';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Маркет: Отправка Прокси</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #fff;
    margin: 0;
    padding: 0;
  }
  header {
    background-color: #FFCC00;
    padding: 15px 30px;
    color: #000;
    font-weight: bold;
    font-size: 24px;
  }
  main {
    max-width: 480px;
    margin: 40px auto;
    padding: 20px 30px;
    border: 1px solid #ddd;
    border-radius: 8px;
  }
  label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
  }
  input[type="text"] {
    width: 100%;
    padding: 10px 12px;
    font-size: 16px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-sizing: border-box;
  }
  button {
    margin-top: 20px;
    background-color: #FFCC00;
    border: none;
    padding: 12px;
    width: 100%;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    border-radius: 4px;
    transition: background-color 0.3s ease;
  }
  button:hover {
    background-color: #e6b800;
  }
  .message {
    margin-top: 25px;
    padding: 15px;
    border-radius: 6px;
  }
  .message.error {
    background-color: #ffe6e6;
    color: #cc0000;
    border: 1px solid #cc0000;
  }
  .message.success {
    background-color: #e6ffe6;
    color: #007700;
    border: 1px solid #007700;
  }
  pre {
      margin:0; 
      font-family: Consolas, monospace; 
      font-size:14px; 
      white-space: pre-wrap; 
      word-break: break-word;
  }
</style>
</head>
<body>

<header>Маркет — Отправка Прокси</header>

<main>
<form method="post" novalidate>
  <label for="order_id">Номер заказа:</label>
  <input type="text" id="order_id" name="order_id" placeholder="Введите номер заказа" value="<?= htmlspecialchars($_POST['order_id'] ?? '') ?>" required pattern="d+" inputmode="numeric" />
  
  <button type="submit">Отправить ключ</button>
  
  <hr>
  <a href="market.php" style="
      display: inline-block;
      padding: 10px 20px;
      background-color: #28a745;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      text-align: center;
  "> <-- Назад</a>
</form>

<?php if ($message !== '') : ?>
<div class="message <?= htmlspecialchars($messageType) ?>">
<?= $message ?>
</div>
<?php endif; ?>

</main>

</body>
</html>
