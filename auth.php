<?php
require 'db.php';

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $email = trim($_POST['email'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($password !== $password2) {
        $_SESSION['auth_error'] = 'Пароли не совпадают';
        header('Location: login.php');
        exit;
    }

    if (strlen($login) < 3) {
        $_SESSION['auth_error'] = 'Логин должен быть не короче 3 символов';
        header('Location: login.php');
        exit;
    }


    $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ? OR email = ?');
    $stmt->execute([$login, $email]);
    if ($stmt->fetch()) {
        $_SESSION['auth_error'] = 'Такой логин или почта уже заняты';
        header('Location: login.php');
        exit;
    }

 
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (email, login, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$email, $login, $hash]);

    $_SESSION['auth_success'] = 'Регистрация успешна! Теперь войдите.';
    header('Location: login.php');
    exit;
}

if ($action === 'login') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE login = ? LIMIT 1');
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['auth_error'] = 'Неверный логин или пароль';
        header('Location: login.php');
        exit;
    }


    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_login'] = $user['login'];
    $_SESSION['is_admin'] = (int)$user['is_admin'];

    header('Location: profile.php');
    exit;
}


header('Location: login.php');
