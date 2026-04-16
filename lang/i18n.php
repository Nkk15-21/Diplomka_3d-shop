<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================================
   ДОСТУПНЫЕ ЯЗЫКИ
   ========================================================= */

$availableLanguages = ['ru', 'en', 'et'];

/* =========================================================
   ПЕРЕКЛЮЧЕНИЕ ЯЗЫКА
   ========================================================= */

if (isset($_GET['lang'])) {
    $requestedLang = trim((string)$_GET['lang']);

    if (in_array($requestedLang, $availableLanguages, true)) {
        $_SESSION['lang'] = $requestedLang;
    }
}

/* =========================================================
   ЯЗЫК ПО УМОЛЧАНИЮ
   ========================================================= */

$currentLang = $_SESSION['lang'] ?? 'ru';

if (!in_array($currentLang, $availableLanguages, true)) {
    $currentLang = 'ru';
}

/* =========================================================
   ПОДКЛЮЧЕНИЕ ФАЙЛА ПЕРЕВОДА
   ========================================================= */

$translations = require __DIR__ . '/' . $currentLang . '.php';

/* =========================================================
   ФУНКЦИЯ ПЕРЕВОДА
   ========================================================= */

function t(string $key): string
{
    global $translations;

    return $translations[$key] ?? $key;
}

/* =========================================================
   ТЕКУЩИЙ ЯЗЫК
   ========================================================= */

function currentLang(): string
{
    return $_SESSION['lang'] ?? 'ru';
}