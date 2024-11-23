<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Вход</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="library.php">Библиотека</a>
            <a href="register.php">Регистрация</a>
        </nav>
    </header>
    <main>
        <?php
        $message = '';
        if(isset($_POST['login'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['is_admin'] = $user['is_admin'];

                $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $notif_stmt->execute([$user['id']]);
                $_SESSION['unread_count'] = $notif_stmt->fetchColumn();

                header('Location: index.php');
                exit();
            } else {
                $message = "<p class='alert'>Неверный логин или пароль</p>";
            }
        }
        ?>
        <?php echo $message; ?>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="login">Войти</button>
        </form>
    </main>
</body>
</html>
