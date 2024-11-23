<?php
include 'db.php';
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$book_id = $_GET['id'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    if(!$book) {
        $message = "<p class='alert'>Книга не найдена</p>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user_books WHERE user_id = ? AND book_id = ? AND rent_end IS NULL");
        $stmt->execute([$user_id, $book_id]);
        if($stmt->rowCount() > 0) {
            $message = "<p class='alert'>Вы уже купили эту книгу</p>";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if($user['balance'] >= $book['price_buy']) {
                $pdo->beginTransaction();

                try {
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                    $stmt->execute([$book['price_buy'], $user_id]);

                    $stmt = $pdo->prepare("INSERT INTO user_books (user_id, book_id) VALUES (?, ?)");
                    $stmt->execute([$user_id, $book_id]);

                    $notify_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $notify_stmt->execute([$user_id, "Вы успешно купили книгу \"" . $book['book_name'] . "\"."]);

                    $pdo->commit();

                    $message = "<p class='success'>Книга успешно куплена</p>";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = "<p class='alert'>Ошибка при покупке книги</p>";
                }
            } else {
                $message = "<p class='alert'>Недостаточно средств</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Покупка книги</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Покупка книги</h1>
        <?php
        $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $notif_stmt->execute([$user_id]);
        $unread_count = $notif_stmt->fetchColumn();
        ?>
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
        <?php if(!$message): ?>
            <p>Вы уверены, что хотите купить эту книгу?</p>
            <form method="post">
                <button type="submit">Купить</button>
            </form>
        <?php else: ?>
            <a href="library.php" class="button">Вернуться в библиотеку</a>
        <?php endif; ?>
    </main>
</body>
</html>
