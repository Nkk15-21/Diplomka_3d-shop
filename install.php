<?php
declare(strict_types=1);

$host = 'localhost';
$user = 'root';
$password = '';

$conn = @new mysqli($host, $user, $password);

if ($conn->connect_errno) {
    die('Ошибка подключения к MySQL: (' . $conn->connect_errno . ') ' . $conn->connect_error);
}

$sqlFile = __DIR__ . '/db/install.sql';

if (!file_exists($sqlFile)) {
    die('Файл db/install.sql не найден.');
}

$sql = file_get_contents($sqlFile);

if ($sql === false) {
    die('Не удалось прочитать install.sql.');
}

if (!$conn->multi_query($sql)) {
    die('Ошибка выполнения SQL: ' . $conn->error);
}

do {
    if ($result = $conn->store_result()) {
        $result->free();
    }
} while ($conn->more_results() && $conn->next_result());

if ($conn->error) {
    die('Ошибка после выполнения SQL: ' . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Установка проекта</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main class="container">
    <div class="message success">
        <h1>Установка завершена</h1>
        <p>База данных <strong>3d_print_shop</strong> успешно создана.</p>
        <p><a class="btn" href="index.php">Перейти на главную</a></p>
    </div>
</main>
</body>
</html>