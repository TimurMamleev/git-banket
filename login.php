<?php
session_start();

// Если пользователь уже авторизован, перенаправляем
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = true;
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        include('db.php');
        
        // Используем подготовленные выражения для защиты от SQL инъекций
        $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = true;
            $error_message = 'Неверный логин или пароль';
        } else {
            $user = $result->fetch_assoc();
            
            // Проверка пароля (рекомендуется использовать password_hash() при регистрации)
            if ($password !== $user['password']) {
                $error = true;
                $error_message = 'Неверный логин или пароль';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_fullname'] = $user['fullname'];
                
                // Проверка на администратора
                if ($user['login'] == 'Admin26') {
                    $_SESSION['admin'] = true;
                    header('Location: admin.php');
                } else {
                    header('Location: create.php');
                }
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Банкетам.Нет</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Анимированные волны на фоне */
        .wave {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.08)" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,208C384,203,480,181,576,181.3C672,181,768,203,864,208C960,213,1056,203,1152,186.7C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') repeat-x;
            background-size: cover;
            animation: waveMove 10s linear infinite;
            z-index: 0;
        }

        @keyframes waveMove {
            0% { background-position-x: 0; }
            100% { background-position-x: 1440px; }
        }

        .container {
            max-width: 450px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            animation: slideInUp 0.6s ease-out;
            position: relative;
            z-index: 1;
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

        /* Логотип */
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 2px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--forest-green) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: titleGlow 2s ease-in-out infinite;
            text-transform: uppercase;
        }

        @keyframes titleGlow {
            0%, 100% {
                text-shadow: 0 0 0px rgba(218, 165, 32, 0);
            }
            50% {
                text-shadow: 0 0 15px rgba(218, 165, 32, 0.3);
            }
        }

        .logo p {
            color: var(--forest-green);
            font-size: 16px;
            margin-top: 5px;
            font-weight: 400;
            letter-spacing: 1px;
        }

        /* Заголовок формы */
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: var(--forest-green);
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #888;
            font-size: 14px;
            font-weight: 300;
        }

        /* Сообщение об ошибке */
        .error-message {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: var(--crimson);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid var(--crimson);
            animation: shakeError 0.5s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 400;
        }

        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Стили формы */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            letter-spacing: 0.5px;
            color: var(--forest-green);
            transition: all 0.3s ease;
        }

        .form-group label i {
            margin-right: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid var(--rose-gold);
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Oswald', sans-serif;
            font-weight: 300;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 4px rgba(218, 165, 32, 0.2);
            transform: scale(1.02);
            background: white;
        }

        .form-group input:hover {
            border-color: var(--gold);
            background: white;
        }

        /* Кнопка входа */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--forest-green) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 20px;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(218, 165, 32, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Дополнительные ссылки */
        .form-footer {
            margin-top: 25px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--rose-gold);
        }

        .form-footer p {
            color: #666;
            margin-bottom: 10px;
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        .register-link {
            color: var(--gold);
            text-decoration: none;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .register-link:hover {
            color: var(--forest-green);
            transform: translateX(5px);
        }

        .back-home {
            display: inline-block;
            margin-top: 15px;
            color: #888;
            text-decoration: none;
            font-size: 14px;
            font-weight: 300;
            transition: all 0.3s ease;
        }

        .back-home:hover {
            color: var(--gold);
        }

        /* Анимация для инпутов */
        .form-group {
            animation: fadeInUp 0.5s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .btn-login { animation: fadeInUp 0.5s ease-out 0.3s both; }
        .form-footer { animation: fadeInUp 0.5s ease-out 0.4s both; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Адаптивность */
        @media (max-width: 480px) {
            .container {
                padding: 25px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
            
            .form-header h2 {
                font-size: 24px;
            }
            
            .btn-login {
                padding: 12px;
                font-size: 18px;
            }
        }

        /* Стили для иконок */
        .icon {
            display: inline-block;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wave"></div>
    
    <div class="container">
        <div class="logo">
            <h1>Банкетам.Нет</h1>
            <p>Выбор помещения для банкета</p>
        </div>

        <div class="form-header">
            <h2>Добро пожаловать!</h2>
            <p>Войдите в свой аккаунт</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <span>⚠️</span>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="login">
                    <span class="icon">👤</span> Логин
                </label>
                <input type="text" id="login" name="login" 
                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                       placeholder="Введите ваш логин" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">
                    <span class="icon">🔒</span> Пароль
                </label>
                <input type="password" id="password" name="password" 
                       placeholder="Введите пароль" required>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <span class="icon">🎉</span> Войти
            </button>
        </form>

        <div class="form-footer">
            <p>Нет аккаунта? <a href="register.php" class="register-link">Зарегистрироваться →</a></p>
            <a href="index.php" class="back-home">← Вернуться на главную</a>
        </div>
    </div>

    <script>
        // Анимация при отправке формы
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const login = document.getElementById('login').value.trim();
                const password = document.getElementById('password').value;
                
                if (!login || !password) {
                    e.preventDefault();
                    showError('Пожалуйста, заполните все поля');
                    return;
                }
                
                // Добавляем анимацию загрузки
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="icon">⏳</span> Вход...';
                submitBtn.style.opacity = '0.7';
                submitBtn.disabled = true;
                
                // Если форма валидна, она отправится автоматически
                setTimeout(() => {
                    // Эта функция выполнится только если форма не отправилась
                    submitBtn.innerHTML = originalText;
                    submitBtn.style.opacity = '1';
                    submitBtn.disabled = false;
                }, 3000);
            });
        }
        
        // Функция показа ошибки (клиентская валидация)
        function showError(message) {
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<span>⚠️</span> ${message}`;
            
            const formHeader = document.querySelector('.form-header');
            formHeader.insertAdjacentElement('afterend', errorDiv);
            
            // Анимация встряхивания контейнера
            const container = document.querySelector('.container');
            container.style.animation = 'shakeError 0.5s ease-in-out';
            setTimeout(() => {
                container.style.animation = '';
            }, 500);
        }
        
        // Добавляем эффект при наведении на инпуты
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateX(5px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateX(0)';
            });
        });
        
        // Сохраняем логин в localStorage (для удобства, опционально)
        const savedLogin = localStorage.getItem('savedLogin');
        if (savedLogin && !document.getElementById('login').value) {
            document.getElementById('login').value = savedLogin;
        }
        
        form.addEventListener('submit', function() {
            const login = document.getElementById('login').value;
            localStorage.setItem('savedLogin', login);
        });
    </script>
</body>
</html>