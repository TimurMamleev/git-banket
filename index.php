<?php
$pageTitle = 'Главная';
require 'includes/header.php';

// Получаем последние 3 заказа пользователя (если авторизован)
$recentOrders = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("
        SELECT o.*, GROUP_CONCAT(v.name SEPARATOR ', ') as vehicles_list
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN vehicles v ON oi.vehicle_id = v.id
        WHERE o.user_id = ? 
        GROUP BY o.id
        ORDER BY o.created_at DESC LIMIT 3
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recentOrders = $stmt->fetchAll();
}

// Статистика для всех
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$completedOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();
$activeOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('new', 'processing', 'on_way')")->fetchColumn();
$totalVehicles = $pdo->query("SELECT SUM(stock) FROM vehicles WHERE is_active = 1")->fetchColumn();

$statuses = [
    'new' => ['label' => 'Новый', 'color' => '#0066CC', 'bg' => '#e8f0fe'],
    'processing' => ['label' => 'В обработке', 'color' => '#ed6c02', 'bg' => '#fff4e5'],
    'on_way' => ['label' => 'В пути', 'color' => '#2e7d32', 'bg' => '#e8f5e9'],
    'delivered' => ['label' => 'Доставлен', 'color' => '#00897b', 'bg' => '#e0f2f1'],
    'cancelled' => ['label' => 'Отменён', 'color' => '#d32f2f', 'bg' => '#ffebee']
];
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap');

* { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --primary: #0057B3;
    --primary-dark: #003d82;
    --primary-light: #eef4ff;
    --primary-glow: rgba(0,87,179,0.08);
    --text: #111827;
    --text-light: #4b5563;
    --text-muted: #9ca3af;
    --bg: #ffffff;
    --bg-light: #f9fafb;
    --bg-card: #ffffff;
    --border: #e5e7eb;
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.02);
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.04);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.04);
    --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.05);
    --radius: 24px;
    --radius-md: 16px;
    --radius-sm: 12px;
    --transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--text);
    background: var(--bg);
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
}

.container { max-width: 1280px; margin: 0 auto; padding: 0 32px; }

/* Hero */
.hero {
    text-align: center;
    padding: 64px 24px;
    background: linear-gradient(145deg, var(--bg-light) 0%, var(--bg) 100%);
    border-radius: var(--radius);
    margin-bottom: 56px;
    border: 1px solid var(--border);
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary-light);
    color: var(--primary);
    font-size: 13px;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 40px;
    margin-bottom: 24px;
}

.hero h1 {
    font-size: 48px;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin-bottom: 20px;
    background: linear-gradient(135deg, var(--text) 0%, var(--primary) 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero p {
    font-size: 18px;
    color: var(--text-light);
    margin-bottom: 32px;
    max-width: 560px;
    margin-left: auto;
    margin-right: auto;
}

.hero-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Статистика */
.stats {
    display: flex;
    justify-content: center;
    gap: 64px;
    margin-bottom: 64px;
    flex-wrap: wrap;
    padding: 24px 0;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}

.stat-card {
    text-align: center;
}

.stat-number {
    font-size: 38px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 6px;
}

.stat-label {
    font-size: 14px;
    color: var(--text-muted);
}

/* Секции */
.section-header {
    text-align: center;
    margin-bottom: 40px;
}

.section-tag {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: var(--primary);
    margin-bottom: 12px;
}

.section-title {
    font-size: 30px;
    font-weight: 650;
    letter-spacing: -0.02em;
    color: var(--text);
}

/* Карточки заказов */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 64px;
}

.order-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 20px 24px;
    transition: var(--transition);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.order-card:hover {
    border-color: var(--primary-light);
    box-shadow: var(--shadow-md);
}

.order-info {
    flex: 2;
}

.order-number {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 6px;
}

.order-number a {
    color: var(--text);
    text-decoration: none;
}

.order-number a:hover {
    color: var(--primary);
}

.order-route {
    font-size: 13px;
    color: var(--text-muted);
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.order-status {
    flex: 0 0 auto;
}

.status {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 500;
}

.order-price {
    font-weight: 600;
    font-size: 18px;
    color: var(--primary);
    min-width: 100px;
    text-align: right;
}

.empty-state {
    text-align: center;
    padding: 48px 24px;
    background: var(--bg-light);
    border-radius: var(--radius-sm);
}

.empty-state-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

/* Преимущества */
.features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    margin-bottom: 64px;
}

.feature {
    text-align: center;
    padding: 32px 24px;
    background: var(--bg-card);
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    transition: var(--transition);
}

.feature:hover {
    transform: translateY(-4px);
    border-color: var(--primary-light);
    box-shadow: var(--shadow-lg);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-light);
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin: 0 auto 20px;
}

.feature h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;
}

.feature p {
    color: var(--text-light);
    font-size: 14px;
}

/* Кнопки */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 28px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border-radius: 40px;
    transition: var(--transition);
    cursor: pointer;
    border: none;
    font-family: inherit;
}

