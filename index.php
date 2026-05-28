<?php
session_start();

// Выход из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Проверяем, установлен ли ключ admin в сессии
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Банкетам.Нет - выбор площадки для банкета</title>
  <!-- Подключение шрифта Oswald -->
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Новая цветовая схема: золотой, розово-золотистый, кремовый, насыщенно-красный, тёмно-зелёный */
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
      margin: 0;
      padding: 0;
      color: var(--cream);
      min-height: 100vh;
    }

    /* Шапка сайта */
    .header {
      background: rgba(0, 40, 0, 0.95);
      padding: 15px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .logo {
      color: var(--gold);
      font-size: 28px;
      font-weight: 700;
      letter-spacing: 2px;
      text-decoration: none;
      text-shadow: 0 0 10px rgba(218, 165, 32, 0.5);
      transition: all 0.3s ease;
      text-transform: uppercase;
    }

    .logo:hover {
      color: var(--rose-gold);
      text-shadow: 0 0 15px rgba(255, 218, 185, 0.8);
    }

    .nav-buttons a {
      margin-left: 15px;
      padding: 10px 20px;
      border: 2px solid var(--gold);
      border-radius: 25px;
      color: var(--gold);
      text-decoration: none;
      transition: all 0.3s ease;
      font-family: 'Oswald', sans-serif;
      font-weight: 500;
      letter-spacing: 0.5px;
    }

    .nav-buttons a:hover {
      background-color: var(--gold);
      color: var(--forest-green);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Слайдер */
    .slideshow-container {
      max-width: 1000px;
      position: relative;
      margin: 40px auto;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .mySlides {
      display: none;
    }

    .fade {
      animation: fadeIn 1.5s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0.4; }
      to { opacity: 1; }
    }

    .mySlides {
      text-align: center;  /* Это центрирует содержимое по горизонтали */
      background: linear-gradient(135deg, #006400 0%, #003300 100%);  /* ЗЕЛЁНЫЙ ГРАДИЕНТ */
    }
    
    .mySlides img {
      display: block !important;
      width: 45% !important;
      height: 300px !important;
      object-fit: cover !important;
      object-position: center !important;
      margin: 0 auto !important;
      border-radius: 15px !important;
    }
    .text {
      position: absolute;
      bottom: 20px;
      left: 20px;
      background: rgba(0, 40, 0, 0.8);
      padding: 10px 20px;
      border-radius: 5px;
      font-size: 20px;
      font-weight: 600;
      letter-spacing: 1px;
      color: var(--gold);
      font-family: 'Oswald', sans-serif;
    }

    /* Стрелки */
    .prev, .next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0, 40, 0, 0.8);
      color: var(--gold);
      border: none;
      cursor: pointer;
      padding: 15px 20px;
      font-size: 18px;
      border-radius: 50%;
      transition: all 0.3s ease;
      font-family: 'Oswald', sans-serif;
    }

    .prev {
      left: 10px;
    }

    .next {
      right: 10px;
    }

    .prev:hover, .next:hover {
      background-color: var(--gold);
      color: var(--forest-green);
      transform: translateY(-50%) scale(1.1);
    }

    /* Точки навигации */
    .dot-container {
      text-align: center;
      padding: 20px 0;
    }

    .dot {
      cursor: pointer;
      height: 15px;
      width: 15px;
      margin: 0 5px;
      background-color: #bbb;
      border-radius: 50%;
      display: inline-block;
      transition: background-color 0.3s ease;
    }

    .dot.active, .dot:hover {
      background-color: var(--gold);
    }

    /* Секция преимуществ */
    .features-section {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .features-title {
      text-align: center;
      color: var(--gold);
      margin-bottom: 30px;
      font-size: 32px;
      font-weight: 600;
      letter-spacing: 1px;
      text-transform: uppercase;
      font-family: 'Oswald', sans-serif;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
    }

    .feature-card {
      background: rgba(0, 40, 0, 0.8);
      padding: 25px;
      border-radius: 10px;
      text-align: center;
      transition: all 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      border: 1px solid var(--gold);
    }

    .feature-card h3 {
      color: var(--rose-gold);
      font-size: 22px;
      font-weight: 500;
      letter-spacing: 0.5px;
      margin-bottom: 15px;
      font-family: 'Oswald', sans-serif;
    }

    .feature-card p {
      color: var(--cream);
      line-height: 1.5;
      font-weight: 300;
      font-size: 16px;
      font-family: 'Oswald', sans-serif;
    }

    /* Адаптивность */
    @media (max-width: 768px) {
      .nav {
        flex-direction: column;
        align-items: center;
        padding: 10px 15px;
        gap: 10px;
      }

      .logo {
        font-size: 20px;
        text-align: center;
      }

      .nav-buttons {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        width: 100%;
        gap: 8px;
      }

      .nav-buttons a {
        padding: 8px 14px;
        font-size: 13px;
        white-space: nowrap;
      }
    }

    /* Для очень маленьких телефонов (до 480px) */
    @media (max-width: 480px) {
      .nav {
        padding: 8px 12px;
        gap: 8px;
      }

      .logo {
        font-size: 18px;
      }

      .nav-buttons {
        flex-direction: column;
        width: 100%;
        gap: 6px;
      }

      .nav-buttons a {
        display: block;
        width: 100%;
        text-align: center;
        padding: 8px 12px;
        font-size: 14px;
        white-space: normal;
      }
    }
  </style>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
<!-- Шапка сайта -->
<header class="header">
  <div class="nav">
    <a href="index.php" class="logo">🍽️ Банкетам.Нет</a>

    <!-- Кнопки навигации -->
    <div class="nav-buttons">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="btn-login">🔐 Войти</a>
        <a href="register.php" class="btn-register">📝 Регистрация</a>
      <?php elseif ($is_admin): ?>
        <a href="admin.php" class="btn-admin">👑 Панель администратора</a>
        <a href="?logout=1" class="btn-exit">🚪 Выход</a>
      <?php elseif (isset($_SESSION['user_id'])): ?>
        <a href="history.php" class="btn-lk">📋 Мои заявки</a>
        <a href="create.php" class="btn-create">🎉 Новая заявка</a>
        <a href="?logout=1" class="btn-exit">🚪 Выход</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Слайдер с картинками для банкетных площадок -->
<div class="mySlides fade">
  <img src="images/banquet.jpg" alt="Банкетный зал">
  <div class="text">🏛️ Просторный банкетный зал</div>
</div>

<div class="mySlides fade">
  <img src="images/banquet2.jpg" alt="Ресторан для банкета">
  <div class="text">🍷 Изысканный ресторан</div>
</div>

<div class="mySlides fade">
  <img src="images/banquet3.jpg" alt="Летняя веранда">
  <div class="text">🌞 Уютная летняя веранда</div>
</div>

<div class="mySlides fade">
  <img src="images/banquet4.jpg" alt="Закрытая веранда">
  <div class="text">🏠 Тёплая закрытая веранда</div>
</div>

<!-- Точки навигации -->
<div class="dot-container">
  <span class="dot" onclick="currentSlide(1)"></span>
  <span class="dot" onclick="currentSlide(2)"></span>
  <span class="dot" onclick="currentSlide(3)"></span>
  <span class="dot" onclick="currentSlide(4)"></span>
</div>

<!-- Основной контент -->
<section class="features-section">
  <h2 class="features-title">✨ Почему выбирают «Банкетам.Нет»?</h2>
  
  <div class="features-grid">
    <div class="feature-card">
      <h3>🏛️ Лучшие залы и рестораны</h3>
      <p>Подберём идеальное место для вашего торжества — от камерных залов до больших ресторанов.</p>
    </div>
    
    <div class="feature-card">
      <h3>🌿 Летние и закрытые веранды</h3>
      <p>Организуем банкет на свежем воздухе или в уютной закрытой веранде в любое время года.</p>
    </div>
    
    <div class="feature-card">
      <h3>🤝 Помощь с выбором</h3>
      <p>Наши менеджеры помогут выбрать помещение под любой бюджет и количество гостей.</p>
    </div>
  </div>
</section>

<script>
// JavaScript для управления слайдером
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");

  if (n > slides.length) { slideIndex = 1 }
  if (n < 1) { slideIndex = slides.length }

  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }

  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
}

// Автоматическое переключение слайдов каждые 3 секунды
let slideInterval = setInterval(function() {
  plusSlides(1);
}, 3000);

// Останавливаем автоматическое переключение при наведении на слайдер
const slideshowContainer = document.querySelector('.slideshow-container');
if (slideshowContainer) {
  slideshowContainer.addEventListener('mouseenter', function() {
    clearInterval(slideInterval);
  });
  
  slideshowContainer.addEventListener('mouseleave', function() {
    slideInterval = setInterval(function() {
      plusSlides(1);
    }, 3000);
  });
}
</script>
</body>
</html>