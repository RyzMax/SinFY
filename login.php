<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход / Регистрация</title>
    <link rel="stylesheet" href="assets/css/styleslogin.css">
    <link rel="icon" href="assets/images/note.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
</head>
<body>
<div class="login-form" id="loginForm">


    <?php if (!empty($_SESSION['auth_error'])): ?>
        <div class="error-msg"><?php echo $_SESSION['auth_error']; unset($_SESSION['auth_error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['auth_success'])): ?>
        <div class="success-msg"><?php echo $_SESSION['auth_success']; unset($_SESSION['auth_success']); ?></div>
    <?php endif; ?>


    <div class="login-section active" id="loginSection">
        <form method="post" action="auth.php">
            <input type="hidden" name="action" value="login">
            <input type="text" class="input-field" name="login" placeholder="Логин" required>
            <input type="password" class="input-field" name="password" placeholder="Пароль" required>
            <button class="login-btn" type="submit">Войти</button>
        </form>
    </div>

    <button class="register-toggle" onclick="toggleForm()">Зарегистрироваться</button>


    <div class="register-section" id="registerSection">
        <form method="post" action="auth.php">
            <input type="hidden" name="action" value="register">
            <input type="email" class="input-field" name="email" placeholder="Почта" required>
            <input type="text" class="input-field" name="login" placeholder="Логин" required>
            <input type="password" class="input-field" name="password" placeholder="Пароль" required>
            <input type="password" class="input-field" name="password2" placeholder="Повторить пароль" required>
            <button class="login-btn" type="submit">Зарегистрироваться</button>
        </form>
        <button class="register-toggle back-btn" onclick="toggleForm()">Войти</button>
    </div>
</div>

<script>
    function toggleForm() {
        const loginSection = document.getElementById('loginSection');
        const registerSection = document.getElementById('registerSection');
        loginSection.classList.toggle('active');
        registerSection.classList.toggle('active');
    }
</script>
</body>
</html>
