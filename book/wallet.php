<?php
include 'db.php';
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

$message = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(isset($_POST['add_money'])) {
    $amount = $_POST['amount'];
    if($amount > 0) {
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        if($stmt->execute([$amount, $user_id])) {
            $message = "<p class='success'>Баланс успешно пополнен</p>";
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $message = "<p class='alert'>Ошибка при пополнении баланса</p>";
        }
    } else {
        $message = "<p class='alert'>Введите корректную сумму</p>";
    }
}

$notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notif_stmt->execute([$user_id]);
$unread_count = $notif_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Кошелек</title>
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Кошелек</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="library.php">Библиотека</a>
            <a href="my_books.php">Мои книги</a>
            <a href="notifications.php">Уведомления<?php if($unread_count > 0) echo " ({$unread_count})"; ?></a>
            <a href="wallet.php">Кошелек</a>
            <a href="logout.php">Выйти</a>
        </nav>
    </header>
    <main>
        <?php echo $message; ?>
        <h2>Ваш баланс: <?php echo htmlspecialchars($user['balance']); ?> руб.</h2>
        <form method="post" action="">
            <input type="number" step="0.01" name="amount" placeholder="Сумма пополнения" required>
            <button type="submit" name="add_money">Пополнить</button>
        </form>
    </main>
</body>
</html>