.btn-primary {
    background: var(--primary);
    color: white;
    box-shadow: 0 2px 8px rgba(0,87,179,0.2);
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,87,179,0.25);
}

.btn-outline {
    background: transparent;
    color: var(--primary);
    border: 1.5px solid var(--primary);
}

.btn-outline:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
}

.btn-secondary {
    background: var(--bg-light);
    color: var(--text);
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    border-color: var(--primary);
    color: var(--primary);
}

/* Анимации */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.hero, .stats, .orders-list, .features {
    animation: fadeUp 0.4s ease-out forwards;
}

/* Адаптив */
@media (max-width: 768px) {
    .container { padding: 0 20px; }
    .hero { padding: 40px 20px; margin-bottom: 40px; }
    .hero h1 { font-size: 32px; }
    .hero p { font-size: 16px; }
    .section-title { font-size: 24px; }
    .stats { gap: 32px; margin-bottom: 48px; }
    .features { grid-template-columns: 1fr; gap: 20px; margin-bottom: 48px; }
    .order-card { flex-direction: column; align-items: flex-start; }
    .order-price { text-align: left; }
}
</style>

<section class="hero">
    <div class="hero-badge">
        <span>✦</span> Управление заказами
    </div>
    <h1>ООО «Наталья»<br>грузоперевозки по всей России</h1>
    <p>Оформляйте заказы онлайн, отслеживайте статус в реальном времени</p>
    <div class="hero-buttons">
        <a href="create_order.php" class="btn btn-primary">📦 Оформить заказ</a>
        <?php if (!isLoggedIn()): ?>
            <a href="login.php" class="btn btn-outline">🔐 Войти в личный кабинет</a>
        <?php else: ?>
            <a href="orders.php" class="btn btn-outline">📋 Мои заказы</a>
        <?php endif; ?>
    </div>
</section>

<div class="stats">
    <div class="stat-card"><div class="stat-number"><?= number_format($totalOrders, 0, ',', ' ') ?></div><div class="stat-label">всего заказов</div></div>
    <div class="stat-card"><div class="stat-number"><?= $activeOrders ?></div><div class="stat-label">в работе</div></div>
    <div class="stat-card"><div class="stat-number"><?= $completedOrders ?></div><div class="stat-label">доставлено</div></div>
    <div class="stat-card"><div class="stat-number"><?= number_format($totalVehicles, 0, ',', ' ') ?></div><div class="stat-label">единиц техники</div></div>
</div>

<?php if (isLoggedIn() && !empty($recentOrders)): ?>
<div class="section-header">
    <div class="section-tag">Последние заказы</div>
    <div class="section-title">Ваши <span style="color: var(--primary);">последние заявки</span></div>
</div>
<div class="orders-list">
    <?php foreach ($recentOrders as $o): ?>
    <div class="order-card">
        <div class="order-info">
            <div class="order-number">
                <a href="order.php?id=<?= $o['id'] ?>">Заказ №<?= $o['id'] ?></a>
            </div>
            <div class="order-route">
                <span>📍 <?= esc($o['address_from']) ?></span>
                <span>→</span>
                <span>🎯 <?= esc($o['address_to']) ?></span>
            </div>
        </div>
        <div class="order-status">
            <span class="status" style="color: <?= $statuses[$o['status']]['color'] ?>; background: <?= $statuses[$o['status']]['bg'] ?>;">
                <?= $statuses[$o['status']]['label'] ?>
            </span>
        </div>
        <div class="order-price"><?= number_format($o['total_price'], 0, ',', ' ') ?> ₽</div>
    </div>
    <?php endforeach; ?>
</div>
<div style="text-align: center; margin-bottom: 48px;">
    <a href="orders.php" class="btn btn-outline">Смотреть все заказы →</a>
</div>
<?php elseif (isLoggedIn()): ?>
<div class="empty-state" style="margin-bottom: 48px;">
    <div class="empty-state-icon">📭</div>
    <p style="margin-bottom: 16px; color: var(--text-muted);">У вас пока нет заказов</p>
    <a href="create_order.php" class="btn btn-primary">Оформить первый заказ</a>
</div>
<?php endif; ?>

<div class="section-header">
    <div class="section-tag">Преимущества</div>
    <div class="section-title">Почему выбирают <span style="color: var(--primary);">нас</span></div>
</div>
<div class="features">
    <div class="feature">
        <div class="feature-icon">⚡</div>
        <h3>Быстрое оформление</h3>
        <p>Оформите заказ за 2 минуты без лишних звонков</p>
    </div>
    <div class="feature">
        <div class="feature-icon">📊</div>
        <h3>Отслеживание статуса</h3>
        <p>Всегда знайте, где находится ваш груз</p>
    </div>
    <div class="feature">
        <div class="feature-icon">📋</div>
        <h3>Прозрачная отчётность</h3>
        <p>История заказов и полная информация по каждой перевозке</p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>