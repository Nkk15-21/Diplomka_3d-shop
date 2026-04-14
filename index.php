<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/header.php';
?>

    <section class="hero">
        <h1>Услуги 3D-печати и готовые изделия</h1>
        <p>
            Добро пожаловать в 3D Print Shop. Здесь можно заказать готовые 3D-печатные товары,
            отправить свой файл на печать и подобрать материал под задачу.
        </p>

        <div class="hero-actions">
            <a class="btn" href="catalog.php">Открыть каталог</a>
            <a class="btn btn-secondary" href="custom_order.php">Индивидуальный заказ</a>
        </div>
    </section>

    <section>
        <h2 class="section-title">Что мы предлагаем</h2>
        <div class="grid-3">
            <div class="info-card">
                <h3>Готовые товары</h3>
                <p>Подставки, аксессуары, декор, функциональные детали и другие 3D-печатные изделия.</p>
            </div>

            <div class="info-card">
                <h3>Печать по вашему файлу</h3>
                <p>Загрузите модель, выберите материал и получите ориентировочную стоимость заказа.</p>
            </div>

            <div class="info-card">
                <h3>Подбор материала</h3>
                <p>PLA, PETG, ABS, ASA, Nylon, TPU и другие варианты под внешний вид и нагрузку.</p>
            </div>
        </div>
    </section>

    <section style="margin-top: 30px;">
        <h2 class="section-title">Почему это удобно</h2>
        <div class="grid-3">
            <div class="card">
                <h3>Быстрый старт</h3>
                <p>Можно оформить заказ сразу после регистрации в личном кабинете.</p>
            </div>

            <div class="card">
                <h3>Понятная цена</h3>
                <p>Для индивидуального заказа система считает примерную стоимость ещё до отправки формы.</p>
            </div>

            <div class="card">
                <h3>Удобная связь</h3>
                <p>Через форму контактов можно задать вопрос, уточнить сроки и обсудить детали печати.</p>
            </div>
        </div>
    </section>

<?php
require_once __DIR__ . '/includes/footer.php';