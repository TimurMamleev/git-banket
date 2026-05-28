<?php
include('db.php');
session_start();

// Проверка авторизации администратора
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Допустимые статусы (новые для банкетной тематики)
$valid_statuses = ['Новая', 'Банкет назначен', 'Банкет завершен'];
$status_updated = false;

// Обработка изменения статуса заявки
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';

    // Валидация статуса
    if (!in_array($status, $valid_statuses, true)) {
        die('Недопустимый статус заявки');
    }

    // Использование подготовленных выражений
    $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $request_id);

    if (!$stmt->execute()) {
        die('Ошибка обновления: ' . $con->error);
    } else {
        $status_updated = true;
    }
}

// Получение заявок с пагинацией (10 заявок на страницу)
$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$query = $con->query("
    SELECT request.*, users.login, users.fullname,
           COUNT(*) OVER() as total_count
    FROM request
    INNER JOIN users ON request.user_id = users.id
    ORDER BY request.date DESC
    LIMIT $limit OFFSET $offset
");

if (!$query) die('Ошибка запроса: ' . $con->error);

// Подсчёт статистики одним запросом
$stats_query = $con->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_requests,
        SUM(CASE WHEN status = 'Банкет назначен' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'Банкет завершен' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Банкетам.Нет</title>
    <!-- Подключение шрифта Oswald -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --gold: #DAA520;
            --rose-gold: #FFDAB9;
            --cream: #FFFDD0;
            --crimson: #DC143C;
            --forest-green: #006400;
            --border-radius: 12px;
            --shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Oswald', sans-serif;
            background: linear-gradient(135deg, var(--forest-green) 0%, #003300 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* Шапка */
        .header {
            background: white;
            padding: 25px 30px;
            border-bottom: 2px solid var(--rose-gold);
        }

        .header h1 {
            color: var(--forest-green);
            font-size: 32px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .subtitle {
            color: #666;
            font-size: 16px;
            font-weight: 300;
        }

        /* Навигация */
        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: var(--cream);
            border-bottom: 1px solid var(--rose-gold);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold), var(--forest-green));
            color: white;
        }

        .btn-outline {
            background: transparent;
            color: var(--forest-green);
            border: 2px solid var(--gold);
        }

        .btn-outline:hover {
            background: var(--gold);
            color: white;
            transform: translateY(-2px);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(218, 165, 32, 0.4);
        }

        /* Статистика */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 25px 30px;
            background: #fafafa;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 4px solid var(--gold);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 600;
            margin: 10px 0;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Список заявок */
        .requests-container {
            padding: 0 30px 30px;
        }

        .request-item {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 4px solid;
            transition: all 0.3s ease;
        }

        .request-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .user-info h3 {
            color: var(--forest-green);
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .user-info p {
            color: #666;
            font-weight: 300;
        }

        .request-id {
            background: var(--cream);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
            color: var(--forest-green);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-new {
            background: rgba(218, 165, 32, 0.15);
            color: var(--gold);
            border: 1px solid var(--gold);
        }

        .status-assigned {
            background: rgba(0, 100, 0, 0.1);
            color: var(--forest-green);
            border: 1px solid var(--forest-green);
        }

        .status-completed {
            background: rgba(218, 165, 32, 0.2);
            color: #b8860b;
            border: 1px solid var(--gold);
        }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .detail-item {
            padding: 12px;
            background: var(--cream);
            border-radius: var(--border-radius);
        }

        .detail-label {
            font-size: 12px;
            color: var(--forest-green);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 500;
        }

        .detail-value {
            font-size: 16px;
            color: #333;
            margin-top: 5px;
            font-weight: 300;
        }

        /* Форма изменения статуса */
        .status-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed var(--rose-gold);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--forest-green);
            letter-spacing: 0.5px;
        }

        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--rose-gold);
            border-radius: var(--border-radius);
            font-size: 16px;
            font-family: 'Oswald', sans-serif;
            font-weight: 300;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.2);
        }

        .btn-save {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--gold), var(--forest-green));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-family: 'Oswald', sans-serif;
            font-size: 18px;
            font-weight: 500;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(218, 165, 32, 0.4);
        }

        /* Пагинация */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
            padding-bottom: 30px;
        }

        .page-link {
            padding: 8px 16px;
            border: 2px solid var(--rose-gold);
            border-radius: var(--border-radius);
            text-decoration: none;
            color: var(--forest-green);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-link:hover,
        .page-link.active {
            background: var(--gold);
            color: white;
            border-color: var(--gold);
        }

        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--cream);
            border-radius: var(--border-radius);
        }

        .empty-state i {
            font-size: 48px;
            color: var(--gold);
            margin-bottom: 15px;
        }

        .empty-state h3 {
            color: var(--forest-green);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .empty-state p {
            color: #666;
            font-weight: 300;
        }

        /* Уведомление */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: linear-gradient(135deg, var(--gold), var(--forest-green));
            color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            z-index: 1000;
            animation: slideInRight 0.5s ease-out, fadeOut 0.5s ease-out 2.5s forwards;
            font-weight: 500;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 8px;
            }

            .nav-bar {
                flex-direction: column;
                gap: 10px;
                padding: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .request-item {
                padding: 20px;
            }

            .request-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-champagne-glasses"></i> Панель администратора</h1>
            <p class="subtitle">Управление заявками на банкетные площадки</p>
        </div>

        <div class="nav-bar">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Главная
            </a>
            <a href="?logout=1" class="btn btn-outline" onclick="return confirm('Выйти из аккаунта?')">
                <i class="fas fa-sign-out-alt"></i> Выход
            </a>
        </div>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" style="color: var(--gold);"><?= $stats['total'] ?></div>
                <div class="stat-label">Всего заявок</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--gold);"><?= $stats['new_requests'] ?></div>
                <div class="stat-label">🆕 Новые</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--forest-green);"><?= $stats['assigned'] ?></div>
                <div class="stat-label">🍽️ Банкет назначен</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #b8860b;"><?= $stats['completed'] ?></div>
                <div class="stat-label">✅ Банкет завершен</div>
            </div>
        </div>

        <!-- Список заявок -->
        <div class="requests-container">
            <?php
            if ($query->num_rows === 0) {
            ?>
                <div class="empty-state">
                    <i class="fas fa-glass-cheers"></i>
                    <h3>Заявок пока нет</h3>
                    <p>Когда пользователи оставят заявки на банкет, они появятся здесь</p>
                </div>
            <?php } else {
                while ($request = $query->fetch_assoc()) {
                    // Определяем класс для статуса
                    $status_class = match($request['status']) {
                        'Новая' => 'status-new',
                        'Банкет назначен' => 'status-assigned',
                        'Банкет завершен' => 'status-completed',
                        default => 'status-new'
                    };
            ?>
                <div class="request-item" style="border-left-color: 
                    <?= $request['status'] == 'Новая' ? 'var(--gold)' : ($request['status'] == 'Банкет назначен' ? 'var(--forest-green)' : 'var(--gold)') ?>;">
                    <div class="request-header">
                        <div class="user-info">
                            <h3><i class="fas fa-user"></i> <?= htmlspecialchars($request['login']) ?></h3>
                            <p><?= htmlspecialchars($request['fullname']) ?></p>
                        </div>
                        <div>
                            <span class="request-id">Заявка №<?= htmlspecialchars($request['id']) ?></span>
                            <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span>
                        </div>
                    </div>

                    <div class="request-details">
                        <div class="detail-item">
                            <div class="detail-label"><i class="far fa-calendar-alt"></i> Дата и время</div>
                            <div class="detail-value"><?= htmlspecialchars($request['date']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-utensils"></i> Тип площадки</div>
                            <div class="detail-value"><?= htmlspecialchars($request['curses'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-credit-card"></i> Способ оплаты</div>
                            <div class="detail-value"><?= htmlspecialchars($request['payment'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-comment"></i> Доп. информация</div>
                            <div class="detail-value"><?= htmlspecialchars($request['review'] ?? '—') ?></div>
                        </div>
                    </div>

                    <!-- Форма изменения статуса -->
                    <div class="status-form">
                        <form method="POST" class="status-update-form">
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">

                            <div class="form-group">
                                <label class="form-label" for="status_<?= $request['id'] ?>">
                                    <i class="fas fa-tag"></i> Изменить статус заявки:
                                </label>
                                <select name="status" id="status_<?= $request['id'] ?>" class="form-select">
                                    <option value="Новая" <?= $request['status'] == 'Новая' ? 'selected' : '' ?>>
                                        🆕 Новая
                                    </option>
                                    <option value="Банкет назначен" <?= $request['status'] == 'Банкет назначен' ? 'selected' : '' ?>>
                                        🍽️ Банкет назначен
                                    </option>
                                    <option value="Банкет завершен" <?= $request['status'] == 'Банкет завершен' ? 'selected' : '' ?>>
                                        ✅ Банкет завершен
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            <?php
                }
            }
            ?>
        </div>

        <!-- Пагинация -->
        <?php if ($stats['total'] > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($stats['total'] / $limit);
                for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Уведомление об успехе -->
    <?php if ($status_updated): ?>
        <div class="notification">
            <i class="fas fa-check-circle"></i> Статус заявки успешно обновлён!
        </div>
    <?php endif; ?>

    <script>
        // Обработка отправки форм статуса
        document.querySelectorAll('.status-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.btn-save');
                const originalText = submitBtn.innerHTML;

                // Блокировка кнопки на время обработки
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';

                // Восстановление через 2 секунды (можно заменить на обработку ответа сервера)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 2000);
            });
        });

        // Плавная прокрутка к уведомлениям
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            // Автоматическое скрытие через 3 секунды
            setTimeout(() => {
                if (notification) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 500);
                }
            }, 3000);
        }

        // Подсветка активной страницы в пагинации
        document.querySelectorAll('.page-link').forEach(link => {
            if (link.getAttribute('href') === window.location.pathname + window.location.search) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>