<?php
include 'db.php';
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

$update_stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$update_stmt->execute([$user_id]);

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

$notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$notif_stmt->execute([$user_id]);
$unread_count = $notif_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Уведомления</title>
    <link rel="stylesheet" href="style.css">
    <!-- Подключение Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Уведомления</h1>
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
        <?php if(count($notifications) > 0): ?>
            <ul>
                <?php foreach($notifications as $notification): ?>
                    <li>
                        <?php echo htmlspecialchars($notification['message']); ?>
                        <span class="date"><?php echo date('d.m.Y H:i', strtotime($notification['created_at'])); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>У вас нет уведомлений.</p>
        <?php endif; ?>
    </main>
</body>
</html>
