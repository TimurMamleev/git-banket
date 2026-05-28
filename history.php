<?php
session_start();
if(!isset($_SESSION['user_id'])) die('Чтобы посмотреть историю заявок, надо войти в аккаунт.');
include('db.php');

// Код изменения отзыва
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $request_id = (int)$_POST['request_id'];
    $con->query("UPDATE request SET review='$review' WHERE id='$request_id' AND user_id='$user_id'");
    echo '<div class="success-message">✓ Отзыв успешно оставлен!</div>';
}

// Код истории заявок
$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if(!$query) die('query error: ' . $con->error); 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заявки - Банкетам.Нет</title>
    <!-- Подключение шрифта Oswald -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Цветовая схема: Золотой, Розово-золотистый, Кремовый, Насыщенно-красный, Тёмно-зелёный */
        :root {
            --gold: #DAA520;
            --rose-gold: #FFDAB9;
            --cream: #FFFDD0;
            --crimson: #DC143C;
            --forest-green: #006400;
        }

        body {
            font-family: 'Oswald', sans-serif;
            background: linear-gradient(135deg, var(--forest-green) 0%, #003300 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 35px;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Кнопка на главную */
        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, var(--gold) 0%, var(--forest-green) 100%);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 50px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(218, 165, 32, 0.4);
            background: linear-gradient(135deg, #e6b422 0%, #008000 100%);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--forest-green);
            font-size: 32px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Сообщение об успехе */
        .success-message {
            background: linear-gradient(135deg, var(--cream) 0%, #e8e4c5 100%);
            color: var(--forest-green);
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid var(--gold);
            font-weight: 400;
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Карточка заявки */
        .request {
            border: 2px solid var(--rose-gold);
            margin: 20px 0;
            padding: 20px;
            border-radius: 15px;
            background: #fafafa;
            transition: all 0.3s ease;
        }

        .request:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: var(--gold);
        }

        .request h2 {
            margin-top: 0;
            color: var(--gold);
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--rose-gold);
        }

        .request b {
            color: var(--forest-green);
            font-weight: 600;
        }

        .request p {
            margin: 8px 0;
            font-weight: 300;
        }

        /* Статусы заявок */
        .status-new {
            color: var(--gold);
            font-weight: 600;
            background: rgba(218, 165, 32, 0.1);
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
        }

        .status-processing {
            color: #ff9800;
            font-weight: 600;
            background: rgba(255, 152, 0, 0.1);
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
        }

        .status-completed {
            color: var(--forest-green);
            font-weight: 600;
            background: rgba(0, 100, 0, 0.1);
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
        }

        .status-cancelled {
            color: var(--crimson);
            font-weight: 600;
            background: rgba(220, 20, 60, 0.1);
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
        }

        /* Форма отзыва */
        .review-form {
            margin-top: 18px;
            padding-top: 15px;
            border-top: 1px dashed var(--rose-gold);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .review-form input[type="text"] {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid var(--rose-gold);
            border-radius: 50px;
            font-size: 14px;
            font-family: 'Oswald', sans-serif;
            font-weight: 300;
            transition: all 0.3s ease;
        }

        .review-form input[type="text"]:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.2);
        }

        .review-form button {
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--forest-green) 100%);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-family: 'Oswald', sans-serif;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .review-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(218, 165, 32, 0.4);
        }

        /* Отзыв */
        .review-text {
            margin-top: 12px;
            padding: 10px;
            background: var(--cream);
            border-radius: 10px;
            color: var(--forest-green);
            font-weight: 300;
        }

        .review-text b {
            color: var(--gold);
        }

        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #888;
            font-size: 18px;
            font-weight: 300;
        }

        /* Адаптивность */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .request h2 {
                font-size: 18px;
            }
            
            .review-form {
                flex-direction: column;
            }
            
            .review-form input[type="text"] {
                width: 100%;
            }
            
            .review-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-home">🏠 На главную</a>
        
        <h1>📋 Мои заявки на банкет</h1>
        
        <?php
        $i = 0;
        if($query->num_rows == 0) {
            echo '<div class="empty-state">🎉 У вас пока нет заявок.<br><br>✍️ <a href="create.php" style="color: var(--gold);">Создать новую заявку</a></div>';
        }
        while($request = $query->fetch_assoc()) {
            $i++; 
            
            // Определяем класс статуса
            $status_class = 'status-new';
            $status_text = htmlspecialchars($request['status']);
            if($status_text == 'Новая') $status_class = 'status-new';
            elseif($status_text == 'В обработке') $status_class = 'status-processing';
            elseif($status_text == 'Завершено') $status_class = 'status-completed';
            elseif($status_text == 'Отменено') $status_class = 'status-cancelled';
            
            echo '
            <div class="request">
                <h2>🎯 Заявка #' . $request['id'] . '</h2>
                <p><b>📅 Дата проведения:</b> ' . htmlspecialchars($request['date']) . '</p>
                <p><b>🍽️ Тип площадки:</b> ' . htmlspecialchars($request['curses']) . '</p>
                <p><b>💳 Способ оплаты:</b> ' . htmlspecialchars($request['payment']) . '</p>
                <p><b>📊 Статус:</b> <span class="' . $status_class . '">' . $status_text . '</span></p>';
            
            // Если есть отзыв, показываем его
            if(!empty($request['review'])) {
                echo '<div class="review-text"><b>⭐ Ваш отзыв:</b> ' . htmlspecialchars($request['review']) . '</div>';
            }
            
            // Если статус "Завершено" - показываем форму для отзыва
            if($request['status'] === 'Завершено') {
                echo '
                <div class="review-form">
                    <form action="" method="POST" style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;">
                        <input type="hidden" name="request_id" value="' . $request['id'] . '">
                        <input type="text" name="review" placeholder="✍️ Оставьте отзыв о проведённом банкете..." value="' . htmlspecialchars($request['review'] ?? '') . '">
                        <button type="submit">⭐ Оставить отзыв</button>
                    </form>
                </div>';
            }
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="create.php" style="background: linear-gradient(135deg, var(--gold) 0%, var(--forest-green) 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 50px; font-weight: 500; display: inline-block;">🎉 Создать новую заявку</a>
        </div>
    </div>
</body>
</html>